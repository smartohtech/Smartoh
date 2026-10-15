<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use BelongsToTenant;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'tenant_id', 'site_id', 'code', 'name', 'type', 'current_balance', 'is_active'
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
