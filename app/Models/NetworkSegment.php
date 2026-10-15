<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkSegment extends Model
{
    use BelongsToTenant;

    protected $table = 'network_segments';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'vrf_id',
        'name',
        'vlan_id',
        'subnet_cidr',
        'gateway_ip',
        'interface_name',
        'segment_type',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function vrf(): BelongsTo
    {
        return $this->belongsTo(Vrf::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
