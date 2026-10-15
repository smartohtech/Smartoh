<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'action',
        'details',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function setActionAttribute($value): void
    {
        $this->attributes['event'] = $value;
    }

    public function getActionAttribute(): ?string
    {
        return $this->attributes['event'] ?? null;
    }

    public function setDetailsAttribute($value): void
    {
        $current = $this->new_values ?? [];
        if (is_array($current)) {
            $current['details'] = $value;
        } else {
            $current = ['details' => $value];
        }
        $this->attributes['new_values'] = json_encode($current);
    }

    public function getDetailsAttribute(): string
    {
        if (is_array($this->new_values) && isset($this->new_values['details'])) {
            return (string)$this->new_values['details'];
        }
        return (string)($this->event ?? '');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
