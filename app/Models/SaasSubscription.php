<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasSubscription extends Model
{
    use HasFactory;

    // 12 Valid States
    public const STATUS_PENDING_APPROVAL            = 'PENDING_APPROVAL';
    public const STATUS_REJECTED                    = 'REJECTED';
    public const STATUS_APPROVED_PENDING_ACTIVATION = 'APPROVED_PENDING_ACTIVATION';
    public const STATUS_TRIAL_ACTIVE                = 'TRIAL_ACTIVE';
    public const STATUS_TRIAL_EXPIRING              = 'TRIAL_EXPIRING';
    public const STATUS_TRIAL_EXPIRED               = 'TRIAL_EXPIRED';
    public const STATUS_PAYMENT_PENDING             = 'PAYMENT_PENDING';
    public const STATUS_ACTIVE_PAID                 = 'ACTIVE_PAID';
    public const STATUS_PAST_DUE                    = 'PAST_DUE';
    public const STATUS_GRACE_PERIOD                = 'GRACE_PERIOD';
    public const STATUS_SUSPENDED                   = 'SUSPENDED';
    public const STATUS_CANCELLED                   = 'CANCELLED';

    protected $fillable = [
        'tenant_id',
        'saas_plan_id',
        'saas_plan_version_id',
        'status',
        'billing_interval',
        'amount',
        'currency',
        'trial_started_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'grace_period_ends_at',
        'cancelled_at',
        'custom_limits',
    ];

    protected $casts = [
        'amount'                => 'decimal:2',
        'trial_started_at'      => 'datetime',
        'trial_ends_at'         => 'datetime',
        'current_period_start'  => 'datetime',
        'current_period_end'    => 'datetime',
        'grace_period_ends_at'  => 'datetime',
        'cancelled_at'          => 'datetime',
        'custom_limits'         => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(SaasPlanVersion::class, 'saas_plan_version_id');
    }

    public function trialEvents(): HasMany
    {
        return $this->hasMany(TrialEvent::class, 'subscription_id')->latest('recorded_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SaasInvoice::class, 'subscription_id')->latest('created_at');
    }

    public function planChangeRequests(): HasMany
    {
        return $this->hasMany(SaasPlanChangeRequest::class, 'subscription_id')->latest('created_at');
    }

    public function isTrial(): bool
    {
        return in_array($this->status, [
            self::STATUS_TRIAL_ACTIVE,
            self::STATUS_TRIAL_EXPIRING,
        ], true);
    }

    public function isPaidActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE_PAID;
    }

    public function isOperational(): bool
    {
        return in_array($this->status, [
            self::STATUS_TRIAL_ACTIVE,
            self::STATUS_TRIAL_EXPIRING,
            self::STATUS_ACTIVE_PAID,
            self::STATUS_GRACE_PERIOD,
        ], true);
    }

    public function daysLeftInTrial(): int
    {
        if (!$this->trial_ends_at || Carbon::now()->greaterThanOrEqualTo($this->trial_ends_at)) {
            return 0;
        }
        return (int) Carbon::now()->diffInDays($this->trial_ends_at, false) + 1;
    }

    public function hoursLeftInTrial(): int
    {
        if (!$this->trial_ends_at || Carbon::now()->greaterThanOrEqualTo($this->trial_ends_at)) {
            return 0;
        }
        return (int) Carbon::now()->diffInHours($this->trial_ends_at, false);
    }

    public function daysRemainingInTrial(): int
    {
        return $this->daysLeftInTrial();
    }

    public function hoursRemainingInTrial(): int
    {
        return $this->hoursLeftInTrial();
    }
}
