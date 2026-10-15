<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantDeviceModel extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_device_models';

    protected $fillable = [
        'tenant_id',
        'global_device_model_id',
        'custom_name',
        'custom_description',
        'custom_image_url',
        'custom_thumbnail_url',
        'custom_capabilities',
        'custom_signatures',
        'configuration_templates',
        'is_approved',
    ];

    protected $casts = [
        'custom_capabilities' => 'array',
        'custom_signatures' => 'array',
        'configuration_templates' => 'array',
        'is_approved' => 'boolean',
    ];

    public function globalModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'global_device_model_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'tenant_device_model_id');
    }

    public function getEffectiveNameAttribute(): string
    {
        return $this->custom_name ?: ($this->globalModel?->model ?? 'Custom Device');
    }
}
