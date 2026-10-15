<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    protected $table = 'device_models';

    protected $fillable = [
        'manufacturer',
        'model',
        'product_class',
        'device_type',
        'image_url',
        'thumbnail_url',
        'icon_url',
        'identification_signatures',
        'capabilities',
        'supported_protocols',
        'monitoring_templates',
        'is_global',
    ];

    protected $casts = [
        'identification_signatures' => 'array',
        'capabilities' => 'array',
        'supported_protocols' => 'array',
        'monitoring_templates' => 'array',
        'is_global' => 'boolean',
    ];

    public function tenantModels(): HasMany
    {
        return $this->hasMany(TenantDeviceModel::class, 'global_device_model_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'device_model_id');
    }
}
