<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'site_id', 'staff_id_number', 'phone',
        'specialization', 'is_available', 'current_latitude', 'current_longitude', 'last_active_at'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installationJobs(): HasMany
    {
        return $this->hasMany(InstallationJob::class);
    }
}
