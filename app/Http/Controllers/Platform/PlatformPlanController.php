<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SaasPlan;
use App\Models\SaasPlanVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformPlanController extends Controller
{
    public function index()
    {
        $plans = SaasPlan::with(['versions', 'currentVersion'])
            ->withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => 'required|string|max:50|unique:saas_plans,slug',
            'description'      => 'nullable|string|max:500',
            'monthly_price'    => 'required|numeric|min:0',
            'annual_price'     => 'required|numeric|min:0',
            'currency'         => 'required|string|max:10',
            'trial_days'       => 'required|integer|min:0|max:90',
            'max_subscribers'  => 'required|integer|min:1',
            'max_routers'      => 'required|integer|min:1',
            'max_staff'        => 'required|integer|min:1',
            'max_sites'        => 'required|integer|min:1',
            'has_api_access'   => 'nullable|boolean',
            'has_advanced_noc' => 'nullable|boolean',
            'has_white_label'  => 'nullable|boolean',
            'has_gis_maps'     => 'nullable|boolean',
        ]);

        $features = [
            'max_subscribers'   => (int)$validated['max_subscribers'],
            'max_routers'       => (int)$validated['max_routers'],
            'max_staff'         => (int)$validated['max_staff'],
            'max_sites'         => (int)$validated['max_sites'],
            'has_api_access'    => !empty($validated['has_api_access']),
            'has_advanced_noc'  => !empty($validated['has_advanced_noc']),
            'has_white_label'   => !empty($validated['has_white_label']),
            'has_gis_maps'      => !empty($validated['has_gis_maps']),
            'has_mpesa_daraja'  => true,
            'has_adie_discovery'=> true,
        ];

        $plan = SaasPlan::create([
            'name'          => $validated['name'],
            'slug'          => Str::slug($validated['slug']),
            'description'   => $validated['description'],
            'monthly_price' => $validated['monthly_price'],
            'annual_price'  => $validated['annual_price'],
            'currency'      => $validated['currency'],
            'trial_days'    => $validated['trial_days'],
            'is_active'     => true,
            'sort_order'    => SaasPlan::count() + 1,
        ]);

        SaasPlanVersion::create([
            'saas_plan_id'   => $plan->id,
            'version_number' => 1,
            'features'       => $features,
            'monthly_price'  => $plan->monthly_price,
            'annual_price'   => $plan->annual_price,
            'currency'       => $plan->currency,
            'changelog'      => 'Initial release',
            'is_current'     => true,
        ]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'SAAS_PLAN_CREATED',
            'details'   => "Created new SaaS Plan '{$plan->name}' with version 1.0.",
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('platform.plans.index')->with('success', "Plan '{$plan->name}' successfully created.");
    }

    public function update(Request $request, SaasPlan $plan)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:500',
            'monthly_price'    => 'required|numeric|min:0',
            'annual_price'     => 'required|numeric|min:0',
            'currency'         => 'required|string|max:10',
            'trial_days'       => 'required|integer|min:0|max:90',
            'changelog'        => 'nullable|string|max:255',
            'max_subscribers'  => 'required|integer|min:1',
            'max_routers'      => 'required|integer|min:1',
            'max_staff'        => 'required|integer|min:1',
            'max_sites'        => 'required|integer|min:1',
            'has_api_access'   => 'nullable|boolean',
            'has_advanced_noc' => 'nullable|boolean',
            'has_white_label'  => 'nullable|boolean',
            'has_gis_maps'     => 'nullable|boolean',
        ]);

        $features = [
            'max_subscribers'   => (int)$validated['max_subscribers'],
            'max_routers'       => (int)$validated['max_routers'],
            'max_staff'         => (int)$validated['max_staff'],
            'max_sites'         => (int)$validated['max_sites'],
            'has_api_access'    => !empty($validated['has_api_access']),
            'has_advanced_noc'  => !empty($validated['has_advanced_noc']),
            'has_white_label'   => !empty($validated['has_white_label']),
            'has_gis_maps'      => !empty($validated['has_gis_maps']),
            'has_mpesa_daraja'  => true,
            'has_adie_discovery'=> true,
        ];

        // Update parent plan current prices
        $plan->update([
            'name'          => $validated['name'],
            'description'   => $validated['description'],
            'monthly_price' => $validated['monthly_price'],
            'annual_price'  => $validated['annual_price'],
            'currency'      => $validated['currency'],
            'trial_days'    => $validated['trial_days'],
        ]);

        // Mark previous versions as non-current
        $plan->versions()->update(['is_current' => false]);

        // Create new incremented version (preserves existing contracts)
        $latestVersionNumber = $plan->versions()->max('version_number') ?? 0;
        $newVersionNumber = $latestVersionNumber + 1;

        SaasPlanVersion::create([
            'saas_plan_id'   => $plan->id,
            'version_number' => $newVersionNumber,
            'features'       => $features,
            'monthly_price'  => $plan->monthly_price,
            'annual_price'   => $plan->annual_price,
            'currency'       => $plan->currency,
            'changelog'      => $validated['changelog'] ?? "Updated to version {$newVersionNumber}",
            'is_current'     => true,
        ]);

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'SAAS_PLAN_VERSION_UPDATED',
            'details'   => "Updated plan '{$plan->name}' to version {$newVersionNumber}. Prior subscription contracts preserved.",
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('platform.plans.index')->with('success', "Plan '{$plan->name}' updated to version {$newVersionNumber}.");
    }

    public function toggleStatus(SaasPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $statusStr = $plan->is_active ? 'activated' : 'archived';

        return back()->with('success', "Plan '{$plan->name}' is now {$statusStr}.");
    }
}
