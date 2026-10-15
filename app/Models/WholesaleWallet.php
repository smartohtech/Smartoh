<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WholesaleWallet extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'reseller_company_name', 'contact_person', 'email', 'phone',
        'balance', 'credit_limit', 'currency', 'is_locked'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(WholesaleOrder::class);
    }
}
