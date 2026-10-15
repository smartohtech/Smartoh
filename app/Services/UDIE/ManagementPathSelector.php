<?php

namespace App\Services\UDIE;

use App\Models\Device;

class ManagementPathSelector
{
    /**
     * Choose the optimal primary and fallback management path based on verified capabilities and device type
     */
    public function selectPaths(Device $device, array $capabilities): array
    {
        $primary = 'lan_api';
        $fallback = null;
        $monitoring = 'lan_ping';
        $topologySource = 'lan_arp';

        $isOnt = in_array($device->device_type, ['ont', 'onu']);
        $isMikroTik = strtolower($device->manufacturer ?? '') === 'mikrotik';

        if ($isMikroTik && ($capabilities['mikrotik_api'] ?? '') === 'verified') {
            $primary = 'mikrotik_api';
            $fallback = ($capabilities['wireguard_vpn'] ?? '') === 'connected' ? 'wireguard_vpn' : 'snmp';
            $monitoring = 'mikrotik_api';
            $topologySource = 'mikrotik_api';
        } elseif ($isOnt) {
            if (($capabilities['tr069'] ?? '') === 'verified') {
                $primary = 'tr069';
                $fallback = ($capabilities['olt_api'] ?? '') === 'verified' ? 'olt_api' : 'wireguard_vpn';
            } elseif (($capabilities['tr181_usp'] ?? '') === 'verified') {
                $primary = 'tr181_usp';
                $fallback = 'olt_api';
            } elseif (($capabilities['olt_api'] ?? '') === 'verified') {
                $primary = 'olt_api';
                $fallback = 'tr069';
            }
            $monitoring = ($capabilities['olt_api'] ?? '') === 'verified' ? 'olt_omci' : 'tr069';
            $topologySource = 'olt_pon';
        } else {
            // Customer Router / Access Point / Switch
            if (($capabilities['tr181_usp'] ?? '') === 'verified') {
                $primary = 'tr181_usp';
                $fallback = 'tr069';
            } elseif (($capabilities['tr069'] ?? '') === 'verified') {
                $primary = 'tr069';
                $fallback = ($capabilities['wireguard_vpn'] ?? '') === 'connected' ? 'wireguard_vpn' : 'lan_api';
            } elseif (($capabilities['wireguard_vpn'] ?? '') === 'connected') {
                $primary = 'wireguard_vpn';
                $fallback = 'lan_api';
            } elseif (($capabilities['snmp'] ?? '') === 'verified') {
                $primary = 'snmp';
                $fallback = 'lan_api';
            }
            $monitoring = ($capabilities['snmp'] ?? '') === 'verified' ? 'snmp' : 'lan_ping';
            $topologySource = 'dhcp_arp';
        }

        return [
            'primary_management_protocol' => $primary,
            'fallback_management_protocol' => $fallback,
            'monitoring_protocol' => $monitoring,
            'topology_source' => $topologySource,
        ];
    }
}
