<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetworkActivationJob extends Model
{
    use BelongsToTenant;

    public const STATUS_QUEUED             = 'queued';
    public const STATUS_PROCESSING         = 'processing';
    public const STATUS_RETRYING           = 'retrying';
    public const STATUS_APPLIED            = 'applied';
    public const STATUS_CONNECTED_VERIFIED = 'connected_verified';
    public const STATUS_FAILED_DEAD_LETTER = 'failed_dead_letter';

    protected $fillable = [
        'tenant_id',
        'client_id',
        'payment_id',
        'mikrotik_device_id',
        'service_type',
        'action',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'executed_at',
        'verified_online_at',
        'command_payload',
        'router_response',
        'last_error',
        'noc_alerted',
    ];

    protected $casts = [
        'attempts'           => 'integer',
        'max_attempts'       => 'integer',
        'next_retry_at'      => 'datetime',
        'executed_at'        => 'datetime',
        'verified_online_at' => 'datetime',
        'command_payload'    => 'array',
        'router_response'    => 'array',
        'noc_alerted'        => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function mikrotikDevice(): BelongsTo
    {
        return $this->belongsTo(MikroTikDevice::class, 'mikrotik_device_id');
    }
}
