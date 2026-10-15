<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\IspApplication;
use App\Models\SaasPlan;
use App\Services\SaaS\SubscriptionLifecycleService;
use Illuminate\Http\Request;

class PlatformApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = IspApplication::with(['saasPlan', 'reviewer', 'provisionedTenant'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($planId = $request->input('plan_id')) {
            $query->where('saas_plan_id', $planId);
        }

        $applications = $query->paginate(15)->withQueryString();
        $plans = SaasPlan::all();

        return view('platform.applications.index', compact('applications', 'plans'));
    }

    public function show(IspApplication $application)
    {
        $application->load(['saasPlan.currentVersion', 'reviewer', 'provisionedTenant.activeSubscription']);
        $plans = SaasPlan::where('is_active', true)->get();

        return view('platform.applications.show', compact('application', 'plans'));
    }

    public function approve(Request $request, IspApplication $application, SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'negotiated_price' => 'nullable|numeric|min:0',
            'approval_notes'   => 'nullable|string|max:1000',
        ]);

        $customPrice = !empty($validated['negotiated_price']) ? (float)$validated['negotiated_price'] : null;

        $result = $lifecycle->idempotentApproveApplication(
            $application,
            $request->user(),
            $customPrice,
            $validated['approval_notes'] ?? null
        );

        $msg = $result['is_duplicate']
            ? "Application {$application->application_number} was already approved. Retrieved existing tenant workspace."
            : "Application {$application->application_number} successfully approved! Tenant '{$result['tenant']->name}' provisioned and 7-day trial activated.";

        return redirect()->route('platform.applications.show', $application)->with('success', $msg);
    }

    public function reject(Request $request, IspApplication $application, SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $lifecycle->rejectApplication($application, $request->user(), $validated['rejection_reason']);

        return redirect()->route('platform.applications.show', $application)
            ->with('success', "Application {$application->application_number} marked as Rejected.");
    }

    public function requestInfo(Request $request, IspApplication $application, SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'info_request_notes' => 'required|string|min:5|max:1000',
        ]);

        $lifecycle->requestAdditionalInfo($application, $request->user(), $validated['info_request_notes']);

        return redirect()->route('platform.applications.show', $application)
            ->with('success', "Additional information requested from applicant {$application->email}.");
    }
}
