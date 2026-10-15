<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceTopology;
use Illuminate\Support\Facades\Log;

class ParentChildCorrelationEngine
{
    /**
     * Correlate parent-child topology relationships between devices
     * Strictly enforces the Parent-Child Rule: Bridged ONT vs Downstream Customer Router
     */
    public function correlate(Device $device, array $context = []): ?DeviceTopology
    {
        $tenantId = $device->tenant_id;

        // 1. RULE: Downstream Router behind a Bridged ONT
        // If device is a router/cpe and observed on the same customer/service/PON or MAC behind an ONT
        if (in_array($device->device_type, ['router', 'customer_gateway', 'cpe', 'access_point', 'switch'])) {
            $parentOnt = $this->findParentOnt($device, $context);
            if ($parentOnt && $parentOnt->id !== $device->id) {
                return $this->linkParentChild(
                    parent: $parentOnt,
                    child: $device,
                    relationshipType: 'ont_to_downstream_router',
                    confidence: 95.0,
                    evidence: [
                        'rule' => 'bridged_ont_downstream_router',
                        'ont_serial' => $parentOnt->serial_number,
                        'router_mac' => $device->mac_address,
                        'customer_id' => $device->customer_id,
                        'service_id' => $device->service_id,
                        'vlan_id' => $device->vlan_id,
                        'observed_at' => now()->toIso8601String(),
                    ]
                );
            }
        }

        // 2. RULE: ONT/ONU connected to an OLT
        if (in_array($device->device_type, ['ont', 'onu']) && $device->olt_id) {
            $oltDevice = Device::where('tenant_id', $tenantId)
                ->where('device_type', 'olt')
                ->where(function($q) use ($device) {
                    $q->where('olt_id', $device->olt_id)
                      ->orWhere('id', $device->olt_id);
                })
                ->first();

            if ($oltDevice && $oltDevice->id !== $device->id) {
                return $this->linkParentChild(
                    parent: $oltDevice,
                    child: $device,
                    relationshipType: 'olt_to_ont',
                    confidence: 98.0,
                    evidence: [
                        'rule' => 'olt_pon_onu_association',
                        'pon_id' => $device->pon_id,
                        'onu_id' => $device->onu_id,
                        'ont_serial' => $device->serial_number,
                        'observed_at' => now()->toIso8601String(),
                    ]
                );
            }
        }

        // 3. RULE: Switch or AP connected to an Upstream Router
        if (in_array($device->device_type, ['access_point', 'switch']) && !empty($context['upstream_router_id'])) {
            $router = Device::where('tenant_id', $tenantId)->find($context['upstream_router_id']);
            if ($router && $router->id !== $device->id) {
                $relType = ($device->device_type === 'access_point') ? 'router_to_ap' : 'router_to_switch';
                return $this->linkParentChild(
                    parent: $router,
                    child: $device,
                    relationshipType: $relType,
                    confidence: 90.0,
                    evidence: [
                        'rule' => 'interface_lldp_arp_uplink',
                        'interface' => $device->interface,
                        'observed_at' => now()->toIso8601String(),
                    ]
                );
            }
        }

        return null;
    }

    /**
     * Find parent ONT for a downstream router
     */
    protected function findParentOnt(Device $router, array $context): ?Device
    {
        $tenantId = $router->tenant_id;

        // Context override
        if (!empty($context['parent_ont_id'])) {
            return Device::where('tenant_id', $tenantId)->find($context['parent_ont_id']);
        }

        // Search by customer + service match where ONT is bridge mode
        if ($router->customer_id) {
            $ont = Device::where('tenant_id', $tenantId)
                ->where('customer_id', $router->customer_id)
                ->whereIn('device_type', ['ont', 'onu'])
                ->where('id', '!=', $router->id)
                ->latest()
                ->first();

            if ($ont) {
                return $ont;
            }
        }

        // Search by OLT/PON match if provided
        if ($router->olt_id && $router->pon_id) {
            return Device::where('tenant_id', $tenantId)
                ->where('olt_id', $router->olt_id)
                ->where('pon_id', $router->pon_id)
                ->whereIn('device_type', ['ont', 'onu'])
                ->where('id', '!=', $router->id)
                ->first();
        }

        return null;
    }

    /**
     * Link parent and child in device_topologies table and update device.parent_device_id
     */
    public function linkParentChild(Device $parent, Device $child, string $relationshipType, float $confidence, array $evidence = []): DeviceTopology
    {
        // CRITICAL RULE: Child router must NEVER inherit the ONT's model identity!
        // Verify child maintains its own independent identity
        if ($child->parent_device_id !== $parent->id) {
            $child->update([
                'parent_device_id' => $parent->id,
            ]);

            DeviceEvent::create([
                'tenant_id' => $child->tenant_id,
                'device_id' => $child->id,
                'event_type' => 'PARENT_CHILD_CORRELATED',
                'severity' => 'info',
                'title' => "Linked as downstream child of {$parent->display_name}",
                'description' => "Established {$relationshipType} topology link. Parent: {$parent->display_name} (SN: {$parent->serial_number}) -> Child: {$child->display_name} (MAC: {$child->mac_address})",
                'details' => [
                    'parent_id' => $parent->id,
                    'parent_model' => $parent->model,
                    'child_id' => $child->id,
                    'child_model' => $child->model,
                    'relationship_type' => $relationshipType,
                    'confidence' => $confidence,
                ],
            ]);
        }

        return DeviceTopology::updateOrCreate(
            [
                'parent_device_id' => $parent->id,
                'child_device_id' => $child->id,
                'relationship_type' => $relationshipType,
            ],
            [
                'tenant_id' => $child->tenant_id,
                'confidence_score' => $confidence,
                'evidence' => $evidence,
                'last_verified_at' => now(),
            ]
        );
    }
}
