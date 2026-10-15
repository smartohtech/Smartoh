<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaasPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'currency',
        'trial_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price'  => 'decimal:2',
        'trial_days'    => 'integer',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(SaasPlanVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(SaasPlanVersion::class)->where('is_current', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IspApplication::class);
    }
}
