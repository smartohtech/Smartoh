<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceEvidence;
use App\Models\DiscoveryJob;
use App\Services\UDIE\Data\DeviceEvidenceData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscoveryCoordinator
{
    public function __construct(
        protected FingerprintEngine $fingerprintEngine,
        protected IdentificationEngine $identificationEngine,
        protected ConfidenceEngine $confidenceEngine,
        protected ParentChildCorrelationEngine $parentChildEngine,
        protected CapabilityEngine $capabilityEngine,
        protected ManagementPathSelector $pathSelector,
        protected AnomalyDetector $anomalyDetector,
        protected DeviceImageResolver $imageResolver
    ) {}

    public function processEvidence(DeviceEvidenceData $evidence): Device
    {
        return $this->ingestEvidence($evidence);
    }

    /**
     * Ingest normalized evidence and execute full UDIE workflow:
     * DISCOVER -> SCOPE -> FINGERPRINT -> IDENTIFY -> CORRELATE -> VERIFY ->
     * APPLY PARENT-CHILD RULE -> DETERMINE CAPABILITIES -> BUILD TOPOLOGY -> ENROL/MANAGE -> MONITOR -> ALERT
     */
    public function ingestEvidence(DeviceEvidenceData $evidence): Device
    {
        return DB::transaction(function() use ($evidence) {
            $tenantId = $evidence->tenantId;

            // 1. SCOPE AWARENESS:
            // Locate existing device by strong identifiers in the correct scope.
            // Never match by IP alone across different VRFs or VLANs!
            $device = $this->findExistingDevice($evidence);

            // 2. ANOMALY DETECTION (Replacement / Topology Movement)
            $anomalies = $this->anomalyDetector->detectAnomalies($evidence, $device);

            // 3. CREATE OR UPDATE DEVICE
            $isNew = false;
            if (!$device) {
                $isNew = true;
                $device = Device::create([
                    'tenant_id' => $tenantId,
                    'site_id' => $evidence->siteId,
                    'customer_id' => $evidence->customerId,
                    'service_id' => $evidence->serviceId,
                    'device_type' => $evidence->deviceType ?: 'unknown',
                    'manufacturer' => $evidence->manufacturer,
                    'model' => $evidence->model,
                    'product_class' => $evidence->productClass,
                    'serial_number' => $evidence->serialNumber,
                    'mac_address' => $evidence->macAddress,
                    'ip_address' => $evidence->ipAddress,
                    'hostname' => $evidence->hostname,
                    'firmware_version' => $evidence->firmwareVersion,
                    'hardware_version' => $evidence->hardwareVersion,
                    'vrf_id' => $evidence->vrfId,
                    'network_segment_id' => $evidence->networkSegmentId,
                    'vlan_id' => $evidence->vlanId,
                    'interface' => $evidence->interface,
                    'olt_id' => $evidence->oltId,
                    'pon_id' => $evidence->ponId,
                    'onu_id' => $evidence->onuId,
                    'pppoe_session_id' => $evidence->pppoeUsername,
                    'vpn_peer_id' => $evidence->vpnPeerId,
                    'status' => 'online',
                    'identification_status' => 'identifying',
                    'first_seen' => now(),
                    'last_seen' => now(),
                    'raw_metadata' => $evidence->rawMetadata,
                ]);

                DeviceEvent::create([
                    'tenant_id' => $tenantId,
                    'device_id' => $device->id,
                    'event_type' => 'DEVICE_DISCOVERED',
                    'severity' => 'info',
                    'title' => "Discovered via {$evidence->source}",
                    'description' => "New device discovered at IP {$evidence->ipAddress} (MAC: {$evidence->macAddress}) on {$evidence->source}",
                    'details' => $evidence->rawMetadata,
                ]);
            } else {
                // Update basic telemetry & scope
                $device->update([
                    'ip_address' => $evidence->ipAddress ?: $device->ip_address,
                    'vrf_id' => $evidence->vrfId ?: $device->vrf_id,
                    'network_segment_id' => $evidence->networkSegmentId ?: $device->network_segment_id,
                    'vlan_id' => $evidence->vlanId ?: $device->vlan_id,
                    'customer_id' => $evidence->customerId ?: $device->customer_id,
                    'service_id' => $evidence->serviceId ?: $device->service_id,
                    'firmware_version' => $evidence->firmwareVersion ?: $device->firmware_version,
                    'hardware_version' => $evidence->hardwareVersion ?: $device->hardware_version,
                    'status' => 'online',
                    'last_seen' => now(),
                ]);
            }

            // Record Anomaly Events if detected
            if (!empty($anomalies)) {
                $this->anomalyDetector->recordEvents($tenantId, $device->id, $anomalies);
            }

            // 4. RECORD EVIDENCE GRAPH ENTRIES
            $this->storeEvidenceGraph($device, $evidence);

            // 5. FINGERPRINT & HASH
            $fp = $this->fingerprintEngine->generateFingerprint($device);
            $device->update(['fingerprint_hash' => $fp['hash']]);
            $this->fingerprintEngine->cacheFingerprint($tenantId, $fp['hash'], $device->id);

            // 6. IDENTIFICATION ENGINE
            $this->identificationEngine->identify($device, $evidence);

            // 7. PARENT-CHILD CORRELATION (Bridged ONT vs Downstream Router)
            $this->parentChildEngine->correlate($device, [
                'parent_ont_id' => $evidence->rawMetadata['parent_ont_id'] ?? $evidence->upstreamDeviceId ?? null,
                'upstream_router_id' => $evidence->rawMetadata['upstream_router_id'] ?? $evidence->upstreamDeviceId ?? null,
            ]);

            // 8. CAPABILITY ENGINE & MANAGEMENT PATH SELECTION
            $activeSignals = [
                'tr069_verified' => $evidence->source === 'tr069',
                'tr181_verified' => $evidence->source === 'tr181_usp',
                'mikrotik_verified' => $evidence->source === 'mikrotik_api',
                'vpn_connected' => !empty($evidence->vpnPeerId) || $evidence->source === 'vpn',
                'snmp_verified' => $evidence->source === 'snmp',
            ];
            $capabilities = $this->capabilityEngine->detectCapabilities($device, $activeSignals);
            $paths = $this->pathSelector->selectPaths($device, $capabilities);

            $device->update([
                'capabilities' => $capabilities,
                'primary_management_protocol' => $paths['primary_management_protocol'],
                'fallback_management_protocol' => $paths['fallback_management_protocol'],
                'management_status' => 'managed',
                'last_management_check' => now(),
            ]);

            return $device->fresh([
                'deviceModel',
                'tenantDeviceModel',
                'parentDevice',
                'childDevices',
                'evidences',
                'topologiesAsParent',
                'topologiesAsChild',
                'vrf',
                'networkSegment',
            ]);
        });
    }

    /**
     * Find existing device in correct network scope
     */
    protected function findExistingDevice(DeviceEvidenceData $evidence): ?Device
    {
        $tenantId = $evidence->tenantId;

        // 1. By Serial Number (Global anchor within tenant)
        if ($evidence->serialNumber) {
            $bySerial = Device::where('tenant_id', $tenantId)
                ->where('serial_number', $evidence->serialNumber)
                ->first();
            if ($bySerial) return $bySerial;
        }

        // 2. By MAC Address (Within tenant)
        if ($evidence->macAddress) {
            $cleanMac = strtolower($evidence->macAddress);
            $byMac = Device::where('tenant_id', $tenantId)
                ->whereRaw('LOWER(mac_address) = ?', [$cleanMac])
                ->first();
            if ($byMac) return $byMac;
        }

        // 3. By OLT + PON + ONU slot
        if ($evidence->oltId && $evidence->ponId && $evidence->onuId) {
            $byOnu = Device::where('tenant_id', $tenantId)
                ->where('olt_id', $evidence->oltId)
                ->where('pon_id', $evidence->ponId)
                ->where('onu_id', $evidence->onuId)
                ->first();
            if ($byOnu) return $byOnu;
        }

        // 4. By Scoped IP Address (IP + VRF + VLAN)
        // CRITICAL: NEVER match IP alone without matching VRF / scope!
        if ($evidence->ipAddress && $evidence->vrfId) {
            $byScopedIp = Device::where('tenant_id', $tenantId)
                ->where('ip_address', $evidence->ipAddress)
                ->where('vrf_id', $evidence->vrfId)
                ->when($evidence->vlanId, fn($q) => $q->where('vlan_id', $evidence->vlanId))
                ->first();
            if ($byScopedIp) return $byScopedIp;
        }

        return null;
    }

    /**
     * Store evidence graph records for audit and explainability
     */
    protected function storeEvidenceGraph(Device $device, DeviceEvidenceData $evidence): void
    {
        $attributes = [
            'manufacturer' => $evidence->manufacturer,
            'model' => $evidence->model,
            'product_class' => $evidence->productClass,
            'serial_number' => $evidence->serialNumber,
            'mac_address' => $evidence->macAddress,
            'ip_address' => $evidence->ipAddress,
            'hostname' => $evidence->hostname,
            'device_type' => $evidence->deviceType,
            'firmware_version' => $evidence->firmwareVersion,
        ];

        foreach ($attributes as $attr => $val) {
            if ($val !== null && $val !== '') {
                // Check if this attribute conflicts with existing verified attribute
                $existingVal = $device->{$attr} ?? null;
                $isContradictory = false;
                if ($existingVal && strtolower(trim((string)$existingVal)) !== strtolower(trim((string)$val))) {
                    $isContradictory = true;
                }

                DeviceEvidence::create([
                    'tenant_id' => $device->tenant_id,
                    'device_id' => $device->id,
                    'source' => $evidence->source,
                    'attribute_name' => $attr,
                    'attribute_value' => (string) $val,
                    'evidence_scope' => $evidence->evidenceScope,
                    'is_contradictory' => $isContradictory,
                    'confidence_weight' => $evidence->confidenceContribution,
                    'observed_at' => now(),
                    'raw_payload' => $evidence->rawMetadata,
                ]);
            }
        }
    }
}
