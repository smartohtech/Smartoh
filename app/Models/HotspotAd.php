<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class HotspotAd extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'title', 'advertiser_name', 'media_image_url',
        'destination_url', 'duration_seconds', 'required_views',
        'impressions_count', 'clicks_count', 'reward_points_per_view', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
