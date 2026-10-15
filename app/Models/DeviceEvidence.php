<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvidence extends Model
{
    use BelongsToTenant;

    protected $table = 'device_evidences';

    protected $fillable = [
        'tenant_id',
        'device_id',
        'source',
        'attribute_name',
        'attribute_value',
        'evidence_scope',
        'is_contradictory',
        'confidence_weight',
        'penalty_weight',
        'observed_at',
        'raw_payload',
    ];

    protected $casts = [
        'evidence_scope' => 'array',
        'raw_payload' => 'array',
        'is_contradictory' => 'boolean',
        'confidence_weight' => 'decimal:2',
        'penalty_weight' => 'decimal:2',
        'observed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
