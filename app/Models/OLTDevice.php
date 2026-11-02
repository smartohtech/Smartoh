<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OLTDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'olt_devices';

    protected $fillable = [
        'tenant_id', 'site_id', 'name', 'vendor', 'ip_address', 'latitude', 'longitude', 'county',
        'api_url', 'api_token_encrypted', 'snmp_community',
        'pon_ports_count', 'registered_onus_count', 'is_online'
    ];

    protected $casts = [
        'is_online' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function ontDevices(): HasMany
    {
        return $this->hasMany(ONTDevice::class, 'olt_device_id');
    }
}
