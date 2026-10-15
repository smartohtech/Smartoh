<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Services\UDIE\Data\DeviceEvidenceData;
use Illuminate\Support\Facades\Cache;

class FingerprintEngine
{
    /**
     * Compute a deterministic fingerprint and hash for a device observation or instance
     */
    public function generateFingerprint(DeviceEvidenceData|Device $subject): array
    {
        $tenantId = $subject->tenant_id ?? ($subject->tenantId ?? 1);
        $mac = strtolower(str_replace([':', '-', '.'], '', $subject->mac_address ?? ($subject->macAddress ?? '')));
        $serial = strtoupper(trim($subject->serial_number ?? ($subject->serialNumber ?? '')));
        $hostname = strtolower(trim($subject->hostname ?? ''));
        $vrfId = $subject->vrf_id ?? ($subject->vrfId ?? 0);
        $vlanId = $subject->vlan_id ?? ($subject->vlanId ?? 0);
        $ip = trim($subject->ip_address ?? ($subject->ipAddress ?? ''));
        $oltId = $subject->olt_id ?? ($subject->oltId ?? 0);
        $ponId = trim($subject->pon_id ?? ($subject->ponId ?? ''));
        $onuId = trim($subject->onu_id ?? ($subject->onuId ?? ''));
        $manufacturer = strtolower(trim($subject->manufacturer ?? ''));
        $model = strtolower(trim($subject->model ?? ''));

        // Fingerprint components array
        $components = [
            'tenant' => $tenantId,
            'mac' => $mac,
            'serial' => $serial,
            'hostname' => $hostname,
            'scope' => "vrf:{$vrfId}|vlan:{$vlanId}",
            'scoped_ip' => $ip ? "{$ip}@vrf:{$vrfId}" : '',
            'pon_anchor' => ($oltId && $ponId) ? "olt:{$oltId}|pon:{$ponId}|onu:{$onuId}" : '',
            'vendor_anchor' => "{$manufacturer}:{$model}",
        ];

        // Filter empty components
        $normalizedString = implode('||', array_filter($components));
        $hash = hash('sha256', $normalizedString);

        return [
            'raw_string' => $normalizedString,
            'hash' => $hash,
            'components' => $components,
        ];
    }

    /**
     * Generate raw hash string
     */
    public function generate(DeviceEvidenceData|Device $subject): string
    {
        return $this->generateFingerprint($subject)['hash'];
    }

    /**
     * Check if a fingerprint is cached and whether it has changed
     */
    public function isCached(int $tenantId, string $fingerprintHash): bool
    {
        $key = "udie:fp:{$tenantId}:{$fingerprintHash}";
        return Cache::has($key);
    }

    /**
     * Store fingerprint in cache for fast re-discovery bypass
     */
    public function cacheFingerprint(int $tenantId, string $fingerprintHash, int $deviceId, int $ttlSeconds = 86400): void
    {
        $key = "udie:fp:{$tenantId}:{$fingerprintHash}";
        Cache::put($key, $deviceId, $ttlSeconds);
    }

    /**
     * Get cached device ID from fingerprint
     */
    public function getCachedDeviceId(int $tenantId, string $fingerprintHash): ?int
    {
        $key = "udie:fp:{$tenantId}:{$fingerprintHash}";
        return Cache::get($key);
    }
}
