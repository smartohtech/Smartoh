<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReconciliationException extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'external_transaction_id',
        'submitted_reference',
        'phone_number',
        'payer_name',
        'amount',
        'exception_reason',
        'status',
        'resolved_client_id',
        'resolved_invoice_id',
        'resolved_by_user_id',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function resolvedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'resolved_client_id');
    }

    public function resolvedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'resolved_invoice_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
