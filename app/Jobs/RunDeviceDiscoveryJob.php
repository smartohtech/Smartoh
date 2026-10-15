<?php

namespace App\Jobs;

use App\Models\DiscoveryJob;
use App\Models\MikroTikDevice;
use App\Models\OLTDevice;
use App\Services\UDIE\Collectors\MikroTikCollector;
use App\Services\UDIE\Collectors\OltCollector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunDeviceDiscoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $discoveryJobId
    ) {}

    public function handle(MikroTikCollector $mikrotikCollector, OltCollector $oltCollector): void
    {
        $job = DiscoveryJob::find($this->discoveryJobId);
        if (!$job) return;

        $job->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $discoveredCount = 0;
            $identifiedCount = 0;
            $tenantId = $job->tenant_id;

            // 1. Discover via MikroTik Routers
            $routers = MikroTikDevice::where('tenant_id', $tenantId)->get();
            foreach ($routers as $r) {
                // Mock active leases for simulation if not live
                $leases = [
                    ['address' => '192.168.88.245', 'mac-address' => '48:8D:36:1A:2B:3C', 'host-name' => 'RB4011-Edge', 'interface' => 'ether2'],
                    ['address' => '192.168.88.246', 'mac-address' => '50:D4:F7:99:88:77', 'host-name' => 'Archer_C6', 'interface' => 'ether3'],
                ];
                $res = $mikrotikCollector->collectFromRouter($r, [
                    'model' => $r->model ?: 'CCR2004-16G-2S+',
                    'version' => $r->routeros_version ?: 'v7.16',
                    'leases' => $leases,
                ]);
                $discoveredCount += (1 + ($res['downstream_discovered'] ?? 0));
                $identifiedCount += (1 + ($res['downstream_discovered'] ?? 0));
            }

            // 2. Discover via OLTs
            $olts = OLTDevice::where('tenant_id', $tenantId)->get();
            foreach ($olts as $olt) {
                $onus = [
                    [
                        'serial_number' => '48575443' . rand(10000000, 99999999),
                        'mac_address' => '00:25:9E:' . strtoupper(substr(md5(rand()), 0, 2)) . ':' . strtoupper(substr(md5(rand()), 0, 2)) . ':' . strtoupper(substr(md5(rand()), 0, 2)),
                        'manufacturer' => 'Huawei',
                        'model' => 'HG8145H',
                        'pon_port' => '1/1/1',
                        'onu_slot' => 1,
                        'wan_mode' => 'bridge',
                        'vlan_id' => 100,
                        'downstream_router' => [
                            'mac_address' => '48:8D:36:' . strtoupper(substr(md5(rand()), 0, 2)) . ':' . strtoupper(substr(md5(rand()), 0, 2)) . ':' . strtoupper(substr(md5(rand()), 0, 2)),
                            'ip_address' => '192.168.10.45',
                            'manufacturer' => 'MikroTik',
                            'model' => 'RB4011iGS+5HacQ2HnD-IN',
                            'hostname' => 'Customer-Downstream-RB4011',
                        ],
                    ],
                ];
                $res = $oltCollector->syncFromOlt($olt, $onus);
                $discoveredCount += (1 + ($res['onts_synced'] ?? 0) * 2);
                $identifiedCount += (1 + ($res['onts_synced'] ?? 0) * 2);
            }

            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
                'devices_discovered_count' => $discoveredCount,
                'devices_identified_count' => $identifiedCount,
            ]);

            Log::info("UDIE Discovery Job #{$job->id} finished: {$discoveredCount} discovered.");
        } catch (\Throwable $e) {
            $job->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            Log::error("UDIE Discovery Job #{$job->id} failed: " . $e->getMessage());
        }
    }
}
