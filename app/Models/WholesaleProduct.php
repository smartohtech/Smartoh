<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleProduct extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'capacity_pool_id', 'name', 'committed_bandwidth_cir_mbps',
        'burst_bandwidth_pir_mbps', 'contention_ratio', 'handoff_type',
        'price_per_mbps', 'setup_fee', 'is_active'
    ];

    protected $casts = [
        'price_per_mbps' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function capacityPool(): BelongsTo
    {
        return $this->belongsTo(WholesaleCapacityPool::class, 'capacity_pool_id');
    }
}
