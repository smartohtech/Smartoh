<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vrf extends Model
{
    use BelongsToTenant;

    protected $table = 'vrfs';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'name',
        'route_distinguisher',
        'description',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function networkSegments(): HasMany
    {
        return $this->hasMany(NetworkSegment::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
