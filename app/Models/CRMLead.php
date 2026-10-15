<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CRMLead extends Model
{
    use BelongsToTenant;

    protected $table = 'crm_leads';

    protected $fillable = [
        'tenant_id', 'site_id', 'assigned_to_user_id', 'name', 'company_name',
        'phone', 'email', 'location', 'latitude', 'longitude',
        'service_interest', 'estimated_value', 'status', 'notes'
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
