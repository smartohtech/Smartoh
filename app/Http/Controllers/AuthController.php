<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
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

            if ($user->isClient()) {
                return redirect()->route('client.portal');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name'      => 'required|string|max:255',
            'subdomain'         => 'required|string|max:100|alpha_dash|unique:tenants,subdomain',
            'company_email'     => 'required|email|max:255',
            'company_phone'     => 'required|string|max:30',
            'subscription_tier' => 'required|in:starter,professional,enterprise',
            'admin_name'        => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'phone'             => 'required|string|max:30',
            'password'          => 'required|string|min:8|confirmed',
            'terms'             => 'accepted',
            'network_type'      => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name'              => $request->company_name,
                'subdomain'         => strtolower($request->subdomain),
                'company_email'     => $request->company_email,
                'company_phone'     => $request->company_phone,
                'address'           => $request->address,
                'subscription_tier' => $request->subscription_tier,
                'status'            => 'pending', // Awaiting super-admin approval
                'brand_color'       => '#1565C0',
                'accent_color'      => '#00BCD4',
                'settings'          => [
                    'network_type'      => $request->network_type,
                    'approx_subscribers'=> $request->approx_subscribers,
                    'router_brand'      => $request->router_brand,
                    'payment_methods'   => $request->payment_methods ?? [],
                    'referral_source'   => $request->referral_source,
                    'onboarded_at'      => now()->toISOString(),
                ],
            ]);

            User::create([
                'name'        => $request->admin_name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'tenant_id'   => $tenant->id,
                'role'        => 'isp_admin',
                'is_approved' => false,
            ]);
        });

        return redirect()->route('login')->with('success',
            'Your ISP account has been submitted! You will receive a confirmation once approved by our team.'
        );
    }

    public function switchTenant(Request $request)
    {
        $tenantId = (int) $request->input('tenant_id');
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
