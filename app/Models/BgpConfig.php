<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BgpConfig extends Model
{
    use BelongsToTenant;

    protected $table = 'bgp_configs';

    protected $fillable = [
        'tenant_id', 'mikrotik_device_id', 'as_number', 'router_id',
        'peer_name', 'peer_address', 'peer_as', 'is_active', 'status'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mikrotikDevice(): BelongsTo
    {
        return $this->belongsTo(MikroTikDevice::class);
    }
}
