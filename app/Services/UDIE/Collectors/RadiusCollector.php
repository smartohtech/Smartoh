<?php

namespace App\Services\UDIE\Collectors;

use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DiscoveryCoordinator;

class RadiusCollector
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator
    ) {}

    /**
     * Ingest active RADIUS Accounting / PPPoE session
     */
    public function handleSession(array $sessionData, int $tenantId): array
    {
        $evidence = new DeviceEvidenceData($tenantId, 'radius', [
            'ipAddress' => $sessionData['framed_ip_address'] ?? null,
            'macAddress' => $sessionData['calling_station_id'] ?? null,
            'pppoeUsername' => $sessionData['username'] ?? null,
            'interface' => $sessionData['nas_port_id'] ?? null,
            'deviceType' => 'customer_gateway',
            'customerId' => $sessionData['client_id'] ?? null,
            'confidenceContribution' => 22.0,
            'rawMetadata' => [
                'nas_ip' => $sessionData['nas_ip_address'] ?? null,
                'session_time' => $sessionData['acct_session_time'] ?? null,
                'session_id' => $sessionData['acct_session_id'] ?? null,
            ],
        ]);

        $device = $this->coordinator->ingestEvidence($evidence);

        return [
            'device_id' => $device->id,
            'status' => 'synced',
        ];
    }
}
