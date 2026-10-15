<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'site_id', 'billing_plan_id', 'reseller_id',
        'account_number', 'name', 'email', 'phone', 'id_passport_number',
        'address', 'latitude', 'longitude', 'service_type',
        'pppoe_username', 'pppoe_password', 'assigned_ip', 'mac_address',
        'status', 'wallet_balance', 'data_usage_bytes', 'current_cycle_start',
        'current_cycle_end', 'service_suspended_at', 'auto_renew'
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'current_cycle_start' => 'datetime',
        'current_cycle_end' => 'datetime',
        'service_suspended_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function clientRouter(): HasOne
    {
        return $this->hasOne(ClientRouter::class);
    }

    public function ontDevice(): HasOne
    {
        return $this->hasOne(ONTDevice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
