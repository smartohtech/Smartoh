<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuritySetting extends Model
{
    protected $fillable = [
        'tenant_id', 'require_two_factor', 'session_timeout_minutes',
        'max_login_attempts', 'password_expiry_days', 'ip_whitelist_enabled', 'ip_whitelist'
    ];

    protected $casts = [
        'require_two_factor' => 'boolean',
        'ip_whitelist_enabled' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
