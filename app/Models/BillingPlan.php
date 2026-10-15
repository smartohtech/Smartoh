<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'type', 'price', 'currency',
        'download_speed_kbps', 'upload_speed_kbps', 'fup_limit_gb',
        'fup_throttled_download_kbps', 'fup_throttled_upload_kbps',
        'burst_limit', 'burst_threshold', 'burst_time', 'contention_ratio',
        'validity_days', 'vlan_id', 'mikrotik_profile_name', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function getFormattedSpeedAttribute(): string
    {
        $dl = $this->download_speed_kbps >= 1024 ? ($this->download_speed_kbps / 1024) . 'M' : $this->download_speed_kbps . 'K';
        $ul = $this->upload_speed_kbps >= 1024 ? ($this->upload_speed_kbps / 1024) . 'M' : $this->upload_speed_kbps . 'K';
        return "{$dl} / {$ul}";
    }
}
