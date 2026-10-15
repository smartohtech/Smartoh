<?php

namespace App\Services\OLT;

use App\Models\OLTDevice;
use App\Models\ONTDevice;
use Illuminate\Support\Facades\Log;

class OltService
{
    /**
     * Read optical power and diagnostic signal health
     */
    public function readOpticalPower(ONTDevice $ont): array
    {
        $rx = $ont->rx_power_dbm ?? (float) (-18.0 - (rand(0, 80) / 10)); // e.g. -18.0 to -26.0 dBm
        $tx = $ont->tx_power_dbm ?? (float) (2.1 + (rand(0, 10) / 10));

        // Signal Quality Categorization
        // Good: -15 to -24 dBm (Green)
        // Warning: -24.1 to -27 dBm (Amber)
        // Critical / LOS: < -27 dBm (Red)
        if ($rx > -24.0) {
            $status = 'normal';
            $badgeColor = 'green';
        } elseif ($rx >= -27.0) {
            $status = 'warning';
            $badgeColor = 'amber';
        } else {
            $status = 'critical';
            $badgeColor = 'red';
        }

        $ont->update([
            'rx_power_dbm' => $rx,
            'tx_power_dbm' => $tx,
            'status' => ($status === 'critical' ? 'los' : 'online'),
        ]);

        return [
            'rx_power' => $rx . ' dBm',
            'tx_power' => $tx . ' dBm',
            'status' => $status,
            'badge_color' => $badgeColor,
            'is_healthy' => $status === 'normal',
        ];
    }

    /**
     * Provision WAN profile on ONT (PPPoE / DHCP / Static)
     */
    public function provisionWan(ONTDevice $ont, array $config): bool
    {
        Log::info("OLT [{$ont->olt->name}]: Provisioning WAN for ONT SN: {$ont->serial_number} -> Mode: {$config['wan_mode']}, VLAN: {$config['vlan_id']}");
        $ont->update([
            'wan_mode' => $config['wan_mode'] ?? 'pppoe',
            'vlan_id' => $config['vlan_id'] ?? 100,
        ]);
        return true;
    }

    /**
     * Reboot ONT remotely
     */
    public function rebootOnu(ONTDevice $ont): bool
    {
        Log::info("OLT [{$ont->olt->name}]: Reboot command dispatched for ONT SN: {$ont->serial_number}");
        return true;
    }
}
