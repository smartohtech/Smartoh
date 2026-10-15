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
}
