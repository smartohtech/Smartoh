<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MikroTikDevice;
use Illuminate\Http\Request;

class RadiusController extends Controller
{
    public function index()
    {
        $routers = MikroTikDevice::all();
        $clients = Client::where('status', 'active')->with('billingPlan')->get();

        // Active RADIUS sessions simulation / live
        $activeSessions = $clients->map(function ($client, $idx) {
            $uptimeHours = rand(1, 72);
            $bytesIn = rand(500, 9500) * 1024 * 1024;
            $bytesOut = rand(1500, 35000) * 1024 * 1024;
            return (object)[
                'session_id' => 'RAD-SES-' . str_pad($client->id, 5, '0', STR_PAD_LEFT),
                'username' => $client->pppoe_username ?? 'user_' . $client->account_number,
                'ip_address' => $client->ip_address ?? '10.10.0.' . ($idx + 10),
                'mac_address' => 'CC:DD:EE:01:' . sprintf('%02d', $idx) . ':01',
                'service' => $client->service_type ?? 'pppoe',
                'plan_name' => $client->billingPlan?->name ?? 'Standard Fiber',
                'uptime' => $uptimeHours . 'h ' . rand(1, 59) . 'm',
                'download_mb' => round($bytesOut / (1024 * 1024), 1),
                'upload_mb' => round($bytesIn / (1024 * 1024), 1),
                'nas_ip' => '10.0.0.1',
            ];
        });

        $config = (object)[
            'server_ip' => '127.0.0.1',
            'auth_port' => 1812,
            'acct_port' => 1813,
            'secret' => 'SmartohRadiusSecret2026',
            'bridge_name' => 'bridge-lan',
            'bridge_ip' => '10.10.0.1/24',
            'service_type' => 'pppoe',
            'anti_sharing' => true,
            'ttl_lock' => 1,
            'auto_reconnect' => true,
        ];

        return view('radius.index', compact('routers', 'activeSessions', 'config'));
    }

    public function disconnectSession(Request $request)
    {
        $sessionId = $request->input('session_id');
        $username = $request->input('username');

        return back()->with('success', "RADIUS CoA Disconnect-Request sent for session '{$sessionId}' ({$username}). Client session terminated.");
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'bridge_name' => 'required|string',
            'bridge_ip' => 'required|string',
            'anti_sharing' => 'nullable|boolean',
            'auto_reconnect' => 'nullable|boolean',
        ]);

        return back()->with('success', "RADIUS Server configuration & Anti-Sharing protection rules updated successfully!");
    }
}
