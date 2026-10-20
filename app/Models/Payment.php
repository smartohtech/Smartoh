<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'site_id',
        'client_id',
        'invoice_id',
        'payment_gateway_id',
        'transaction_reference',
        'external_transaction_id',
        'payment_method',
        'source_channel',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'currency',
        'phone_number',
        'account_reference',
        'payer_name',
        'status',
        'verification_status',
        'reconciliation_status',
        'settlement_status',
        'activation_status',
        'idempotency_key',
        'raw_payload',
        'paid_at',
        'verified_at',
        'reconciled_at',
        'settled_at',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'allocated_amount'   => 'decimal:2',
        'unallocated_amount' => 'decimal:2',
        'paid_at'            => 'datetime',
        'verified_at'        => 'datetime',
        'reconciled_at'      => 'datetime',
        'settled_at'         => 'datetime',
        'reversed_at'        => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function activationJob(): HasOne
    {
        return $this->hasOne(NetworkActivationJob::class)->latestOfMany();
    }

    public function reconciliationException(): HasOne
    {
        return $this->hasOne(PaymentReconciliationException::class)->latestOfMany();
    }

    /**
     * Human-readable operational status that strictly separates payment verification from connectivity.
     * Never displays "Connected" unless connectivity has actually been confirmed.
     */
    public function getHumanLifecycleStatusAttribute(): string
    {
        if ($this->status === 'reversed' || $this->verification_status === 'reversed') {
            return 'Payment reversed — eligibility reassessed';
        }
        if ($this->verification_status === 'pending_verification' || $this->status === 'pending') {
            return 'Payment pending verification';
        }
        if ($this->verification_status === 'unverified_manual') {
            return 'Unverified manual reference — pending review';
        }
        if ($this->verification_status === 'rejected' || $this->status === 'failed') {
            return 'Payment verification failed';
        }

        // Payment is verified: check activation status
        return match ($this->activation_status) {
            'connected_verified' => 'Connected',
            'applied'            => 'Payment verified — activation applied',
            'retrying'           => 'Payment received — activation retrying',
            'queued'             => 'Payment verified — activation queued',
            'failed'             => 'Payment received — activation failed (NOC alerted)',
            default              => 'Payment verified',
        };
    }
}
