<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'provider',
        'type',
        'name',
        'shortcode',
        'settlement_account_label',
        'settlement_mode',
        'is_active',
        'is_sandbox',
        'health_status',
        'last_tested_at',
        'last_webhook_received_at',
        'credentials',
        'credentials_encrypted',
        'webhook_secret',
        'provider_limitations',
        'is_non_custodial',
    ];

    protected $hidden = [
        'credentials_encrypted',
        'webhook_secret',
    ];

    protected $casts = [
        'is_active'                => 'boolean',
        'is_sandbox'               => 'boolean',
        'is_non_custodial'         => 'boolean',
        'last_tested_at'           => 'datetime',
        'last_webhook_received_at' => 'datetime',
    ];

    public function getCredentialsAttribute(): array
    {
        try {
            return json_decode(decrypt($this->credentials_encrypted), true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setCredentialsAttribute(array $value): void
    {
        $this->attributes['credentials_encrypted'] = encrypt(json_encode($value));
    }

    /**
     * Return masked credentials safe for ISP Admin UI display (never expose raw secrets to frontend).
     */
    public function getMaskedCredentialsAttribute(): array
    {
        $creds = $this->credentials;
        $masked = [];
        foreach ($creds as $key => $val) {
            if (!is_string($val)) {
                $masked[$key] = $val;
                continue;
            }
            if (in_array($key, ['consumer_secret', 'passkey', 'client_secret', 'api_secret', 'webhook_secret'], true)) {
                $masked[$key] = strlen($val) > 6
                    ? substr($val, 0, 3) . str_repeat('*', max(4, strlen($val) - 6)) . substr($val, -3)
                    : '********';
            } elseif (in_array($key, ['consumer_key', 'client_id'], true)) {
                $masked[$key] = strlen($val) > 6
                    ? substr($val, 0, 4) . '...' . substr($val, -4)
                    : $val;
            } else {
                $masked[$key] = $val;
            }
        }
        return $masked;
    }

    public function getEnvironmentAttribute(): string
    {
        return $this->is_sandbox ? 'sandbox' : 'production';
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }
}
