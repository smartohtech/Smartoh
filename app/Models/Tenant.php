<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'custom_domain',
        'company_email',
        'company_phone',
        'address',
        'brand_color',
        'accent_color',
        'logo_url',
        'theme_mode',
        'subscription_tier',
        'status',
        'max_routers',
        'max_clients',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'max_routers' => 'integer',
        'max_clients' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->subdomain)) {
                $base = \Illuminate\Support\Str::slug($tenant->name ?: 'tenant');
                $tenant->subdomain = ($base ?: 'tenant') . '-' . strtolower(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function billingPlans(): HasMany
    {
        return $this->hasMany(BillingPlan::class);
    }

    public function mikrotikDevices(): HasMany
    {
        return $this->hasMany(MikroTikDevice::class);
    }

    public function clientRouters(): HasMany
    {
        return $this->hasMany(ClientRouter::class);
    }

    public function oltDevices(): HasMany
    {
        return $this->hasMany(OLTDevice::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentGateways(): HasMany
    {
        return $this->hasMany(PaymentGateway::class);
    }

    public function securitySetting(): HasOne
    {
        return $this->hasOne(SecuritySetting::class);
    }

    public function saasSubscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class)->latest('created_at');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(SaasSubscription::class)->latestOfMany();
    }

    public function saasInvoices(): HasMany
    {
        return $this->hasMany(SaasInvoice::class)->latest('created_at');
    }

    public function usage(): HasOne
    {
        return $this->hasOne(TenantUsage::class);
    }

    public function application(): HasOne
    {
        return $this->hasOne(IspApplication::class, 'provisioned_tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
