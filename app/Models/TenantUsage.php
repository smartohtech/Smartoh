<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsage extends Model
{
    use HasFactory;

    protected $table = 'tenant_usage';

    protected $fillable = [
        'tenant_id',
        'active_subscribers_count',
        'managed_routers_count',
        'active_staff_count',
        'active_sites_count',
        'last_calculated_at',
    ];

    protected $casts = [
        'active_subscribers_count' => 'integer',
        'managed_routers_count'    => 'integer',
        'active_staff_count'       => 'integer',
        'active_sites_count'       => 'integer',
        'last_calculated_at'       => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
