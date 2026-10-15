<?php

namespace App\Http\Middleware;

use App\Models\SaasSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionStateMiddleware
{
    /**
     * Enforce 12-state SaaS subscription lifecycle rules.
     * Restricts expired/suspended tenants to billing, payments, and support.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Bypass check for unauthenticated users, Super Admins, and end-clients
        if (!$user || $user->isSuperAdmin() || $user->isClient()) {
            return $next($request);
        }

        $tenant = $user->tenant;
        if (!$tenant) {
            return $next($request);
        }

        // 2. Suspended tenant check
        if ($tenant->status === 'suspended') {
            if ($request->is('logout') || $request->is('tickets*')) {
                return $next($request);
            }
            return response()->view('errors.account_suspended', ['tenant' => $tenant], 403);
        }

        // 3. Pending approval check
        if ($tenant->status === 'pending') {
            if ($request->is('logout') || $request->is('application-status*') || $request->is('tickets*')) {
                return $next($request);
            }
            return redirect()->route('application.status');
        }

        // 4. Subscription State Restriction Check
        $subscription = $tenant->activeSubscription;
        if ($subscription) {
            $restrictedStates = [
                SaasSubscription::STATUS_TRIAL_EXPIRED,
                SaasSubscription::STATUS_PAYMENT_PENDING,
                SaasSubscription::STATUS_PAST_DUE,
                SaasSubscription::STATUS_CANCELLED,
            ];

            if (in_array($subscription->status, $restrictedStates, true)) {
                // Allowed routes during restricted subscription state (payment, invoices, support, logout)
                $allowedPrefixes = [
                    'logout',
                    'billing/subscription',
                    'billing/saas-invoices',
                    'billing/pay',
                    'tickets',
                ];

                $isAllowed = false;
                foreach ($allowedPrefixes as $prefix) {
                    if ($request->is($prefix) || $request->is($prefix . '/*')) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    return redirect()->route('billing.subscription')
                        ->with('warning', 'Your free trial or subscription has expired. Operational modules (NOC, Router Control, Device Discovery) are locked until payment is confirmed. All customer and network data is safely preserved.');
                }
            }
        }

        return $next($request);
    }
}
