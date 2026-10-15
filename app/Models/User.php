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
        return $this->role === 'super_admin';
    }

    public function isIspAdmin(): bool
    {
        return $this->role === 'isp_admin';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isBillingOfficer(): bool
    {
        return $this->role === 'billing_officer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function hasRole(string ...$roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return in_array($this->role, $roles, true);
    }
}
