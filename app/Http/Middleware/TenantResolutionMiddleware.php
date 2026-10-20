<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantResolutionMiddleware
{
    /**
     * Resolve and bind tenant context with strict multitenant isolation.
     * Prevents cross-tenant access and insecure fallbacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetInstance('current_tenant_id');
        $tenantId = null;
        $user = $request->user();

        // 1. Authenticated tenant user (ISP Admin, Technician, Billing Officer, Client)
        if ($user) {
            if ($user->tenant_id) {
                $tenantId = (int) $user->tenant_id;

                // Security check: If request tries to override with a different tenant_id in query/body, reject immediately
                if ($request->has('tenant_id') && (int) $request->input('tenant_id') !== $tenantId) {
                    abort(403, 'Cross-tenant access violation: You are not authorized to access records belonging to another tenant.');
                }
            } elseif ($user->isSuperAdmin()) {
                // Platform Super Admin: only bind a tenant context if explicitly impersonating or viewing a specific tenant workspace
                if ($request->has('impersonate_tenant')) {
                    $tenantId = (int) $request->get('impersonate_tenant');
                    session(['admin_active_tenant_id' => $tenantId]);
                } elseif (session()->has('admin_active_tenant_id') && !$request->is('platform*')) {
                    $tenantId = (int) session('admin_active_tenant_id');
                }
            }
        }

        // 2. Resolve subdomain for tenant public portals (hotspot, client portal)
        if (!$tenantId) {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0] ?? null;
            if ($subdomain && !in_array($subdomain, ['www', 'localhost', '127', 'smartoh', 'api', 'app'], true)) {
                $tenant = Tenant::where('subdomain', $subdomain)->where('status', 'active')->first();
                if ($tenant) {
                    $tenantId = $tenant->id;
                }
            }
        }

        // Strictly bind tenant context if validly resolved (NEVER fall back to first active tenant)
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                app()->instance('current_tenant_id', $tenantId);
                view()->share('currentTenant', $tenant);

                // Load active subscription into view if available
                $subscription = $tenant->activeSubscription;
                view()->share('currentSubscription', $subscription);
            }
        }

        return $next($request);
    }
}
