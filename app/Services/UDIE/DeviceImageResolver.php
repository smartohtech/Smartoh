<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\TenantDeviceModel;

class DeviceImageResolver
{
    /**
     * Resolve image URL according to strict hierarchy:
     * 1. Tenant-specific exact-model image
     * 2. Global exact-model image
     * 3. Tenant manufacturer image
     * 4. Global manufacturer image
     * 5. Tenant device-type image
     * 6. Global device-type image
     * 7. Generic network-device icon
     */
    public function resolveImage(Device $device, string $size = 'full'): string
    {
        $tenantId = $device->tenant_id;
        $deviceModel = $device->deviceModel;
        $tenantModel = $device->tenantDeviceModel;

        // 1. Tenant-specific exact-model image
        if ($tenantModel) {
            $url = ($size === 'thumbnail') ? $tenantModel->custom_thumbnail_url : $tenantModel->custom_image_url;
            if ($url) return $url;
        }

        // 2. Global exact-model image
        if ($deviceModel) {
            $url = ($size === 'thumbnail') ? $deviceModel->thumbnail_url : $deviceModel->image_url;
            if ($url) return $url;
        }

        $manufacturer = strtolower(trim($device->manufacturer ?? ''));
        $deviceType = strtolower(trim($device->device_type ?? 'router'));

        // 3. Tenant manufacturer image
        $tenantMfg = TenantDeviceModel::where('tenant_id', $tenantId)
            ->whereHas('globalModel', fn($q) => $q->whereRaw('LOWER(manufacturer) = ?', [$manufacturer]))
            ->whereNotNull('custom_image_url')
            ->first();
        if ($tenantMfg && $tenantMfg->custom_image_url) {
            return $tenantMfg->custom_image_url;
        }

        // 4. Global manufacturer default image
        $globalMfg = DeviceModel::whereRaw('LOWER(manufacturer) = ?', [$manufacturer])
            ->whereNotNull('image_url')
            ->first();
        if ($globalMfg && $globalMfg->image_url) {
            return $globalMfg->image_url;
        }

        // 5. Tenant device-type image
        $tenantType = TenantDeviceModel::where('tenant_id', $tenantId)
            ->whereHas('globalModel', fn($q) => $q->where('device_type', $deviceType))
            ->whereNotNull('custom_image_url')
            ->first();
        if ($tenantType && $tenantType->custom_image_url) {
            return $tenantType->custom_image_url;
        }

        // 6. Global device-type image
        $globalType = DeviceModel::where('device_type', $deviceType)
            ->whereNotNull('image_url')
            ->first();
        if ($globalType && $globalType->image_url) {
            return $globalType->image_url;
        }

        // 7. Generic Network Device Icon
        return $this->getGenericIcon($deviceType);
    }

    /**
     * Get clean SVG data URI / fallback asset for generic device icons
     */
    public function getGenericIcon(string $deviceType): string
    {
        $type = strtolower($deviceType);

        switch ($type) {
            case 'ont':
            case 'onu':
                return '/images/devices/generic_ont.svg';
            case 'olt':
                return '/images/devices/generic_olt.svg';
            case 'switch':
                return '/images/devices/generic_switch.svg';
            case 'access_point':
                return '/images/devices/generic_ap.svg';
            case 'cpe':
            case 'wireless_cpe':
                return '/images/devices/generic_cpe.svg';
            case 'router':
            default:
                return '/images/devices/generic_router.svg';
        }
    }
}
