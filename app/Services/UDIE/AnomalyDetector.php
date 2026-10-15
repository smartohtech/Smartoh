<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Services\UDIE\Data\DeviceEvidenceData;

class AnomalyDetector
{
    public function checkAnomalies(DeviceEvidenceData|Device $a, DeviceEvidenceData|Device $b): array
    {
        $evidence = ($a instanceof DeviceEvidenceData) ? $a : $b;
        $device = ($a instanceof Device) ? $a : $b;
        return $this->detectAnomalies($evidence, $device);
    }

    /**
     * Inspect incoming evidence against existing device history for replacements or topology changes
     */
    public function detectAnomalies(DeviceEvidenceData $evidence, ?Device $existingDevice = null): array
    {
        $anomalies = [];
        $tenantId = $evidence->tenantId;

        // 1. DEVICE REPLACEMENT DETECTION:
        if ($existingDevice) {
            $isSerialChanged = $evidence->serialNumber && $existingDevice->serial_number && ($evidence->serialNumber !== $existingDevice->serial_number);
            $isMacChanged = $evidence->macAddress && $existingDevice->mac_address && (strtolower($evidence->macAddress) !== strtolower($existingDevice->mac_address));

            if ($isSerialChanged || $isMacChanged) {
                $anomalies[] = [
                    'type' => 'DEVICE_REPLACEMENT_DETECTED',
                    'severity' => 'warning',
                    'title' => "Customer Device Replacement Detected",
                    'description' => "Previous device {$existingDevice->display_name} (SN: {$existingDevice->serial_number}) replaced by new hardware (SN: {$evidence->serialNumber}, MAC: {$evidence->macAddress}).",
                    'details' => [
                        'previous_device_id' => $existingDevice->id,
                        'previous_serial' => $existingDevice->serial_number,
                        'previous_mac' => $existingDevice->mac_address,
                        'new_serial' => $evidence->serialNumber,
                        'new_mac' => $evidence->macAddress,
                        'detected_at' => now()->toIso8601String(),
                    ],
                ];
            }
        } elseif ($evidence->customerId && ($evidence->serialNumber || $evidence->macAddress)) {
            $previousDevice = Device::where('tenant_id', $tenantId)
                ->where('customer_id', $evidence->customerId)
                ->when($evidence->deviceType, fn($q) => $q->where('device_type', $evidence->deviceType))
                ->when($existingDevice, fn($q) => $q->where('id', '!=', $existingDevice->id))
                ->latest()
                ->first();

            if ($previousDevice) {
                $isSerialChanged = $evidence->serialNumber && $previousDevice->serial_number && ($evidence->serialNumber !== $previousDevice->serial_number);
                $isMacChanged = $evidence->macAddress && $previousDevice->mac_address && (strtolower($evidence->macAddress) !== strtolower($previousDevice->mac_address));

                if ($isSerialChanged || $isMacChanged) {
                    $anomalies[] = [
                        'type' => 'DEVICE_REPLACEMENT_DETECTED',
                        'severity' => 'warning',
                        'title' => "Customer Device Replacement Detected",
                        'description' => "Previous device {$previousDevice->display_name} (SN: {$previousDevice->serial_number}) replaced by new device (SN: {$evidence->serialNumber}, MAC: {$evidence->macAddress}) on customer account.",
                        'details' => [
                            'previous_device_id' => $previousDevice->id,
                            'previous_serial' => $previousDevice->serial_number,
                            'previous_mac' => $previousDevice->mac_address,
                            'new_serial' => $evidence->serialNumber,
                            'new_mac' => $evidence->macAddress,
                            'customer_id' => $evidence->customerId,
                            'site_id' => $evidence->siteId,
                            'olt_id' => $evidence->oltId,
                            'pon_id' => $evidence->ponId,
                            'detected_at' => now()->toIso8601String(),
                        ],
                    ];
                }
            }
        }

        // 2. DEVICE TOPOLOGY MOVEMENT DETECTION:
        // Existing device moved to a different OLT, PON port, or VLAN
        if ($existingDevice) {
            $isOltMoved = $evidence->oltId && $existingDevice->olt_id && ($evidence->oltId !== $existingDevice->olt_id);
            $isPonMoved = $evidence->ponId && $existingDevice->pon_id && ($evidence->ponId !== $existingDevice->pon_id);
            $isVlanMoved = $evidence->vlanId && $existingDevice->vlan_id && ($evidence->vlanId !== $existingDevice->vlan_id);

            if ($isOltMoved || $isPonMoved || $isVlanMoved) {
                $anomalies[] = [
                    'type' => 'DEVICE_TOPOLOGY_CHANGED',
                    'severity' => 'warning',
                    'title' => "Device Topology Location Changed",
                    'description' => "Device {$existingDevice->display_name} (SN: {$existingDevice->serial_number}) moved from OLT #{$existingDevice->olt_id} (PON {$existingDevice->pon_id}) to OLT #{$evidence->oltId} (PON {$evidence->ponId}).",
                    'details' => [
                        'device_id' => $existingDevice->id,
                        'previous_olt_id' => $existingDevice->olt_id,
                        'previous_pon_id' => $existingDevice->pon_id,
                        'previous_vlan_id' => $existingDevice->vlan_id,
                        'new_olt_id' => $evidence->oltId,
                        'new_pon_id' => $evidence->ponId,
                        'new_vlan_id' => $evidence->vlanId,
                        'detected_at' => now()->toIso8601String(),
                    ],
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Record detected anomalies as DeviceEvents
     */
    public function recordEvents(int $tenantId, ?int $deviceId, array $anomalies): void
    {
        foreach ($anomalies as $anomaly) {
            DeviceEvent::create([
                'tenant_id' => $tenantId,
                'device_id' => $deviceId,
                'event_type' => $anomaly['type'],
                'severity' => $anomaly['severity'],
                'title' => $anomaly['title'],
                'description' => $anomaly['description'],
                'details' => $anomaly['details'],
                'is_resolved' => false,
            ]);
        }
    }
}
