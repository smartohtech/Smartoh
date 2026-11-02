<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\NetworkActivationJob;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentReconciliationException;
use App\Models\StatementImport;
use App\Models\Voucher;
use App\Services\Accounting\FinanceService;
use App\Services\Network\NetworkActivationOrchestrator;
use App\Services\Payments\MpesaDarajaService;
use App\Services\Payments\PaymentOrchestratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{
    public function plans()
    {
        $plans = BillingPlan::withCount('clients')->latest()->get();
        return view('billing.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'type'                => 'required|in:daily,weekly,monthly,unlimited,data_cap',
            'price'               => 'required|numeric|min:0',
            'download_speed_kbps' => 'required|integer|min:256',
            'upload_speed_kbps'   => 'required|integer|min:256',
            'fup_limit_gb'        => 'nullable|integer',
            'burst_limit'         => 'nullable|string',
            'contention_ratio'    => 'required|integer|min:1',
            'validity_days'       => 'required|integer|min:1',
        ]);

        $plan = BillingPlan::create(array_merge($validated, [
            'currency'  => 'KES',
            'is_active' => true,
        ]));

        return back()->with('success', "Service Plan '{$plan->name}' created successfully!");
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['client', 'billingPlan'])->latest('issue_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15);
        $totalBilled = Invoice::sum('total_amount');
        $totalCollected = Invoice::sum('paid_amount');
        $totalOutstanding = max(0, $totalBilled - $totalCollected);

        return view('billing.invoices', compact('invoices', 'totalBilled', 'totalCollected', 'totalOutstanding'));
    }

    /**
     * ISP Subscriber Payments, Live Feed, Reconciliation & Activation Dashboard.
     * Enforces strict tenant isolation (ordinary Super Admin view cannot view ISP operational collections without audited impersonation).
     */
    public function payments(Request $request, PaymentOrchestratorService $orchestrator)
    {
        $user = $request->user();
        $tenantId = app()->bound('current_tenant_id') ? (int) app('current_tenant_id') : (int) ($user?->tenant_id ?? 0);

        if ($user && $user->isSuperAdmin() && !$tenantId) {
            abort(403, 'Tenant Isolation Policy: Super Administrators cannot view individual ISP subscriber transactions without an explicitly authorized, audited support session.');
        }

        $query = Payment::with(['client', 'invoice', 'paymentGateway', 'activationJob'])->latest('created_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_reference', 'like', "%{$search}%")
                  ->orWhere('external_transaction_id', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('account_reference', 'like', "%{$search}%");
            });
        }

        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        if ($recStatus = $request->get('reconciliation_status')) {
            $query->where('reconciliation_status', $recStatus);
        }

        if ($actStatus = $request->get('activation_status')) {
            $query->where('activation_status', $actStatus);
        }

        $payments = $query->paginate(15)->withQueryString();

        // Financial breakdown: Collected vs Reconciled vs Settled in ISP Account vs Held/Pending Provider Settlement
        $totalVolume = (float) Payment::where('status', 'completed')->sum('amount');
        $reconciledAmount = (float) Payment::where('status', 'completed')
            ->whereIn('reconciliation_status', ['matched', 'overpaid_wallet_credited', 'partially_allocated'])
            ->sum('amount');
        $settledAmount = (float) Payment::where('status', 'completed')
            ->whereIn('settlement_status', ['direct_to_isp_merchant', 'settled_in_isp_account'])
            ->sum('amount');
        $pendingSettlementAmount = (float) Payment::where('status', 'completed')
            ->whereIn('settlement_status', ['pending_provider_settlement', 'pending_settlement', 'held_by_provider'])
            ->sum('amount');

        $gateways = PaymentGateway::where('is_active', true)->get();
        $openExceptions = PaymentReconciliationException::with('payment')->where('status', 'open')->latest()->get();
        $retryingJobs = NetworkActivationJob::with(['client', 'payment', 'mikrotikDevice'])
            ->whereIn('status', [NetworkActivationJob::STATUS_QUEUED, NetworkActivationJob::STATUS_RETRYING, NetworkActivationJob::STATUS_FAILED_DEAD_LETTER])
            ->latest()
            ->get();
        $recentImports = StatementImport::latest()->take(5)->get();
        $providerCapabilities = $orchestrator->getAllProviderCapabilities();

        return view('billing.payments', compact(
            'payments',
            'totalVolume',
            'reconciledAmount',
            'settledAmount',
            'pendingSettlementAmount',
            'gateways',
            'openExceptions',
            'retryingJobs',
            'recentImports',
            'providerCapabilities'
        ));
    }

    /**
     * Real-time JSON feed & Server-Sent Events (SSE) endpoint for ISP dashboard & customer portal.
     */
    public function liveTransactionsFeed(Request $request)
    {
        $user = $request->user();
        $tenantId = app()->bound('current_tenant_id') ? (int) app('current_tenant_id') : (int) ($user?->tenant_id ?? 0);

        if ($user && $user->isSuperAdmin() && !$tenantId) {
            return response()->json(['error' => 'Unauthorized cross-tenant feed access'], 403);
        }

        $payload = $this->buildLiveFeedPayload($tenantId);

        if ($request->query('stream') === 'sse' || str_contains((string) $request->header('Accept'), 'text/event-stream')) {
            return new StreamedResponse(function () use ($payload) {
                echo "event: payment_update\n";
                echo 'data: ' . json_encode($payload) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type'      => 'text/event-stream',
                'Cache-Control'     => 'no-cache, no-store, must-revalidate',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->json($payload);
    }

    protected function buildLiveFeedPayload(int $tenantId): array
    {
        $recent = Payment::with(['client', 'invoice'])
            ->latest('id')
            ->take(15)
            ->get()
            ->map(fn(Payment $p) => [
                'id'                      => $p->id,
                'transaction_reference'   => $p->transaction_reference,
                'external_transaction_id' => $p->external_transaction_id,
                'subscriber_name'         => $p->client?->name ?? $p->payer_name ?? 'Unmatched Payer',
                'account_number'          => $p->client?->account_number ?? $p->account_reference ?? 'UNMATCHED',
                'invoice_number'          => $p->invoice?->invoice_number,
                'amount'                  => (float) $p->amount,
                'currency'                => $p->currency,
                'payment_method'          => $p->payment_method,
                'source_channel'          => $p->source_channel,
                'status'                  => $p->status,
                'verification_status'     => $p->verification_status,
                'reconciliation_status'   => $p->reconciliation_status,
                'settlement_status'       => $p->settlement_status,
                'activation_status'       => $p->activation_status,
                'human_status'            => $p->human_lifecycle_status,
                'paid_at'                 => ($p->paid_at ?? $p->created_at)?->toIso8601String(),
            ]);

        return [
            'success'      => true,
            'tenant_id'    => $tenantId,
            'timestamp'    => now()->toIso8601String(),
            'metrics'      => [
                'collected_total'            => (float) Payment::where('status', 'completed')->sum('amount'),
                'reconciled_total'           => (float) Payment::where('status', 'completed')->whereIn('reconciliation_status', ['matched', 'overpaid_wallet_credited', 'partially_allocated'])->sum('amount'),
                'settled_in_isp_account'     => (float) Payment::where('status', 'completed')->whereIn('settlement_status', ['direct_to_isp_merchant', 'settled_in_isp_account'])->sum('amount'),
                'pending_provider_settlement'=> (float) Payment::where('status', 'completed')->whereIn('settlement_status', ['pending_provider_settlement', 'pending_settlement', 'held_by_provider'])->sum('amount'),
                'open_exceptions_count'      => PaymentReconciliationException::where('status', 'open')->count(),
                'retrying_activations_count' => NetworkActivationJob::whereIn('status', ['queued', 'retrying', 'failed_dead_letter'])->count(),
            ],
            'transactions' => $recent,
        ];
    }

    public function triggerStkPush(Request $request, MpesaDarajaService $mpesa)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount'    => 'required|numeric|min:1',
        ]);

        $client = Client::findOrFail($request->client_id);
        $invoice = Invoice::where('client_id', $client->id)->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->oldest('issue_date')->first();

        $result = $mpesa->initiateStkPush($client, (float) $request->amount, $invoice);

        // Optional sandbox callback trigger for testing when explicitly requested
        if ($result['success'] && $request->boolean('simulate_instant_payment')) {
            $mockCallback = [
                'Body' => [
                    'stkCallback' => [
                        'CheckoutRequestID' => $result['checkout_request_id'],
                        'ResultCode'        => 0,
                        'ResultDesc'        => 'The service request is processed successfully.',
                        'CallbackMetadata'  => [
                            'Item' => [
                                ['Name' => 'Amount', 'Value' => (float) $request->amount],
                                ['Name' => 'MpesaReceiptNumber', 'Value' => 'QGH' . rand(1000000, 9999999)],
                                ['Name' => 'PhoneNumber', 'Value' => $client->phone],
                            ],
                        ],
                    ],
                ],
            ];
            $mpesa->processCallback($mockCallback);
            $payment = Payment::find($result['payment_id']);
            return back()->with('success', "Verified payment received! Status: {$payment?->human_lifecycle_status}");
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Import Bank or Non-API Pochi CSV Statement for controlled reconciliation.
     */
    public function importStatement(Request $request, PaymentOrchestratorService $orchestrator)
    {
        $request->validate([
            'provider'    => 'required|string',
            'csv_content' => 'nullable|string',
            'csv_file'    => 'nullable|file|max:5120',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? (int) app('current_tenant_id') : (int) $request->user()->tenant_id;
        $content = (string) $request->input('csv_content', '');
        $filename = 'manual_paste_' . date('Ymd_His') . '.csv';

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $content = file_get_contents($file->getRealPath());
            $filename = $file->getClientOriginalName();
        }

        if (trim($content) === '') {
            return back()->with('error', 'Please provide statement CSV content or upload a valid CSV file.');
        }

        $import = $orchestrator->importStatementCsv($tenantId, $content, $filename, $request->input('provider'), $request->user());

        return back()->with('success', "Statement '{$import->filename}' imported: {$import->matched_count} matched & activated, {$import->exception_count} exceptions queued, {$import->duplicate_count} duplicates skipped.");
    }

    /**
     * Resolve an ambiguous / wrong-reference / unverified payment exception.
     */
    public function resolveException(Request $request, PaymentReconciliationException $exception, PaymentOrchestratorService $orchestrator)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'notes'     => 'nullable|string|max:500',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $payment = $orchestrator->resolveException($exception, $client, $request->user(), $validated['notes'] ?? null);

        return back()->with('success', "Exception resolved and allocated to {$client->name} ({$client->account_number}). Status: {$payment->human_lifecycle_status}");
    }

    /**
     * Manually retry a queued or failed network activation job.
     */
    public function retryActivation(NetworkActivationJob $job, NetworkActivationOrchestrator $activationOrchestrator)
    {
        if ($job->status === NetworkActivationJob::STATUS_FAILED_DEAD_LETTER) {
            $job->update(['attempts' => max(0, $job->max_attempts - 1), 'status' => NetworkActivationJob::STATUS_RETRYING]);
        }

        $updated = $activationOrchestrator->attemptJobExecution($job);

        return back()->with(
            in_array($updated->status, [NetworkActivationJob::STATUS_CONNECTED_VERIFIED, NetworkActivationJob::STATUS_APPLIED], true) ? 'success' : 'warning',
            "Activation job #{$updated->id} executed. Current status: " . strtoupper($updated->status)
        );
    }

    /**
     * Reverse a payment and trigger controlled reassessment of subscriber service eligibility.
     */
    public function reversePayment(Request $request, Payment $payment, PaymentOrchestratorService $orchestrator)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $res = $orchestrator->reversePayment($payment, $validated['reason']);
        return back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    /**
     * Export tenant-isolated reconciliation report as CSV.
     */
    public function exportPaymentsCsv(Request $request)
    {
        $payments = Payment::with(['client', 'invoice'])->latest('id')->get();
        $csvLines = [
            'Transaction Ref,External Receipt,Subscriber,Account Number,Invoice Number,Method,Source Channel,Amount,Currency,Verification Status,Reconciliation Status,Settlement Status,Activation Status,Paid At',
        ];

        foreach ($payments as $p) {
            $csvLines[] = implode(',', [
                $p->transaction_reference,
                $p->external_transaction_id ?? '',
                '"' . str_replace('"', '""', $p->client?->name ?? $p->payer_name ?? '') . '"',
                $p->client?->account_number ?? $p->account_reference ?? '',
                $p->invoice?->invoice_number ?? '',
                $p->payment_method,
                $p->source_channel,
                $p->amount,
                $p->currency,
                $p->verification_status,
                $p->reconciliation_status,
                $p->settlement_status,
                $p->activation_status,
                $p->paid_at?->toIso8601String() ?? '',
            ]);
        }

        $content = implode("\n", $csvLines);
        return response($content, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="isp_reconciliation_report_' . date('Ymd_His') . '.csv"',
        ]);
    }

    public function vouchers()
    {
        $vouchers = Voucher::with('client')->latest()->paginate(20);
        $plans = BillingPlan::where('is_active', true)->get();
        $activeCount = Voucher::where('status', 'active')->count();
        $usedCount = Voucher::where('status', 'used')->count();

        return view('billing.vouchers', compact('vouchers', 'plans', 'activeCount', 'usedCount'));
    }

    public function generateVouchers(Request $request)
    {
        $validated = $request->validate([
            'billing_plan_id' => 'required|exists:billing_plans,id',
            'type'            => 'required|in:hotspot,pppoe',
            'quantity'        => 'required|integer|min:1|max:100',
            'duration_hours'  => 'required|integer|min:1',
        ]);

        $plan = BillingPlan::findOrFail($validated['billing_plan_id']);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            Voucher::create([
                'tenant_id'        => $plan->tenant_id,
                'billing_plan_id'  => $plan->id,
                'code'             => strtoupper(Str::random(4) . '-' . Str::random(4)),
                'type'             => $validated['type'],
                'price'            => $plan->price,
                'duration_minutes' => $validated['duration_hours'] * 60,
                'status'           => 'active',
                'expires_at'       => now()->addDays(90),
            ]);
        }

        return back()->with('success', "Generated {$validated['quantity']} {$validated['type']} vouchers for plan '{$plan->name}'!");
    }

    /**
     * Display the ISP Tenant's current SaaS subscription, 7-day trial status,
     * plan tier benefits, mid-cycle proration calculator, and platform invoices.
     * Strictly separate from ISP subscriber collections.
     */
    public function subscription(\App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        \App\Models\SaasPlan::ensureDefaultPlansExist();

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? \App\Models\Tenant::find($tenantId) : null)
            ?? auth()->user()?->tenant
            ?? \App\Models\Tenant::first()
            ?? \App\Models\Tenant::create(['name' => 'Smartoh Technologies', 'status' => 'active']);

        $subscription = $lifecycle->ensureTenantHasSubscription($tenant);
        $plans = \App\Models\SaasPlan::where('is_active', true)->with('currentVersion')->orderBy('sort_order')->get();
        $invoices = $tenant->saasInvoices()->with('items')->latest()->take(10)->get();
        $changeRequests = $tenant->planChangeRequests()->with(['currentPlan', 'targetPlan', 'invoice'])->take(10)->get();

        $prorationPreviews = [];
        foreach ($plans as $plan) {
            $prorationPreviews[$plan->id] = $lifecycle->calculateTierProration($tenant, $plan);
        }

        return view('billing.saas_subscription', compact(
            'tenant',
            'subscription',
            'plans',
            'invoices',
            'changeRequests',
            'prorationPreviews'
        ));
    }

    /**
     * Submit payment for the ISP's Smartoh SaaS platform subscription.
     * Updates ONLY the ISP's SaaS plan/subscription/trial access.
     * NEVER modifies or reconnects individual ISP subscriber accounts.
     */
    public function paySubscription(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'phone'    => 'nullable|string',
            'provider' => 'required|in:mpesa,bank_transfer,card,manual',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? \App\Models\Tenant::find($tenantId) : null) ?? \App\Models\Tenant::firstOrFail();
        $subscription = $lifecycle->ensureTenantHasSubscription($tenant);

        $ref = 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8));

        $lifecycle->recordPaymentAndActivate(
            $subscription,
            (float) $subscription->amount,
            $ref,
            $validated['provider'],
            $request->user(),
            "Online subscription renewal paid via " . strtoupper($validated['provider'])
        );

        return back()->with('success', "Payment of {$subscription->currency} " . number_format($subscription->amount) . " confirmed (Ref: {$ref})! Your subscription is now ACTIVE_PAID.");
    }

    /**
     * Calculate and preview mid-cycle proration for upgrading or downgrading ISP SaaS tiers.
     */
    public function previewTierProration(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        \App\Models\SaasPlan::ensureDefaultPlansExist();

        $validated = $request->validate([
            'target_plan_id'   => 'required|exists:saas_plans,id',
            'billing_interval' => 'nullable|in:monthly,annually',
            'remaining_days'   => 'nullable|integer|min:1|max:365',
            'total_cycle_days' => 'nullable|integer|min:1|max:365',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? \App\Models\Tenant::find($tenantId) : null) ?? \App\Models\Tenant::firstOrFail();
        $targetPlan = \App\Models\SaasPlan::findOrFail($validated['target_plan_id']);

        $calculation = $lifecycle->calculateTierProration(
            $tenant,
            $targetPlan,
            $validated['billing_interval'] ?? null,
            isset($validated['remaining_days']) ? (int) $validated['remaining_days'] : null,
            isset($validated['total_cycle_days']) ? (int) $validated['total_cycle_days'] : null
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'proration' => $calculation]);
        }

        $msg = $calculation['is_upgrade']
            ? "Proration calculated for upgrade to {$calculation['target_plan_name']}: Amount to add for {$calculation['remaining_days']} remaining days is {$calculation['currency']} " . number_format($calculation['prorated_amount_to_add'], 2) . "."
            : "Proration calculated for downgrade to {$calculation['target_plan_name']}: Credit carryover is {$calculation['currency']} " . number_format($calculation['credit_carryover'], 2) . ".";

        return back()->with('tier_proration_result', $calculation)->with('success', $msg);
    }

    /**
     * Submit an ISP Upgrade or Downgrade Request with mid-cycle proration.
     */
    public function requestTierChange(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        \App\Models\SaasPlan::ensureDefaultPlansExist();

        $validated = $request->validate([
            'target_plan_id'   => 'required|exists:saas_plans,id',
            'billing_interval' => 'nullable|in:monthly,annually',
            'remaining_days'   => 'nullable|integer|min:1|max:365',
            'total_cycle_days' => 'nullable|integer|min:1|max:365',
            'effective_mode'   => 'nullable|in:immediate,end_of_cycle',
            'payment_method'   => 'nullable|in:mpesa,bank_transfer,card,manual',
            'pay_now'          => 'nullable|boolean',
            'auto_approve'     => 'nullable|boolean',
            'reason_notes'     => 'nullable|string|max:1000',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? \App\Models\Tenant::find($tenantId) : null) ?? \App\Models\Tenant::firstOrFail();
        $targetPlan = \App\Models\SaasPlan::findOrFail($validated['target_plan_id']);

        $changeRequest = $lifecycle->submitPlanChangeRequest(
            $tenant,
            $targetPlan,
            $request->user(),
            $validated
        );

        if ($changeRequest->status === \App\Models\SaasPlanChangeRequest::STATUS_COMPLETED) {
            return back()
                ->with('last_plan_change_request', $changeRequest)
                ->with('success', "Tier {$changeRequest->change_type} to '{$targetPlan->name}' completed! (Ref: {$changeRequest->reference_number}, Prorated Top-Up: {$changeRequest->currency} " . number_format($changeRequest->prorated_amount_to_add, 2) . ").");
        }

        if ($changeRequest->isUpgrade()) {
            return back()
                ->with('last_plan_change_request', $changeRequest)
                ->with('success', "Upgrade request #{$changeRequest->reference_number} to '{$targetPlan->name}' created! Prorated amount to add for the remaining {$changeRequest->remaining_days} days: {$changeRequest->currency} " . number_format($changeRequest->prorated_amount_to_add, 2) . " (Unused credit deducted: {$changeRequest->currency} " . number_format($changeRequest->unused_credit, 2) . ").");
        }

        return back()
            ->with('last_plan_change_request', $changeRequest)
            ->with('success', "Downgrade request #{$changeRequest->reference_number} to '{$targetPlan->name}' submitted! Prorated credit carryover calculated: {$changeRequest->currency} " . number_format($changeRequest->credit_carryover, 2) . ".");
    }

    /**
     * Pay the prorated top-up amount for a pending upgrade request and activate the higher tier.
     */
    public function payTierUpgradeRequest(
        Request $request,
        \App\Models\SaasPlanChangeRequest $changeRequest,
        \App\Services\SaaS\SubscriptionLifecycleService $lifecycle
    ) {
        $validated = $request->validate([
            'provider' => 'nullable|in:mpesa,bank_transfer,card,manual',
            'phone'    => 'nullable|string|max:30',
        ]);

        $user = $request->user();
        if (!$user->isSuperAdmin() && $changeRequest->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized tenant plan upgrade payment.');
        }

        $provider = $validated['provider'] ?? $changeRequest->payment_method ?? 'mpesa';
        $ref = 'UPG-PAY-' . strtoupper(\Illuminate\Support\Str::random(6));

        $lifecycle->payAndCompleteUpgradeRequest($changeRequest, $provider, $ref, $user);

        return back()->with('success', "Prorated upgrade top-up of {$changeRequest->currency} " . number_format($changeRequest->prorated_amount_to_add, 2) . " paid (Ref: {$ref})! Your ISP tier is now upgraded to {$changeRequest->targetPlan->name}.");
    }

    /**
     * Cancel a pending ISP tier upgrade/downgrade request.
     */
    public function cancelTierChangeRequest(Request $request, \App\Models\SaasPlanChangeRequest $changeRequest)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && $changeRequest->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($changeRequest->isPending()) {
            $changeRequest->update(['status' => \App\Models\SaasPlanChangeRequest::STATUS_CANCELLED]);
            if ($changeRequest->invoice && $changeRequest->invoice->status === 'pending') {
                $changeRequest->invoice->update(['status' => 'void']);
            }
        }

        return back()->with('success', "Plan change request {$changeRequest->reference_number} has been cancelled.");
    }
}
