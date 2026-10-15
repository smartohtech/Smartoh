<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IspApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'company_name',
        'subdomain',
        'contact_name',
        'email',
        'phone',
        'country',
        'address',
        'network_type',
        'current_subscribers',
        'hardware_type',
        'saas_plan_id',
        'saas_plan_version_id',
        'negotiated_price',
        'billing_interval',
        'status',
        'rejection_reason',
        'info_request_notes',
        'resubmitted_notes',
        'reviewed_by_user_id',
        'reviewed_at',
        'provisioned_tenant_id',
        'provisioned_at',
        'admin_temp_password',
    ];

    protected $casts = [
        'negotiated_price'    => 'decimal:2',
        'current_subscribers' => 'integer',
        'reviewed_at'         => 'datetime',
        'provisioned_at'      => 'datetime',
    ];

    public function saasPlan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function saasPlanVersion(): BelongsTo
    {
        return $this->belongsTo(SaasPlanVersion::class, 'saas_plan_version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function provisionedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'provisioned_tenant_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isInfoRequested(): bool
    {
        return $this->status === 'info_requested';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
