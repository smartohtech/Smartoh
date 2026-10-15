<?php

namespace App\Http\Controllers;

use App\Models\ClientRouter;
use App\Models\OLTDevice;
use App\Models\ONTDevice;
use App\Models\VPNTunnel;
use App\Services\OLT\OltService;
use App\Services\TR069\AcsService;
use App\Services\WireGuard\WireGuardService;
use Illuminate\Http\Request;

class UniversalCpeController extends Controller
{
    public function index()
    {
        $routers = ClientRouter::with('client')->latest()->get();
        $olts = OLTDevice::with('ontDevices.client')->get();
        $onts = ONTDevice::with(['olt', 'client'])->get();
        $vpnTunnels = VPNTunnel::latest()->get();

        $pathCounts = [
            'tr069' => ClientRouter::where('preferred_management_path', 'tr069')->count(),
            'tr181_usp' => ClientRouter::where('preferred_management_path', 'tr181_usp')->count(),
            'wireguard' => ClientRouter::where('preferred_management_path', 'wireguard')->count(),
            'olt_api' => ClientRouter::where('preferred_management_path', 'olt_api')->count(),
            'lan_direct' => ClientRouter::where('preferred_management_path', 'lan_direct')->count(),
            'manual' => ClientRouter::where('preferred_management_path', 'manual')->count(),
        ];

        return view('cpe.index', compact('routers', 'olts', 'onts', 'vpnTunnels', 'pathCounts'));
    }

    public function testOptical(ONTDevice $ont, OltService $oltService)
    {
        $res = $oltService->readOpticalPower($ont);
        return back()->with('success', "Optical RX Power for ONT {$ont->serial_number}: {$res['rx_power']} (Status: {$res['status']})");
    }

    public function createWireGuardTunnel(Request $request, WireGuardService $wireguard)
    {
        $validated = $request->validate([
            'tunnel_name' => 'required|string|max:100',
            'assigned_ip' => 'required|string',
        ]);

        $keys = $wireguard->generateKeyPair();

        $tunnel = VPNTunnel::create([
            'tenant_id' => app('current_tenant_id') ?? 1,
            'tunnel_name' => $validated['tunnel_name'],
            'tunnel_type' => 'wireguard',
            'assigned_ip' => $validated['assigned_ip'],
            'server_endpoint' => 'vpn.smartoh.net:51820',
            'public_key' => $keys['public_key'],
            'private_key_encrypted' => $keys['private_key'],
            'preshared_key_encrypted' => $keys['preshared_key'],
            'allowed_ips' => '0.0.0.0/0',
            'is_active' => true,
        ]);

        return back()->with('success', "WireGuard Tunnel '{$tunnel->tunnel_name}' provisioned! Endpoint assigned: {$tunnel->assigned_ip}");
    }

    public function handleCwmpInform(Request $request, AcsService $acs)
    {
        $xml = $request->getContent();
        $res = $acs->handleInform($xml, app()->bound('current_tenant_id') ? app('current_tenant_id') : 1);

        return response($res['soap_response'], 200, ['Content-Type' => 'text/xml']);
    }
}
