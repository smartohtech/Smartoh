<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasPlanChangeRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING_REVIEW  = 'pending_review';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_APPROVED        = 'approved';
    public const STATUS_COMPLETED       = 'completed';
    public const STATUS_REJECTED        = 'rejected';
    public const STATUS_CANCELLED       = 'cancelled';

    protected $fillable = [
        'reference_number',
        'tenant_id',
        'subscription_id',
        'requested_by_user_id',
        'current_plan_id',
        'target_plan_id',
        'change_type',
        'billing_interval',
        'total_cycle_days',
        'remaining_days',
        'current_plan_rate',
        'target_plan_rate',
        'unused_credit',
        'prorated_new_charge',
        'prorated_amount_to_add',
        'credit_carryover',
        'currency',
        'effective_mode',
        'payment_method',
        'payment_reference',
        'saas_invoice_id',
        'status',
        'reason_notes',
        'admin_notes',
        'reviewed_by_user_id',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'total_cycle_days'       => 'integer',
        'remaining_days'         => 'integer',
        'current_plan_rate'      => 'decimal:2',
        'target_plan_rate'       => 'decimal:2',
        'unused_credit'          => 'decimal:2',
        'prorated_new_charge'    => 'decimal:2',
        'prorated_amount_to_add' => 'decimal:2',
        'credit_carryover'       => 'decimal:2',
        'reviewed_at'            => 'datetime',
        'applied_at'             => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SaasSubscription::class, 'subscription_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'current_plan_id');
    }

    public function targetPlan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'target_plan_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SaasInvoice::class, 'saas_invoice_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function isUpgrade(): bool
    {
        return $this->change_type === 'upgrade';
    }

    public function isDowngrade(): bool
    {
        return $this->change_type === 'downgrade';
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_REVIEW,
            self::STATUS_PENDING_PAYMENT,
        ], true);
    }
}
