<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\IspApplication;
use App\Models\SaasPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('platform.dashboard');
            }
            if ($user->isClient()) {
                return redirect()->route('client.portal');
            }
            return redirect()->route('dashboard');
        }

        $tenants = Tenant::where('status', 'active')->get();
        return view('auth.login', compact('tenants'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->tenant_id) {
                session(['active_tenant_id' => $user->tenant_id]);
            }

            // Role-based strict routing (never trust browser-supplied state)
            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('platform.dashboard'));
            }

            if ($user->isClient()) {
                return redirect()->route('client.portal');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        SaasPlan::ensureDefaultPlansExist();

        $plans = SaasPlan::where('is_active', true)->with('currentVersion')->orderBy('sort_order')->get();
        $selectedPlanSlug = $request->query('plan', 'pro');

        return view('auth.register', compact('plans', 'selectedPlanSlug'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name'         => 'required|string|max:255',
            'subdomain'            => 'required|string|max:80|alpha_dash|unique:tenants,subdomain|unique:isp_applications,subdomain',
            'contact_name'         => 'required|string|max:255',
            'email'                => 'required|email|max:255|unique:users,email|unique:isp_applications,email',
            'phone'                => 'required|string|max:50',
            'country'              => 'required|string|max:100',
            'address'              => 'nullable|string|max:500',
            'network_type'         => 'required|string',
            'current_subscribers'  => 'nullable|integer|min:0',
            'hardware_type'        => 'nullable|string|max:100',
            'saas_plan_id'         => 'required|exists:saas_plans,id',
            'billing_interval'     => 'required|in:monthly,annually',
            'password'             => 'required|string|min:8|confirmed',
            'terms'                => 'accepted',
        ]);

        $plan = SaasPlan::with('currentVersion')->findOrFail($request->saas_plan_id);
        $version = $plan->currentVersion ?? $plan->versions()->first();

        $appRef = 'APP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $application = IspApplication::create([
            'application_number'   => $appRef,
            'company_name'         => $request->company_name,
            'subdomain'            => strtolower($request->subdomain),
            'contact_name'         => $request->contact_name,
            'email'                => $request->email,
            'phone'                => $request->phone,
            'country'              => $request->country,
            'address'              => $request->address,
            'network_type'         => $request->network_type,
            'current_subscribers'  => (int)$request->current_subscribers,
            'hardware_type'        => $request->hardware_type,
            'saas_plan_id'         => $plan->id,
            'saas_plan_version_id' => $version?->id,
            'billing_interval'     => $request->billing_interval,
            'status'               => 'pending_approval',
            'admin_temp_password'  => $request->password, // Stored encrypted or hashed upon provisioning
        ]);

        AuditLog::create([
            'action'    => 'ISP_APPLICATION_SUBMITTED',
            'details'   => "New application {$appRef} submitted by {$request->company_name} ({$request->email}) for {$plan->name}.",
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('application.status', ['ref' => $appRef])->with('success',
            "Your application ({$appRef}) has been submitted! Our Super Admin team will review your business credentials and activate your 7-day free trial."
        );
    }

    public function switchTenant(Request $request)
    {
        $tenantId = (int) $request->input('tenant_id');
        $user = Auth::user();

        // Strict multitenant isolation: only Super Admin can switch tenants arbitrarily
        if (!$user->isSuperAdmin() && $user->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized cross-tenant switch.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        session(['active_tenant_id' => $tenant->id]);
        return back()->with('success', "Switched workspace to {$tenant->name}");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
