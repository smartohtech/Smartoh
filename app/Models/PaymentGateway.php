<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'provider', 'name', 'is_active', 'is_sandbox',
        'credentials_encrypted', 'webhook_secret'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
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
}
