<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ONTDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'ont_devices';

    protected $fillable = [
        'tenant_id', 'olt_device_id', 'client_id', 'serial_number',
        'mac_address', 'pon_port', 'onu_slot', 'model',
        'rx_power_dbm', 'tx_power_dbm', 'vlan_id', 'wan_mode', 'status'
    ];

    protected $casts = [
        'rx_power_dbm' => 'decimal:2',
        'tx_power_dbm' => 'decimal:2',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(OLTDevice::class, 'olt_device_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
