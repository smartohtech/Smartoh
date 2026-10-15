<?php

namespace App\Services\UDIE\Collectors;

use App\Models\OLTDevice;
use App\Models\ONTDevice;
use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DiscoveryCoordinator;

class OltCollector
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator
    ) {}

    /**
     * Harvest and ingest ONT/ONU devices from an OLT
     */
    public function syncFromOlt(OLTDevice $olt, array $onus = []): array
    {
        $tenantId = $olt->tenant_id;

        // 1. Ingest OLT Chassis itself as an infrastructure Device
        $oltEvidence = new DeviceEvidenceData($tenantId, 'olt', [
            'manufacturer' => $olt->hardware_vendor ?: 'Huawei',
            'model' => $olt->hardware_model ?: 'SmartAX MA5800-X7',
            'ipAddress' => $olt->ip_address,
            'hostname' => $olt->name,
            'deviceType' => 'olt',
            'siteId' => $olt->site_id,
            'confidenceContribution' => 45.0,
            'rawMetadata' => [
                'total_pons' => $olt->total_pon_ports,
                'software_version' => $olt->firmware_version,
            ],
        ]);
        $oltDevice = $this->coordinator->ingestEvidence($oltEvidence);

        // 2. Ingest connected ONTs/ONUs
        $processedOnts = 0;
        foreach ($onus as $item) {
            $isBridged = ($item['wan_mode'] ?? 'bridge') === 'bridge';
            $ontEvidence = new DeviceEvidenceData($tenantId, 'olt', [
                'serialNumber' => $item['serial_number'] ?? null,
                'macAddress' => $item['mac_address'] ?? null,
                'manufacturer' => $item['manufacturer'] ?? ($item['vendor'] ?? 'Huawei'),
                'model' => $item['model'] ?? 'HG8145H',
                'deviceType' => 'ont',
                'oltId' => $olt->id,
                'ponId' => $item['pon_port'] ?? '1/1/1',
                'onuId' => (string) ($item['onu_slot'] ?? '1'),
                'customerId' => $item['client_id'] ?? null,
                'vlanId' => $item['vlan_id'] ?? 100,
                'confidenceContribution' => 40.0,
                'rawMetadata' => [
                    'wan_mode' => $isBridged ? 'bridge' : 'route',
                    'rx_power' => $item['rx_power_dbm'] ?? -19.5,
                    'tx_power' => $item['tx_power_dbm'] ?? 2.1,
                    'parent_olt_device_id' => $oltDevice->id,
                ],
            ]);

            $ontDevice = $this->coordinator->ingestEvidence($ontEvidence);
            $processedOnts++;

            // 3. PARENT-CHILD RULE: If this is a bridged ONT and downstream router details are observed
            // (e.g. customer has a downstream MikroTik / TP-Link router running PPPoE or DHCP)
            if ($isBridged && !empty($item['downstream_router'])) {
                $ds = $item['downstream_router'];
                $childEvidence = new DeviceEvidenceData($tenantId, $ds['source'] ?? 'dhcp', [
                    'macAddress' => $ds['mac_address'] ?? null,
                    'ipAddress' => $ds['ip_address'] ?? null,
                    'manufacturer' => $ds['manufacturer'] ?? 'MikroTik',
                    'model' => $ds['model'] ?? 'RB4011iGS+5HacQ2HnD-IN',
                    'serialNumber' => $ds['serial_number'] ?? null,
                    'hostname' => $ds['hostname'] ?? 'Customer-Core-Router',
                    'deviceType' => 'customer_gateway',
                    'customerId' => $item['client_id'] ?? null,
                    'vlanId' => $item['vlan_id'] ?? 100,
                    'confidenceContribution' => 35.0,
                    'rawMetadata' => [
                        'parent_ont_id' => $ontDevice->id,
                        'downstream_ethernet_port' => 'eth1',
                    ],
                ]);

                // Ingest downstream router as an independent device instance!
                $this->coordinator->ingestEvidence($childEvidence);
            }
        }

        return [
            'olt_device_id' => $oltDevice->id,
            'onts_synced' => $processedOnts,
        ];
    }
}
