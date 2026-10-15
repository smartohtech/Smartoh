<?php

namespace App\Services\UDIE;

use App\Models\Device;

class CapabilityEngine
{
    /**
     * Determine verified management capabilities for a device
     */
    public function detectCapabilities(Device $device, array $activeSignals = []): array
    {
        $capabilities = [
            'tr069' => 'unavailable',
            'tr181_usp' => 'unavailable',
            'mikrotik_api' => 'unavailable',
            'olt_api' => 'unavailable',
            'wireguard_vpn' => 'unavailable',
            'snmp' => 'unavailable',
            'lan_http' => 'available',
        ];

        // 1. TR-069 capability: verified if device has sent CWMP Inform or product_class matches ACS profiles
        if (!empty($activeSignals['tr069_verified']) || $device->evidences()->where('source', 'tr069')->exists()) {
            $capabilities['tr069'] = 'verified';
        } elseif ($device->deviceModel && in_array('tr069', $device->deviceModel->capabilities ?? [])) {
            $capabilities['tr069'] = 'supported_unverified';
        }

        // 2. TR-181/USP capability: verified if DeviceInfo parameters retrieved
        if (!empty($activeSignals['tr181_verified']) || $device->evidences()->where('source', 'tr181_usp')->exists()) {
            $capabilities['tr181_usp'] = 'verified';
        } elseif ($device->deviceModel && in_array('tr181_usp', $device->deviceModel->capabilities ?? [])) {
            $capabilities['tr181_usp'] = 'supported_unverified';
        }

        // 3. MikroTik RouterOS API capability: verified if RouterOS handshake succeeded
        if (strtolower($device->manufacturer ?? '') === 'mikrotik' || $device->evidences()->where('source', 'mikrotik_api')->exists()) {
            $capabilities['mikrotik_api'] = 'verified';
        }

        // 4. OLT API capability: available if associated with an OLT
        if ($device->olt_id && in_array($device->device_type, ['ont', 'onu'])) {
            $capabilities['olt_api'] = 'verified';
        }

        // 5. WireGuard / VPN capability: verified if active VPN tunnel or peer configured
        if ($device->vpn_peer_id || !empty($activeSignals['vpn_connected'])) {
            $capabilities['wireguard_vpn'] = 'connected';
        } elseif ($device->deviceModel && in_array('wireguard_vpn', $device->deviceModel->capabilities ?? [])) {
            $capabilities['wireguard_vpn'] = 'supported_unverified';
        }

        // 6. SNMP capability: verified if SNMP query responded
        if (!empty($activeSignals['snmp_verified']) || $device->evidences()->where('source', 'snmp')->exists()) {
            $capabilities['snmp'] = 'verified';
        }

        return $capabilities;
    }
}
