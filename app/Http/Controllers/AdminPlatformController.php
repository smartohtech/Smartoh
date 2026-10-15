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
    public function ispApprovals()
    {
        $pendingTenants = Tenant::where('status', 'pending')->get();
        $activeTenants = Tenant::where('status', 'active')->get();

        return view('admin.isp_approvals', compact('pendingTenants', 'activeTenants'));
    }

    public function approveIsp(Request $request, Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return back()->with('success', "ISP Tenant '{$tenant->name}' has been approved and activated!");
    }

    public function userApprovals()
    {
        $users = User::with('tenant')->latest()->paginate(15);
        return view('admin.user_approvals', compact('users'));
    }

    public function approveUser(Request $request, User $user)
    {
        $user->update(['is_approved' => true]);
        return back()->with('success', "User '{$user->name}' approved with role '{$user->role}'.");
    }

    public function tenantBranding()
    {
        $tenant = app()->bound('current_tenant_id') ? Tenant::find(app('current_tenant_id')) : Tenant::first();
        return view('admin.tenant_branding', compact('tenant'));
    }

    public function systemSettings()
    {
        return view('admin.system_settings');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest()->paginate(20);
        return view('admin.audit_logs', compact('logs'));
    }

    public function proration(BillingEngine $engine)
    {
        $plans = BillingPlan::where('is_active', true)->get();
        $sampleClient = Client::with('billingPlan')->first();

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

        $calculation = $engine->calculateProration($oldPlan, $newPlan, $client);
        $netAdjustment = $calculation['new_charge'] - $calculation['unused_credit'];
        $calculation['net_adjustment'] = $netAdjustment;

        return back()->with('proration_result', $calculation)->with('success', "Prorated calculation complete: Net Adjustment KES {$netAdjustment}");
    }

    public function systemPlan()
    {
        $tenant = app()->bound('current_tenant_id') ? Tenant::find(app('current_tenant_id')) : Tenant::first();
        return view('admin.system_plan', compact('tenant'));
    }
}
