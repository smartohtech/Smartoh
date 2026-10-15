<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Services\SaaS\SubscriptionLifecycleService;
use Illuminate\Http\Request;

class PlatformTenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['activeSubscription.plan', 'users'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('company_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($tier = $request->input('tier')) {
            $query->where('subscription_tier', $tier);
        }

        $tenants = $query->paginate(15)->withQueryString();
        $plans = SaasPlan::all();

        return view('platform.tenants.index', compact('tenants', 'plans'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load([
            'users',
            'sites',
            'activeSubscription.plan',
            'activeSubscription.trialEvents',
            'saasSubscriptions.plan',
            'saasInvoices',
            'usage',
            'application',
        ]);

        $auditLogs = AuditLog::where('tenant_id', $tenant->id)->latest()->take(20)->get();

        return view('platform.tenants.show', compact('tenant', 'auditLogs'));
    }

    public function suspend(Request $request, Tenant $tenant, SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $lifecycle->suspendTenant($tenant, $request->user(), $validated['reason']);

        return back()->with('success', "Tenant '{$tenant->name}' has been suspended. Operational access restricted while data is safely preserved.");
    }

    public function reinstate(Request $request, Tenant $tenant, SubscriptionLifecycleService $lifecycle)
    {
        $lifecycle->reinstateTenant($tenant, $request->user());

        return back()->with('success', "Tenant '{$tenant->name}' has been successfully reinstated to active status.");
    }

    public function impersonate(Tenant $tenant)
    {
        session(['admin_active_tenant_id' => $tenant->id]);
        return redirect()->route('dashboard')->with('info', "Viewing workspace for '{$tenant->name}' as Super Admin.");
    }
}
