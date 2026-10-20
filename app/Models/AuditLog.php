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
        'previous_hash',
        'integrity_hash',
        'support_access_reason',
        'action',
        'details',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            if (empty($log->integrity_hash)) {
                $prev = static::query()
                    ->when($log->tenant_id, fn($q) => $q->where('tenant_id', $log->tenant_id))
                    ->latest('id')
                    ->value('integrity_hash') ?? 'GENESIS';

                $log->previous_hash = $prev;
                $payload = implode('|', [
                    $prev,
                    (string) ($log->tenant_id ?? '0'),
                    (string) ($log->user_id ?? '0'),
                    (string) ($log->event ?? ''),
                    (string) ($log->auditable_type ?? ''),
                    (string) ($log->auditable_id ?? ''),
                    json_encode($log->new_values ?? []),
                ]);
                $log->integrity_hash = hash('sha256', $payload);
            }
        });
    }

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
