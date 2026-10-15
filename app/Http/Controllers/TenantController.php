<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount(['clients', 'mikrotikDevices', 'users'])->get();
        return view('tenants.index', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|unique:tenants,subdomain',
            'custom_domain' => 'nullable|string|unique:tenants,custom_domain',
            'company_email' => 'required|email',
            'company_phone' => 'required|string',
            'subscription_tier' => 'required|in:starter,pro,enterprise',
            'brand_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
            'admin_name' => 'required|string',
            'admin_password' => 'required|string|min:8',
        ]);

        $maxRouters = match ($validated['subscription_tier']) {
            'enterprise' => 50,
            'pro' => 20,
            default => 5,
        };

        $maxClients = match ($validated['subscription_tier']) {
            'enterprise' => 10000,
            'pro' => 2000,
            default => 300,
        };

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'subdomain' => strtolower($validated['subdomain']),
            'custom_domain' => $validated['custom_domain'],
            'company_email' => $validated['company_email'],
            'company_phone' => $validated['company_phone'],
            'brand_color' => $validated['brand_color'] ?: '#0284c7',
            'accent_color' => $validated['accent_color'] ?: '#0ea5e9',
            'subscription_tier' => $validated['subscription_tier'],
            'status' => 'active', // Approved by platform owner
            'max_routers' => $maxRouters,
            'max_clients' => $maxClients,
        ]);

        // Create Tenant Administrator User
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['company_email'],
            'phone' => $validated['company_phone'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'isp_admin',
            'is_approved' => true,
        ]);

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'event' => 'tenant.onboarded',
            'auditable_type' => Tenant::class,
            'auditable_id' => $tenant->id,
            'new_values' => ['name' => $tenant->name, 'subdomain' => $tenant->subdomain, 'tier' => $tenant->subscription_tier],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Tenant '{$tenant->name}' onboarded successfully on subdomain '{$tenant->subdomain}.smartoh.net'!");
    }

    public function updateBranding(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'brand_color' => 'required|string',
            'accent_color' => 'required|string',
            'theme_mode' => 'required|in:dark,light,system',
            'custom_domain' => 'nullable|string',
        ]);

        $tenant->update($validated);
        return back()->with('success', 'White-label branding and domain updated successfully!');
    }
}
