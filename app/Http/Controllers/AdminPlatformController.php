<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Billing\BillingEngine;
use Illuminate\Http\Request;

class AdminPlatformController extends Controller
{
    /**
     * Platform-level ISP Approvals (Super Admin Only).
     */
    public function ispApprovals()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. ISP approval review is restricted to Platform Super Administrators.');
        }

        $pendingTenants = Tenant::where('status', 'pending')->get();
        $activeTenants = Tenant::where('status', 'active')->get();

        return view('admin.isp_approvals', compact('pendingTenants', 'activeTenants'));
    }

    /**
     * Platform-level ISP Approval Action (Super Admin Only).
     */
    public function approveIsp(Request $request, Tenant $tenant)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. ISP approval is restricted to Platform Super Administrators.');
        }

        $tenant->update(['status' => 'active']);
        return back()->with('success', "ISP Tenant '{$tenant->name}' has been approved and activated!");
    }

    /**
     * Staff Role Approvals: Scoped strictly to the authenticated ISP's tenant.
     */
    public function userApprovals()
    {
        $user = auth()->user();
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : $user->tenant_id;

        $query = User::with('tenant');
        if (!$user->isSuperAdmin()) {
            $query->where('tenant_id', $tenantId);
        }

        $users = $query->latest()->paginate(15);
        return view('admin.user_approvals', compact('users'));
    }

    /**
     * Staff Role Approval Action: Validates tenant ownership.
     */
    public function approveUser(Request $request, User $user)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && $user->tenant_id !== $authUser->tenant_id) {
            abort(403, 'Unauthorized. You may only approve staff members within your organization.');
        }

        $user->update(['is_approved' => true]);
        return back()->with('success', "Staff user '{$user->name}' approved with role '{$user->role}'.");
    }

    /**
     * Company Branding & White-Label: Scoped to the authenticated ISP's tenant.
     */
    public function tenantBranding()
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? Tenant::find($tenantId) : null)
            ?? auth()->user()?->tenant
            ?? Tenant::first()
            ?? Tenant::create(['name' => 'Smartoh Technologies', 'status' => 'active']);

        return view('admin.tenant_branding', compact('tenant'));
    }

    /**
     * System Settings: ISP-specific operational settings.
     */
    public function systemSettings()
    {
        return view('admin.system_settings');
    }

    /**
     * Audit Trail Logs: Strictly scoped to actions taken by staff within this ISP tenant.
     */
    public function auditLogs()
    {
        $user = auth()->user();
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : $user->tenant_id;

        $query = AuditLog::with('user');
        if (!$user->isSuperAdmin()) {
            $query->where('tenant_id', $tenantId);
        }

        $logs = $query->latest()->paginate(20);
        return view('admin.audit_logs', compact('logs'));
    }

    /**
     * Mid-Cycle Proration: Under Billing & Invoicing Settings.
     */
    public function proration(BillingEngine $engine)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()->tenant_id;

        $plans = BillingPlan::where('tenant_id', $tenantId)->where('is_active', true)->get();
        if ($plans->isEmpty() && auth()->user()->isSuperAdmin()) {
            $plans = BillingPlan::where('is_active', true)->get();
        }

        $sampleClient = Client::where('tenant_id', $tenantId)->with('billingPlan')->first();

        return view('admin.proration', compact('plans', 'sampleClient'));
    }

    public function calculateProration(Request $request, BillingEngine $engine)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'new_plan_id' => 'required|exists:billing_plans,id',
        ]);

        $client = Client::with('billingPlan')->findOrFail($request->client_id);
        $newPlan = BillingPlan::findOrFail($request->new_plan_id);
        $oldPlan = $client->billingPlan ?? $newPlan;

        // Tenant ownership check
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $client->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized cross-tenant client proration request.');
        }

        $calculation = $engine->calculateProration($oldPlan, $newPlan, $client);
        $netAdjustment = $calculation['new_charge'] - $calculation['unused_credit'];
        $calculation['net_adjustment'] = $netAdjustment;

        return back()->with('proration_result', $calculation)->with('success', "Prorated calculation complete: Net Adjustment KES {$netAdjustment}");
    }

    /**
     * Subscription & System Quotas (Parity view for /admin/system-plan & /SystemPlan)
     */
    public function systemPlan(\App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        \App\Models\SaasPlan::ensureDefaultPlansExist();

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $tenant = ($tenantId ? Tenant::find($tenantId) : null)
            ?? auth()->user()?->tenant
            ?? Tenant::first()
            ?? Tenant::create(['name' => 'Smartoh Technologies', 'status' => 'active']);

        $subscription = $lifecycle->ensureTenantHasSubscription($tenant);
        $plans = \App\Models\SaasPlan::where('is_active', true)->with('currentVersion')->orderBy('sort_order')->get();
        $changeRequests = $tenant->planChangeRequests()->with(['currentPlan', 'targetPlan', 'invoice'])->take(10)->get();

        $prorationPreviews = [];
        foreach ($plans as $plan) {
            $prorationPreviews[$plan->id] = $lifecycle->calculateTierProration($tenant, $plan);
        }

        return view('admin.system_plan', compact(
            'tenant',
            'subscription',
            'plans',
            'changeRequests',
            'prorationPreviews'
        ));
    }

    public function previewSystemPlanProration(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        return app(BillingController::class)->previewTierProration($request, $lifecycle);
    }

    public function requestSystemPlanChange(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        return app(BillingController::class)->requestTierChange($request, $lifecycle);
    }
}
