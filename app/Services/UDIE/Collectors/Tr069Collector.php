<?php

namespace App\Services\UDIE\Collectors;

use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DiscoveryCoordinator;

class Tr069Collector
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator
    ) {}

    /**
     * Process TR-069 Inform parameters into normalized evidence
     */
    public function handleInform(array $informData, int $tenantId): array
    {
        $serial = $informData['serial_number'] ?? null;
        $oui = $informData['oui'] ?? null;
        $productClass = $informData['product_class'] ?? null;
        $manufacturer = $informData['manufacturer'] ?? null;
        $softwareVersion = $informData['software_version'] ?? null;
        $hardwareVersion = $informData['hardware_version'] ?? null;
        $ip = $informData['ip_address'] ?? null;
        $mac = $informData['mac_address'] ?? null;

        $evidence = new DeviceEvidenceData($tenantId, 'tr069', [
            'serialNumber' => $serial,
            'manufacturer' => $manufacturer,
            'model' => $productClass,
            'productClass' => $productClass,
            'firmwareVersion' => $softwareVersion,
            'hardwareVersion' => $hardwareVersion,
            'ipAddress' => $ip,
            'macAddress' => $mac,
            'deviceType' => 'cpe',
            'confidenceContribution' => 38.0,
            'rawMetadata' => $informData,
        ]);

        $device = $this->coordinator->ingestEvidence($evidence);

        return [
            'status' => 'success',
            'device_id' => $device->id,
            'confidence' => $device->confidence_score,
            'status_label' => $device->identification_status,
        ];
    }
}
