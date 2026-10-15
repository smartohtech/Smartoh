<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'wholesale_wallet_id', 'wholesale_product_id',
        'order_number', 'bandwidth_mbps', 'monthly_charge', 'status',
        'vlan_id', 'allocated_subnet', 'activated_at'
    ];

    protected $casts = [
        'monthly_charge' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(WholesaleWallet::class, 'wholesale_wallet_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WholesaleProduct::class, 'wholesale_product_id');
    }
}
