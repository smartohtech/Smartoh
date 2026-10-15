<?php

namespace App\Services\UDIE\Collectors;

use App\Models\MikroTikDevice;
use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DiscoveryCoordinator;

class MikroTikCollector
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator
    ) {}

    /**
     * Harvest evidence from a MikroTik RouterOS device
     */
    public function collectFromRouter(MikroTikDevice $router, array $systemInfo = []): array
    {
        $tenantId = $router->tenant_id;

        // 1. Ingest the MikroTik router itself
        $routerEvidence = new DeviceEvidenceData($tenantId, 'mikrotik_api', [
            'manufacturer' => 'MikroTik',
            'model' => $systemInfo['model'] ?? ($router->model ?: 'CCR2004-16G-2S+'),
            'serialNumber' => $systemInfo['serial'] ?? 'MK' . substr(md5($router->name . $router->ip_address), 0, 10),
            'firmwareVersion' => $systemInfo['version'] ?? ($router->routeros_version ?: 'v7.16'),
            'ipAddress' => $router->ip_address,
            'hostname' => $router->name,
            'deviceType' => 'router',
            'siteId' => $router->site_id,
            'confidenceContribution' => 45.0,
            'rawMetadata' => [
                'cpu_load' => $router->cpu_load,
                'api_port' => $router->api_port,
                'router_id' => $router->id,
            ],
        ]);

        $routerDevice = $this->coordinator->ingestEvidence($routerEvidence);

        // 2. Discover downstream devices via ARP / DHCP leases on the router
        $downstreamCount = 0;
        $leases = $systemInfo['leases'] ?? [];
        foreach ($leases as $lease) {
            $clientEvidence = new DeviceEvidenceData($tenantId, 'dhcp', [
                'macAddress' => $lease['mac-address'] ?? null,
                'ipAddress' => $lease['address'] ?? null,
                'hostname' => $lease['host-name'] ?? null,
                'siteId' => $router->site_id,
                'interface' => $lease['interface'] ?? null,
                'deviceType' => 'cpe',
                'confidenceContribution' => 15.0,
                'rawMetadata' => [
                    'upstream_router_id' => $routerDevice->id,
                    'lease_time' => $lease['expires-after'] ?? null,
                ],
            ]);

            $this->coordinator->ingestEvidence($clientEvidence);
            $downstreamCount++;
        }

        return [
            'router_device_id' => $routerDevice->id,
            'downstream_discovered' => $downstreamCount,
        ];
    }
}
