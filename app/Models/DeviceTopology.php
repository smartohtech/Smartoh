<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceTopology extends Model
{
    use BelongsToTenant;

    protected $table = 'device_topologies';

    protected $fillable = [
        'tenant_id',
        'parent_device_id',
        'child_device_id',
        'relationship_type',
        'confidence_score',
        'evidence',
        'last_verified_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'evidence' => 'array',
        'last_verified_at' => 'datetime',
    ];

    public function parentDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'parent_device_id');
    }

    public function childDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'child_device_id');
    }
}
