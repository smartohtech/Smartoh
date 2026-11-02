<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SaasPlan;
use App\Models\SaasPlanChangeRequest;
use App\Models\SaasSubscription;
use App\Models\TrialEvent;
use App\Services\SaaS\SubscriptionLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlatformSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        SaasPlan::ensureDefaultPlansExist();

        $query = SaasSubscription::with(['tenant', 'plan', 'planVersion'])->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planId = $request->input('plan_id')) {
            $query->where('saas_plan_id', $planId);
        }

        $subscriptions = $query->paginate(15)->withQueryString();
        $plans = SaasPlan::all();
        $pendingChangeRequests = SaasPlanChangeRequest::with(['tenant', 'currentPlan', 'targetPlan', 'invoice'])
            ->whereIn('status', [
                SaasPlanChangeRequest::STATUS_PENDING_REVIEW,
                SaasPlanChangeRequest::STATUS_PENDING_PAYMENT,
            ])
            ->latest()
            ->get();

        return view('platform.subscriptions.index', compact('subscriptions', 'plans', 'pendingChangeRequests'));
    }

    public function show(SaasSubscription $subscription)
    {
        $subscription->load([
            'tenant.users',
            'plan',
            'planVersion',
            'trialEvents',
            'invoices.payments',
            'planChangeRequests.currentPlan',
            'planChangeRequests.targetPlan',
            'planChangeRequests.invoice',
        ]);

        $plans = SaasPlan::where('is_active', true)->get();

        return view('platform.subscriptions.show', compact('subscription', 'plans'));
    }

    public function updateStatus(Request $request, SaasSubscription $subscription)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                SaasSubscription::STATUS_PENDING_APPROVAL,
                SaasSubscription::STATUS_REJECTED,
                SaasSubscription::STATUS_APPROVED_PENDING_ACTIVATION,
                SaasSubscription::STATUS_TRIAL_ACTIVE,
                SaasSubscription::STATUS_TRIAL_EXPIRING,
                SaasSubscription::STATUS_TRIAL_EXPIRED,
                SaasSubscription::STATUS_PAYMENT_PENDING,
                SaasSubscription::STATUS_ACTIVE_PAID,
                SaasSubscription::STATUS_PAST_DUE,
                SaasSubscription::STATUS_GRACE_PERIOD,
                SaasSubscription::STATUS_SUSPENDED,
                SaasSubscription::STATUS_CANCELLED,
            ]),
            'notes'  => 'nullable|string|max:500',
        ]);

        $oldStatus = $subscription->status;
        $newStatus = $validated['status'];

        $subscription->update(['status' => $newStatus]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'tenant_id' => $subscription->tenant_id,
            'action'    => 'SUBSCRIPTION_STATUS_CHANGED',
            'details'   => "Status changed from {$oldStatus} to {$newStatus} by {$request->user()->name}. Notes: " . ($validated['notes'] ?? 'None'),
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', "Subscription status updated to {$newStatus}.");
    }

    public function extendTrial(Request $request, SaasSubscription $subscription)
    {
        $validated = $request->validate([
            'extra_days' => 'required|integer|min:1|max:30',
            'reason'     => 'required|string|max:255',
        ]);

        $extraDays = (int)$validated['extra_days'];
        $baseDate = ($subscription->trial_ends_at && $subscription->trial_ends_at->isFuture())
            ? $subscription->trial_ends_at
            : Carbon::now();

        $newTrialEnd = $baseDate->copy()->addDays($extraDays);

        $subscription->update([
            'trial_ends_at' => $newTrialEnd,
            'status'        => SaasSubscription::STATUS_TRIAL_ACTIVE,
        ]);

        TrialEvent::create([
            'tenant_id'       => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'event_type'      => 'extended',
            'recorded_at'     => Carbon::now(),
            'notes'           => "Extended by +{$extraDays} days by {$request->user()->name}. Reason: {$validated['reason']}",
        ]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'tenant_id' => $subscription->tenant_id,
            'action'    => 'TRIAL_EXTENDED',
            'details'   => "Extended trial for {$subscription->tenant->name} by {$extraDays} days until {$newTrialEnd->toDateString()}.",
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', "Trial successfully extended by {$extraDays} days until {$newTrialEnd->toFormattedDateString()}.");
    }

    public function generateInvoice(SaasSubscription $subscription, SubscriptionLifecycleService $lifecycle)
    {
        $invoice = $lifecycle->generateRenewalInvoice($subscription);

        return back()->with('success', "Invoice {$invoice->invoice_number} created for KES " . number_format($invoice->total, 2));
    }

    public function approvePlanChangeRequest(
        Request $request,
        SaasPlanChangeRequest $changeRequest,
        SubscriptionLifecycleService $lifecycle
    ) {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $lifecycle->applyPlanChangeRequest(
            $changeRequest,
            $request->user(),
            $validated['admin_notes'] ?? 'Approved by Platform Super Admin.'
        );

        return back()->with('success', "Approved and applied {$changeRequest->change_type} request {$changeRequest->reference_number} for {$changeRequest->tenant->name} to {$changeRequest->targetPlan->name}.");
    }

    public function rejectPlanChangeRequest(Request $request, SaasPlanChangeRequest $changeRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $changeRequest->update([
            'status'              => SaasPlanChangeRequest::STATUS_REJECTED,
            'admin_notes'         => $validated['admin_notes'],
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at'         => Carbon::now(),
        ]);

        if ($changeRequest->invoice && $changeRequest->invoice->status === 'pending') {
            $changeRequest->invoice->update(['status' => 'void']);
        }

        AuditLog::create([
            'user_id'    => $request->user()->id,
            'tenant_id'  => $changeRequest->tenant_id,
            'action'     => 'ISP_PLAN_CHANGE_REJECTED',
            'details'    => "Rejected {$changeRequest->change_type} request {$changeRequest->reference_number} for {$changeRequest->tenant->name}. Reason: {$validated['admin_notes']}",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', "Plan change request {$changeRequest->reference_number} rejected.");
    }
}
