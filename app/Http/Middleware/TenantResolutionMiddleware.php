<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantResolutionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = null;

        // 1. From authenticated user
        if ($user = $request->user()) {
            if ($user->tenant_id) {
                $tenantId = $user->tenant_id;
            } elseif ($user->isSuperAdmin() && session()->has('admin_active_tenant_id')) {
                $tenantId = session('admin_active_tenant_id');
            }
        }

        // 2. From session or query string override for preview
        if (!$tenantId) {
            if ($request->has('tenant')) {
                $tenantId = (int) $request->get('tenant');
                session(['active_tenant_id' => $tenantId]);
            } elseif (session()->has('active_tenant_id')) {
                $tenantId = session('active_tenant_id');
            }
        }

        // 3. Fallback to first active tenant if none selected
        if (!$tenantId) {
            $firstTenant = Tenant::where('status', 'active')->first();
            if ($firstTenant) {
                $tenantId = $firstTenant->id;
            }
        }

        if ($tenantId) {
            app()->instance('current_tenant_id', $tenantId);
            $tenant = Tenant::find($tenantId);
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
