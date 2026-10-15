<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OspfConfig extends Model
{
    use BelongsToTenant;

    protected $table = 'ospf_configs';

    protected $fillable = [
        'tenant_id', 'mikrotik_device_id', 'area', 'router_id',
        'interface', 'cost', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mikrotikDevice(): BelongsTo
    {
        return $this->belongsTo(MikroTikDevice::class);
    }
}
