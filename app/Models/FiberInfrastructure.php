<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiberInfrastructure extends Model
{
    use BelongsToTenant;

    protected $table = 'fiber_infrastructure';

    public const BASE_ENUM_TYPES = ['pop', 'fat_box', 'closure', 'pole', 'fiber_route'];
    public const BASE_ENUM_STATUSES = ['operational', 'maintenance', 'damaged'];

    public const SUPPORTED_ASSET_SUBTYPES = [
        'pop'                => 'PoP / Core Network Site',
        'fat_box'            => 'FAT Distribution Box',
        'splice_closure'     => 'Fiber Splice Closure / Dome',
        'closure'            => 'Splice Closure',
        'manhole'            => 'Underground Manhole / Handhole',
        'cabinet'            => 'Street Fiber / FDH Cabinet',
        'distribution_point' => 'Optical Distribution Point (ODP/FDP)',
        'pole'               => 'Utility / Drop Pole',
        'olt'                => 'GPON / XGS-PON OLT Chassis',
        'onu_ont'            => 'Customer ONU / ONT Endpoint',
        'tower'              => 'Wireless Mast / Base Station Tower',
        'router'             => 'Core / Edge Router (MikroTik NAS)',
        'fiber_route'        => 'Fiber Optic Cable Route (Backbone/Drop)',
        'wireless_link'      => 'Point-to-Point (PtP) Wireless Link',
    ];

    public const SUPPORTED_SUBTYPES = self::SUPPORTED_ASSET_SUBTYPES;

    protected $fillable = [
        'tenant_id',
        'site_id',
        'parent_asset_id',
        'linked_device_type',
        'linked_device_id',
        'type',
        'asset_subtype',
        'name',
        'code',
        'county',
        'sub_county',
        'town',
        'estate_or_road',
        'latitude',
        'longitude',
        'coordinate_source',
        'elevation_m',
        'route_coordinates_json',
        'coordinates_json',
        'length_meters',
        'azimuth_deg',
        'frequency_ghz',
        'capacity_cores',
        'used_cores',
        'total_ports',
        'used_ports',
        'status',
        'notes',
        'metadata_json',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'latitude'               => 'float',
        'longitude'              => 'float',
        'elevation_m'            => 'float',
        'length_meters'          => 'float',
        'azimuth_deg'            => 'float',
        'frequency_ghz'          => 'float',
        'capacity_cores'         => 'integer',
        'used_cores'             => 'integer',
        'route_coordinates_json' => 'array',
        'metadata_json'          => 'array',
    ];

    protected $appends = [
        'effective_type',
        'coordinates_json',
        'total_ports',
        'used_ports',
    ];

    protected static function booted(): void
    {
        static::saving(function (FiberInfrastructure $asset) {
            $requestedType = strtolower(trim($asset->asset_subtype ?: $asset->type ?: 'fat_box'));
            $asset->asset_subtype = $requestedType;
            $asset->type = self::mapToBaseEnumType($requestedType);

            $rawStatus = strtolower(trim($asset->status ?: 'operational'));
            $meta = is_array($asset->metadata_json) ? $asset->metadata_json : [];
            $meta['ui_status'] = $rawStatus;
            $asset->metadata_json = $meta;
            $asset->status = self::mapToBaseEnumStatus($rawStatus);
        });
    }

    /**
     * Map any granular GIS asset type to the 5 base schema enum values for SQLite CHECK compatibility.
     */
    public static function mapToBaseEnumType(string $subtype): string
    {
        return match ($subtype) {
            'pop', 'olt', 'router'                               => 'pop',
            'fat_box', 'cabinet', 'distribution_point', 'onu_ont' => 'fat_box',
            'splice_closure', 'closure', 'manhole'               => 'closure',
            'pole', 'tower'                                      => 'pole',
            'fiber_route', 'wireless_link'                       => 'fiber_route',
            default                                              => in_array($subtype, self::BASE_ENUM_TYPES, true) ? $subtype : 'fat_box',
        };
    }

    public static function normalizeBaseType(string $subtype): string
    {
        return self::mapToBaseEnumType($subtype);
    }

    /**
     * Map status values ('active', 'faulty', 'planned', 'operational', 'maintenance', 'damaged')
     * to the 3 base SQLite CHECK constraint values ('operational', 'maintenance', 'damaged').
     */
    public static function mapToBaseEnumStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));
        return match ($s) {
            'active', 'operational', 'online' => 'operational',
            'maintenance', 'planned'          => 'maintenance',
            'faulty', 'damaged', 'offline'    => 'damaged',
            default                           => in_array($s, self::BASE_ENUM_STATUSES, true) ? $s : 'operational',
        };
    }

    public function getEffectiveTypeAttribute(): string
    {
        return $this->asset_subtype ?: $this->type ?: 'fat_box';
    }

    public function getTypeLabelAttribute(): string
    {
        $eff = $this->effective_type;
        return self::SUPPORTED_ASSET_SUBTYPES[$eff] ?? strtoupper(str_replace('_', ' ', $eff));
    }

    public function getCoordinatesJsonAttribute(): ?array
    {
        return $this->route_coordinates_json;
    }

    public function setCoordinatesJsonAttribute($value): void
    {
        $this->attributes['route_coordinates_json'] = is_string($value) ? $value : ($value !== null ? json_encode($value) : null);
    }

    public function getTotalPortsAttribute(): int
    {
        return (int) ($this->capacity_cores ?? 0);
    }

    public function setTotalPortsAttribute($value): void
    {
        $this->attributes['capacity_cores'] = (int) $value;
    }

    public function getUsedPortsAttribute(): int
    {
        return (int) ($this->used_cores ?? 0);
    }

    public function setUsedPortsAttribute($value): void
    {
        $this->attributes['used_cores'] = (int) $value;
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parentAsset(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_asset_id');
    }

    public function childAssets(): HasMany
    {
        return $this->hasMany(self::class, 'parent_asset_id');
    }
}
