<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Ensure the authenticated user has the platform super_admin role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isSuperAdmin()) {
            abort(403, 'Access denied. You do not have Super Admin permissions to access the Smartoh Platform Control Panel.');
        }

        return $next($request);
    }
}
