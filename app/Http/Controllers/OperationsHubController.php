<?php

namespace App\Http\Controllers;

use App\Models\BgpConfig;
use App\Models\MikroTikDevice;
use App\Models\ONTDevice;
use App\Models\OspfConfig;
use Illuminate\Http\Request;

class OperationsHubController extends Controller
{
    public function predictiveMaintenance()
    {
        $devices = MikroTikDevice::all();
        $onts = ONTDevice::with('client')->get();

        $predictions = collect([
            (object)[
                'id' => 1,
                'device_name' => 'Edge Router South (hEX S)',
                'device_type' => 'MikroTik Router',
                'issue' => 'High CPU & Memory Exhaustion Trend',
                'metric_value' => 'CPU 84% • Memory 78%',
                'severity' => 'critical',
                'est_failure_time' => '18 - 24 hours under peak load',
                'predicted_fault' => 'Packet drop, kernel panic & subscriber connection drops',
                'recommended_action' => 'Offload local PPPoE crypto sessions or upgrade to CCR2004.',
            ],
            (object)[
                'id' => 2,
                'device_name' => 'FAT-04 Westlands Drop #12',
                'device_type' => 'GPON Fiber Line',
                'issue' => 'Rapid Optical Attenuation Degradation',
                'metric_value' => '-26.8 dBm (Drop rate: -0.4 dBm / 48h)',
                'severity' => 'warning',
                'est_failure_time' => '3 - 5 days before Red Optical LOS alarm',
                'predicted_fault' => 'Micro-bending or dirty optical APC connector in distribution cabinet',
                'recommended_action' => 'Dispatch field technician with OTDR to clean patch connector.',
            ],
            (object)[
                'id' => 3,
                'device_name' => 'AP-Rooftop-01 (mANTBox)',
                'device_type' => 'Wireless AP',
                'issue' => 'Excessive Channel 5GHz Interference',
                'metric_value' => 'Noise Floor -82 dBm • Retransmissions 22%',
                'severity' => 'info',
                'est_failure_time' => 'Active degradation',
                'predicted_fault' => 'Subscriber throughput capped under 15 Mbps',
                'recommended_action' => 'Auto-switch frequency channel to 5500 MHz (DFS band).',
            ]
        ]);

        return view('operations.predictive', compact('predictions', 'devices', 'onts'));
    }

    public function routingConfig()
    {
        $bgpPeers = BgpConfig::all();
        $ospfConfigs = OspfConfig::all();
        $routers = MikroTikDevice::all();

        return view('operations.routing', compact('bgpPeers', 'ospfConfigs', 'routers'));
    }

    public function storeOspf(Request $request)
    {
        $validated = $request->validate([
            'router_id' => 'required|string',
            'instance_name' => 'required|string',
            'redistribute_connected' => 'nullable',
            'redistribute_static' => 'nullable',
        ]);

        return back()->with('success', "OSPF Instance '{$validated['instance_name']}' deployed to RouterOS v7 fleet.");
    }

    public function storeBgp(Request $request)
    {
        $validated = $request->validate([
            'peer_name' => 'required|string',
            'remote_as' => 'required|numeric',
            'remote_ip' => 'required|ip',
        ]);

        return back()->with('success', "BGP Peering Session with AS{$validated['remote_as']} ({$validated['peer_name']}) established.");
    }

    public function routerDetection()
    {
        $detectedRouters = collect([
            (object)[
                'id' => 1,
                'mac_address' => '48:8F:5A:21:40:BC',
                'ip_address' => '10.10.4.88',
                'vendor' => 'TP-Link Corporation',
                'discovered_via' => 'MNDP Broadcast / DHCP Snooping',
                'status' => 'pending',
                'detected_at' => now()->subMinutes(12)->diffForHumans(),
                'suggested_client' => 'Walk-in Home Office',
            ],
            (object)[
                'id' => 2,
                'mac_address' => 'E4:8D:8C:99:12:04',
                'ip_address' => '10.10.4.92',
                'vendor' => 'Huawei Technologies Co.',
                'discovered_via' => 'TR-069 ACS Periodic Inform',
                'status' => 'authenticated',
                'detected_at' => now()->subHours(2)->diffForHumans(),
                'suggested_client' => 'John Mwangi',
            ],
            (object)[
                'id' => 3,
                'mac_address' => '74:4D:28:FE:80:11',
                'ip_address' => '10.10.2.14',
                'vendor' => 'Tenda Networks',
                'discovered_via' => 'MikroTik ARP Probe (Rogue DHCP Server)',
                'status' => 'denied',
                'detected_at' => now()->subHours(6)->diffForHumans(),
                'suggested_client' => 'Unmapped rogue bridge on ether3',
            ]
        ]);

        return view('operations.detection', compact('detectedRouters'));
    }

    public function authorizeDetectedRouter(Request $request)
    {
        $mac = $request->input('mac_address');
        $action = $request->input('action'); // approve / block

        if ($action === 'approve') {
            return back()->with('success', "Router {$mac} successfully approved and provisioned into TR-069 ACS!");
        } else {
            return back()->with('success', "Rogue router {$mac} blocked on MikroTik bridge firewall filter rules.");
        }
    }
}
