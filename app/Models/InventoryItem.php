<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'site_id', 'name', 'sku', 'category',
        'quantity_in_stock', 'min_threshold_alert', 'unit_cost', 'supplier_name'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];
}
