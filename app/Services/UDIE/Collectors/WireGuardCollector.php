<?php

namespace App\Services\UDIE\Collectors;

use App\Models\VPNTunnel;
use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DiscoveryCoordinator;

class WireGuardCollector
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator
    ) {}

    /**
     * Harvest devices connected via WireGuard VPN tunnels
     */
    public function collectFromTunnel(VPNTunnel $tunnel, array $peerMetadata = []): array
    {
        $tenantId = $tunnel->tenant_id;

        $evidence = new DeviceEvidenceData($tenantId, 'vpn', [
            'ipAddress' => $tunnel->assigned_ip,
            'hostname' => $tunnel->tunnel_name,
            'deviceType' => 'cpe',
            'vpnPeerId' => $tunnel->public_key,
            'confidenceContribution' => 25.0,
            'rawMetadata' => [
                'vpn_type' => $tunnel->tunnel_type,
                'endpoint' => $tunnel->server_endpoint,
                'is_active' => $tunnel->is_active,
                'client_id' => $tunnel->client_id,
            ],
        ]);

        $device = $this->coordinator->ingestEvidence($evidence);

        return [
            'device_id' => $device->id,
            'tunnel_id' => $tunnel->id,
        ];
    }
}
