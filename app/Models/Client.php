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
        'tenant_id', 'site_id', 'mikrotik_device_id', 'billing_plan_id', 'reseller_id',
        'account_number', 'name', 'email', 'phone', 'id_passport_number',
        'address', 'latitude', 'longitude', 'service_type',
        'pppoe_username', 'pppoe_password', 'assigned_ip', 'mac_address',
        'status', 'connection_status', 'wallet_balance', 'data_usage_bytes', 'current_cycle_start',
        'current_cycle_end', 'service_suspended_at', 'last_activated_at', 'auto_renew'
    ];

    protected $casts = [
        'wallet_balance'       => 'decimal:2',
        'current_cycle_start'  => 'datetime',
        'current_cycle_end'    => 'datetime',
        'service_suspended_at' => 'datetime',
        'last_activated_at'    => 'datetime',
        'auto_renew'           => 'boolean',
    ];

    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', $this->name ?? '', 2);
        return $parts[0] ?? '';
    }

    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name ?? '', 2);
        return $parts[1] ?? '';
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function mikrotikDevice(): BelongsTo
    {
        return $this->belongsTo(MikroTikDevice::class, 'mikrotik_device_id');
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

    public function activationJobs(): HasMany
    {
        return $this->hasMany(NetworkActivationJob::class);
    }

    public function latestActivationJob(): HasOne
    {
        return $this->hasOne(NetworkActivationJob::class)->latestOfMany();
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

    public function getOutstandingBalanceAttribute(): float
    {
        $unpaid = $this->invoices()
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->get()
            ->sum(fn($inv) => max(0, (float)$inv->total_amount - (float)$inv->paid_amount));
        return round((float)$unpaid, 2);
    }
}
