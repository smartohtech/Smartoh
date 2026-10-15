<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRouter extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'site_id', 'device_profile_id',
        'serial_number', 'mac_address', 'ip_address', 'manufacturer',
        'model', 'hardware_version', 'software_version',
        'preferred_management_path', 'fallback_management_path',
        'cwmp_connection_url', 'cwmp_username', 'cwmp_password_encrypted',
        'wifi_ssid', 'wifi_password', 'optical_power_rx', 'optical_power_tx',
        'uptime_seconds', 'last_inform_at', 'status'
    ];

    protected $casts = [
        'optical_power_rx' => 'decimal:2',
        'optical_power_tx' => 'decimal:2',
        'last_inform_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
