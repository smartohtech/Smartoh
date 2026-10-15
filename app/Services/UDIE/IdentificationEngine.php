<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceEvidence;
use App\Models\DeviceModel;
use App\Models\TenantDeviceModel;
use App\Services\UDIE\Data\DeviceEvidenceData;

class IdentificationEngine
{
    public function __construct(
        protected ConfidenceEngine $confidenceEngine,
        protected FingerprintEngine $fingerprintEngine
    ) {}

    /**
     * Identify a device from incoming evidence against Global Catalog & Tenant Overrides
     */
    public function identify(Device $device, ?DeviceEvidenceData $newEvidence = null): array
    {
        $tenantId = $device->tenant_id;

        // Progressive candidate attributes
        $candidateManufacturer = $device->manufacturer;
        $candidateModel = $device->model;
        $candidateProductClass = $device->product_class;
        $candidateType = $device->device_type ?: 'unknown';

        if ($newEvidence) {
            if ($newEvidence->manufacturer) $candidateManufacturer = $newEvidence->manufacturer;
            if ($newEvidence->model) $candidateModel = $newEvidence->model;
            if ($newEvidence->productClass) $candidateProductClass = $newEvidence->productClass;
            if ($newEvidence->deviceType && $newEvidence->deviceType !== 'unknown') $candidateType = $newEvidence->deviceType;
        }

        // 1. OUI lookup fallback if manufacturer is missing
        if (!$candidateManufacturer && $device->mac_address) {
            $candidateManufacturer = $this->resolveOuiManufacturer($device->mac_address);
        }

        // 2. Search Global Model Catalog & Tenant Overrides
        $matchedGlobalModel = null;
        $matchedTenantModel = null;

        if ($candidateManufacturer && $candidateModel) {
            // Check Tenant Catalog first
            $matchedTenantModel = TenantDeviceModel::where('tenant_id', $tenantId)
                ->where(function($q) use ($candidateModel, $candidateManufacturer) {
                    $q->where('custom_name', 'LIKE', "%{$candidateModel}%")
                      ->orWhereHas('globalModel', function($gm) use ($candidateModel, $candidateManufacturer) {
                          $gm->whereRaw('LOWER(manufacturer) = ?', [strtolower($candidateManufacturer)])
                             ->whereRaw('LOWER(model) = ?', [strtolower($candidateModel)]);
                      });
                })
                ->first();

            // Check Global Catalog
            $matchedGlobalModel = DeviceModel::whereRaw('LOWER(manufacturer) = ?', [strtolower($candidateManufacturer)])
                ->where(function($q) use ($candidateModel, $candidateProductClass) {
                    $q->whereRaw('LOWER(model) = ?', [strtolower($candidateModel)])
                      ->orWhereRaw('LOWER(product_class) = ?', [strtolower($candidateProductClass ?: $candidateModel)]);
                })
                ->first();
        }

        // 3. Signature Matching against Global Catalog if model is still ambiguous
        if (!$matchedGlobalModel && ($device->mac_address || $candidateProductClass || $device->hostname)) {
            $matchedGlobalModel = $this->matchSignatures($device, $candidateManufacturer);
            if ($matchedGlobalModel) {
                $candidateManufacturer = $matchedGlobalModel->manufacturer;
                $candidateModel = $matchedGlobalModel->model;
                $candidateType = $matchedGlobalModel->device_type;
            }
        }

        // 4. Calculate Confidence and Detect Contradictions
        $eval = $this->confidenceEngine->calculate($device);
        $confidenceScore = $eval['confidence_score'];
        $identificationStatus = $eval['identification_status'];

        // 5. RULE 45: NO FALSE IDENTIFICATION
        // If confidence is below 50% or model was not matched with verified evidence,
        // do NOT invent an exact model!
        $effectiveModel = $candidateModel;
        if ($confidenceScore < 50.0 || empty($effectiveModel)) {
            $effectiveModel = 'Model not verified';
            if ($identificationStatus !== 'unknown') {
                $identificationStatus = 'partially_identified';
            }
        }

        // Update Device record
        $device->update([
            'manufacturer' => $candidateManufacturer ?: 'Unknown Manufacturer',
            'model' => $effectiveModel,
            'product_class' => $candidateProductClass,
            'device_type' => $candidateType ?: ($matchedGlobalModel?->device_type ?? 'unknown'),
            'device_model_id' => $matchedGlobalModel?->id,
            'tenant_device_model_id' => $matchedTenantModel?->id,
            'confidence_score' => $confidenceScore,
            'identification_status' => $identificationStatus,
            'last_identified_at' => now(),
        ]);

        return [
            'device' => $device,
            'matched_global_model' => $matchedGlobalModel,
            'matched_tenant_model' => $matchedTenantModel,
            'confidence' => $eval,
        ];
    }

    /**
     * Resolve OUI manufacturer from MAC address prefix
     */
    protected function resolveOuiManufacturer(string $mac): ?string
    {
        $clean = strtoupper(str_replace([':', '-', '.'], '', $mac));
        $prefix6 = substr($clean, 0, 6);

        $ouis = [
            '00259E' => 'Huawei',
            '485702' => 'Huawei',
            'F86E05' => 'Huawei',
            '001556' => 'Microsoft',
            '00180A' => 'Cisco',
            '0024B2' => 'Cisco',
            '488D36' => 'MikroTik',
            '6C3B6B' => 'MikroTik',
            'B869F4' => 'MikroTik',
            'D4CA6D' => 'MikroTik',
            '085531' => 'ZTE',
            '74B57E' => 'ZTE',
            '842615' => 'ZTE',
            '50D4F7' => 'TP-Link',
            '60A44C' => 'TP-Link',
            '74DA38' => 'TP-Link',
            'E848B8' => 'VSOL',
            '802AA8' => 'Ubiquiti',
            'F09FC2' => 'Ubiquiti',
        ];

        return $ouis[$prefix6] ?? null;
    }

    /**
     * Match signatures from Global Catalog
     */
    protected function matchSignatures(Device $device, ?string $manufacturer): ?DeviceModel
    {
        $models = DeviceModel::when($manufacturer, fn($q) => $q->whereRaw('LOWER(manufacturer) = ?', [strtolower($manufacturer)]))->get();

        foreach ($models as $m) {
            $sigs = $m->identification_signatures ?? [];

            // Check OUI signature
            if (!empty($sigs['ouis']) && $device->mac_address) {
                $macPrefix = strtoupper(substr(str_replace([':', '-', '.'], '', $device->mac_address), 0, 6));
                if (in_array($macPrefix, array_map('strtoupper', $sigs['ouis']))) {
                    return $m;
                }
            }

            // Check ProductClass signature
            if (!empty($sigs['product_classes']) && $device->product_class) {
                if (in_array(strtolower($device->product_class), array_map('strtolower', $sigs['product_classes']))) {
                    return $m;
                }
            }

            // Check Hostname patterns
            if (!empty($sigs['hostname_patterns']) && $device->hostname) {
                foreach ($sigs['hostname_patterns'] as $pattern) {
                    if (str_contains(strtolower($device->hostname), strtolower($pattern))) {
                        return $m;
                    }
                }
            }
        }

        return null;
    }
}
