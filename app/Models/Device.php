<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use BelongsToTenant;

    protected $table = 'devices';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'customer_id',
        'service_id',
        'device_model_id',
        'tenant_device_model_id',
        'parent_device_id',
        'device_type',
        'manufacturer',
        'model',
        'product_class',
        'serial_number',
        'mac_address',
        'ip_address',
        'ipv6_addresses',
        'hostname',
        'firmware_version',
        'hardware_version',
        'vrf_id',
        'network_segment_id',
        'vlan_id',
        'subnet',
        'interface',
        'status',
        'identification_status',
        'confidence_score',
        'fingerprint_hash',
        'first_seen',
        'last_seen',
        'last_identified_at',
        'last_management_check',
        'management_status',
        'primary_management_protocol',
        'fallback_management_protocol',
        'supported_protocols',
        'capabilities',
        'olt_id',
        'pon_id',
        'onu_id',
        'pppoe_session_id',
        'vpn_peer_id',
        'raw_metadata',
        'notes',
    ];

    protected $casts = [
        'ipv6_addresses' => 'array',
        'supported_protocols' => 'array',
        'capabilities' => 'array',
        'raw_metadata' => 'array',
        'confidence_score' => 'decimal:2',
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
        'last_identified_at' => 'datetime',
        'last_management_check' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    public function tenantDeviceModel(): BelongsTo
    {
        return $this->belongsTo(TenantDeviceModel::class, 'tenant_device_model_id');
    }

    public function parentDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'parent_device_id');
    }

    public function childDevices(): HasMany
    {
        return $this->hasMany(Device::class, 'parent_device_id');
    }

    public function vrf(): BelongsTo
    {
        return $this->belongsTo(Vrf::class);
    }

    public function networkSegment(): BelongsTo
    {
        return $this->belongsTo(NetworkSegment::class);
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(OLTDevice::class, 'olt_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DeviceEvidence::class);
    }

    public function topologiesAsParent(): HasMany
    {
        return $this->hasMany(DeviceTopology::class, 'parent_device_id');
    }

    public function topologiesAsChild(): HasMany
    {
        return $this->hasMany(DeviceTopology::class, 'child_device_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class)->latest();
    }

    /**
     * Check if device is a bridged ONT
     */
    public function isBridgedOnt(): bool
    {
        return in_array($this->device_type, ['ont', 'onu'])
            && (($this->raw_metadata['wan_mode'] ?? '') === 'bridge' || $this->childDevices()->exists());
    }

    /**
     * Check if device is a routed ONT
     */
    public function isRoutedOnt(): bool
    {
        return in_array($this->device_type, ['ont', 'onu'])
            && (($this->raw_metadata['wan_mode'] ?? '') === 'route' || ($this->raw_metadata['wan_mode'] ?? '') === 'pppoe');
    }

    /**
     * Resolve effective display name according to tenant catalog overrides
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->tenantDeviceModel && $this->tenantDeviceModel->custom_name) {
            return $this->tenantDeviceModel->custom_name;
        }

        if ($this->model && $this->model !== 'Unknown Device') {
            return ($this->manufacturer ? $this->manufacturer . ' ' : '') . $this->model;
        }

        if ($this->manufacturer) {
            return "Unknown {$this->manufacturer} Device";
        }

        return "Unidentified Device (#{$this->id})";
    }

    /**
     * Confidence category label
     */
    public function getConfidenceCategoryAttribute(): string
    {
        $score = (float) $this->confidence_score;
        if ($score >= 90.0) return 'VERIFIED';
        if ($score >= 75.0) return 'HIGH CONFIDENCE';
        if ($score >= 50.0) return 'PARTIALLY IDENTIFIED';
        return 'UNKNOWN / REQUIRES REVIEW';
    }

    /**
     * Confidence badge CSS color class
     */
    public function getConfidenceBadgeColorAttribute(): string
    {
        $score = (float) $this->confidence_score;
        if ($score >= 90.0) return 'emerald';
        if ($score >= 75.0) return 'cyan';
        if ($score >= 50.0) return 'amber';
        return 'rose';
    }

    /**
     * Resolved Device Image URL
     */
    public function getResolvedImageUrlAttribute(): string
    {
        return app(\App\Services\UDIE\DeviceImageResolver::class)->resolveImage($this, 'full');
    }

    /**
     * Resolved Device Thumbnail URL
     */
    public function getResolvedThumbnailUrlAttribute(): string
    {
        return app(\App\Services\UDIE\DeviceImageResolver::class)->resolveImage($this, 'thumbnail');
    }
}
