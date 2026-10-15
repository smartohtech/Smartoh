<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MikroTikDevice;
use App\Models\NOCIncident;
use App\Models\ONTDevice;
use App\Models\Technician;
use App\Services\AI\AIOperationsAssistant;
use App\Services\NOC\CapacityTelemetryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NocController extends Controller
{
    public function index(AIOperationsAssistant $aiAssistant, CapacityTelemetryService $capacityService)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;

        $incidents = NOCIncident::with('technician.user')->latest('started_at')->get();
        $technicians = Technician::with('user')->where('is_available', true)->get();

        $routers = MikroTikDevice::all();
        $onts = ONTDevice::with('client')->get();

        $aiFindings = $aiAssistant->scanNetworkHealth($tenantId);

        $severityCounts = [
            'critical' => NOCIncident::where('severity', 'critical')->where('status', '!=', 'resolved')->count(),
            'warning' => NOCIncident::where('severity', 'warning')->where('status', '!=', 'resolved')->count(),
            'info' => NOCIncident::where('severity', 'info')->where('status', '!=', 'resolved')->count(),
        ];

        // Retrieve full ISP capacity & real-time telemetry metrics
        $capacityMetrics = $capacityService->getCapacityMetrics($tenantId);

        return view('noc.index', compact(
            'incidents',
            'technicians',
            'routers',
            'onts',
            'aiFindings',
            'severityCounts',
            'capacityMetrics'
        ));
    }

    /**
     * Real-time capacity and throughput polling endpoint for NOC live stream
     */
    public function capacityTelemetry(Request $request, CapacityTelemetryService $capacityService)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
        $metrics = $capacityService->getCapacityMetrics($tenantId);

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Update the ISP's upstream contracted capacity
     */
    public function updateCapacity(Request $request, CapacityTelemetryService $capacityService)
    {
        $validated = $request->validate([
            'capacity_mbps' => 'required|integer|min:100|max:10000000',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : 1;
        $capacityService->updateUpstreamCapacity($tenantId, (int)$validated['capacity_mbps']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Upstream capacity updated successfully.',
                'capacity_formatted' => $capacityService->formatSpeed($validated['capacity_mbps']),
            ]);
        }

        return back()->with('success', "Upstream capacity updated to " . $capacityService->formatSpeed($validated['capacity_mbps']) . ".");
    }

    public function storeIncident(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'affected_component' => 'required|string',
            'severity' => 'required|in:info,warning,critical,fatal',
            'assigned_technician_id' => 'nullable|exists:technicians,id',
            'description' => 'nullable|string',
        ]);

        $incident = NOCIncident::create(array_merge($validated, [
            'incident_number' => 'INC-' . strtoupper(Str::random(6)),
            'status' => 'open',
            'started_at' => now(),
        ]));

        AuditLog::create([
            'tenant_id' => $incident->tenant_id,
            'user_id' => auth()->id(),
            'event' => 'noc.incident_opened',
            'auditable_type' => NOCIncident::class,
            'auditable_id' => $incident->id,
            'new_values' => ['title' => $incident->title, 'severity' => $incident->severity],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "NOC Incident {$incident->incident_number} logged.");
    }

    public function resolveIncident(NOCIncident $incident)
    {
        $incident->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return back()->with('success', "Incident {$incident->incident_number} marked as resolved.");
    }

    public function askAi(Request $request, AIOperationsAssistant $aiAssistant)
    {
        $request->validate(['query' => 'required|string']);
        $answer = $aiAssistant->answerQuery($request->query('query', ''));

        return response()->json(['success' => true, 'answer' => $answer]);
    }
}
