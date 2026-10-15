<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'tenant_id',
        'client_id',
        'role',
        'is_approved',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isSuperAdmin(): bool
    {
        return in_array(strtolower($this->role), ['super_admin', 'superadmin'], true);
    }

    public function isIspAdmin(): bool
    {
        return in_array(strtolower($this->role), ['isp_admin', 'tenant_admin'], true);
    }

    public function isTenantAdmin(): bool
    {
        return $this->isIspAdmin();
    }

    public function isTechnician(): bool
    {
        return strtolower($this->role) === 'technician';
    }

    public function isBillingOfficer(): bool
    {
        return strtolower($this->role) === 'billing_officer';
    }

    public function isClient(): bool
    {
        return strtolower($this->role) === 'client';
    }

    public function hasRole(string ...$roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userRole = strtolower($this->role);

        foreach ($roles as $r) {
            $checkRole = strtolower($r);
            if ($userRole === $checkRole) {
                return true;
            }
            if (in_array($checkRole, ['tenant_admin', 'isp_admin'], true) && $this->isTenantAdmin()) {
                return true;
            }
            if (in_array($checkRole, ['super_admin', 'superadmin'], true) && $this->isSuperAdmin()) {
                return true;
            }
        }

        return false;
    }
}
