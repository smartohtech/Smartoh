<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'start_date', 'end_date', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
