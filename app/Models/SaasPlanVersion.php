<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlanVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'saas_plan_id',
        'version_number',
        'features',
        'monthly_price',
        'annual_price',
        'currency',
        'changelog',
        'is_current',
    ];

    protected $casts = [
        'features'       => 'array',
        'monthly_price'  => 'decimal:2',
        'annual_price'   => 'decimal:2',
        'version_number' => 'integer',
        'is_current'     => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    /**
     * Unified limits & features accessor so views using ->limits or ->features
     * both receive normalized quota keys and feature flags.
     */
    public function getLimitsAttribute(): array
    {
        $f = $this->features ?? [];

        return array_merge($f, [
            'subscribers'     => $f['max_subscribers'] ?? $f['subscribers'] ?? 300,
            'max_subscribers' => $f['max_subscribers'] ?? $f['subscribers'] ?? 300,
            'devices'         => $f['max_routers'] ?? $f['devices'] ?? 3,
            'max_routers'     => $f['max_routers'] ?? $f['devices'] ?? 3,
            'staff'           => $f['max_staff'] ?? $f['staff'] ?? 3,
            'max_staff'       => $f['max_staff'] ?? $f['staff'] ?? 3,
            'sites'           => $f['max_sites'] ?? $f['sites'] ?? 1,
            'max_sites'       => $f['max_sites'] ?? $f['sites'] ?? 1,
            'cpes'            => $f['max_cpes'] ?? $f['cpes'] ?? 100,
            'max_cpes'        => $f['max_cpes'] ?? $f['cpes'] ?? 100,
            'storage_gb'      => $f['storage_gb'] ?? 10,
        ]);
    }
}
