<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikroTikDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'mikrotik_devices';

    protected $fillable = [
        'tenant_id', 'site_id', 'name', 'ip_address', 'api_port',
        'api_username', 'api_password_encrypted', 'use_ssl', 'model',
        'routeros_version', 'identity', 'is_online', 'cpu_load',
        'free_memory_bytes', 'total_memory_bytes', 'uptime_seconds',
        'active_pppoe_sessions', 'active_hotspot_sessions', 'last_synced_at', 'last_error'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'use_ssl' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function getPasswordAttribute(): string
    {
        try {
            return decrypt($this->api_password_encrypted);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['api_password_encrypted'] = encrypt($value);
    }
}
