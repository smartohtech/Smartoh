<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'device_events';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'event_type',
        'severity',
        'title',
        'description',
        'details',
        'is_resolved',
    ];

    protected $casts = [
        'details' => 'array',
        'is_resolved' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
