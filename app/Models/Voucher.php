<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'site_id', 'billing_plan_id', 'code', 'type',
        'price', 'data_limit_mb', 'duration_minutes', 'status',
        'used_by_client_id', 'used_at', 'expires_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'used_by_client_id');
    }
}
