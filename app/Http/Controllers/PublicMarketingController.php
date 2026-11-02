<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\IspApplication;
use App\Models\SaasPlan;
use Illuminate\Http\Request;

class PublicMarketingController extends Controller
{
    public function features()
    {
        return view('features');
    }

    public function pricing()
    {
        SaasPlan::ensureDefaultPlansExist();

        $plans = SaasPlan::where('is_active', true)
            ->with('currentVersion')
            ->orderBy('sort_order')
            ->get();

        return view('pricing', compact('plans'));
    }

    public function applicationStatus(Request $request)
    {
        $application = null;

        if ($query = $request->input('ref')) {
            $application = IspApplication::with(['saasPlan.currentVersion', 'provisionedTenant'])
                ->where('application_number', trim($query))
                ->orWhere('email', trim($query))
                ->latest()
                ->first();
        }

        return view('auth.application_status', compact('application'));
    }

    public function submitInfoResponse(Request $request, IspApplication $application)
    {
        $validated = $request->validate([
            'resubmitted_notes' => 'required|string|min:10|max:2000',
        ]);

        $application->update([
            'status'            => 'pending_approval',
            'resubmitted_notes' => $validated['resubmitted_notes'],
        ]);

        AuditLog::create([
            'action'    => 'ISP_APPLICATION_INFO_RESUBMITTED',
            'details'   => "Applicant {$application->email} provided requested details for {$application->application_number}.",
            'ip_address'=> $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', 'Your additional information has been submitted. Our team will review your application shortly!');
    }
}
