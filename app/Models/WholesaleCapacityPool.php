<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WholesaleCapacityPool extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'exchange_point', 'total_capacity_mbps',
        'allocated_capacity_mbps', 'reserved_capacity_mbps'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(WholesaleProduct::class, 'capacity_pool_id');
    }

    public function getAvailableCapacityMbpsAttribute(): int
    {
        return max(0, $this->total_capacity_mbps - ($this->allocated_capacity_mbps + $this->reserved_capacity_mbps));
    }
}
