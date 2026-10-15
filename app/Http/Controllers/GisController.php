<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FiberInfrastructure;
use App\Models\InstallationJob;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GisController extends Controller
{
    public function map()
    {
        $infrastructure = FiberInfrastructure::all();
        $clients = Client::whereNotNull('latitude')->whereNotNull('longitude')->get();
        $jobs = InstallationJob::with(['client', 'technician.user'])->whereIn('status', ['scheduled', 'in_progress'])->get();

        return view('gis.map', compact('infrastructure', 'clients', 'jobs'));
    }

    public function jobs()
    {
        $jobs = InstallationJob::with(['client', 'technician.user'])->latest('scheduled_at')->paginate(15);
        $technicians = Technician::with('user')->where('is_available', true)->get();
        $fatBoxes = FiberInfrastructure::where('type', 'fat_box')->get();
        $pendingClients = Client::where('status', 'pending_installation')->orWhere('status', 'active')->get();

        return view('gis.jobs', compact('jobs', 'technicians', 'fatBoxes', 'pendingClients'));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'fiber_box_id' => 'nullable|exists:fiber_infrastructure,id',
            'scheduled_at' => 'required|date',
            'fiber_cable_meters' => 'nullable|integer',
            'technician_notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $job = InstallationJob::create(array_merge($validated, [
            'job_number' => 'JOB-' . strtoupper(Str::random(6)),
            'status' => 'scheduled',
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
        ]));

        return back()->with('success', "Installation Work Order {$job->job_number} dispatched.");
    }

    public function completeJob(InstallationJob $job, Request $request)
    {
        $validated = $request->validate([
            'optical_power_rx_dbm' => 'required|numeric',
            'installed_ont_serial' => 'nullable|string',
            'installed_cpe_serial' => 'nullable|string',
            'technician_notes' => 'nullable|string',
        ]);

        $job->update(array_merge($validated, [
            'status' => 'completed',
            'completed_at' => now(),
        ]));

        // Activate client if was pending
        if ($job->client) {
            $job->client->update(['status' => 'active']);
        }

        return back()->with('success', "Work Order {$job->job_number} completed. Optical power verified: {$job->optical_power_rx_dbm} dBm.");
    }
}
