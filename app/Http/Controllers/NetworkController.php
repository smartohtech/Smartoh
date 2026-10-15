<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BgpConfig;
use App\Models\MikroTikDevice;
use App\Models\OspfConfig;
use App\Models\Site;
use App\Services\MikroTik\MikroTikService;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function mikrotik(MikroTikService $mikrotik)
    {
        $routers = MikroTikDevice::with('site')->get();
        $sites = Site::all();

        // Sample interfaces for active router view
        $activeRouter = $routers->first();
        $interfaces = $activeRouter ? $mikrotik->getInterfaces($activeRouter) : [];

        $bgpPeers = BgpConfig::with('mikrotikDevice')->get();
        $ospfAreas = OspfConfig::with('mikrotikDevice')->get();

        return view('network.mikrotik', compact('routers', 'sites', 'activeRouter', 'interfaces', 'bgpPeers', 'ospfAreas'));
    }

    public function storeRouter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string',
            'api_port' => 'required|integer',
            'api_username' => 'required|string',
            'api_password' => 'required|string',
            'site_id' => 'nullable|exists:sites,id',
            'model' => 'nullable|string',
            'routeros_version' => 'nullable|string',
        ]);

        $device = MikroTikDevice::create([
            'name' => $validated['name'],
            'ip_address' => $validated['ip_address'],
            'api_port' => $validated['api_port'],
            'api_username' => $validated['api_username'],
            'api_password_encrypted' => encrypt($validated['api_password']),
            'site_id' => $validated['site_id'],
            'model' => $validated['model'] ?: 'CCR2004-16G-2S+',
            'routeros_version' => $validated['routeros_version'] ?: 'v7.16',
            'is_online' => true,
            'cpu_load' => 12,
            'uptime_seconds' => 452100,
        ]);

        return back()->with('success', "MikroTik Router '{$device->name}' enrolled successfully!");
    }

    public function testConnection(MikroTikDevice $device, MikroTikService $mikrotik)
    {
        $res = $mikrotik->testConnection($device);
        return back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    public function rebootRouter(MikroTikDevice $device, MikroTikService $mikrotik)
    {
        $mikrotik->reboot($device);

        AuditLog::create([
            'tenant_id' => $device->tenant_id,
            'user_id' => auth()->id(),
            'event' => 'router.rebooted',
            'auditable_type' => MikroTikDevice::class,
            'auditable_id' => $device->id,
            'new_values' => ['name' => $device->name, 'ip' => $device->ip_address],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Reboot command dispatched to router '{$device->name}'.");
    }
}
