<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NOCIncident extends Model
{
    use BelongsToTenant;

    protected $table = 'noc_incidents';

    protected $fillable = [
        'tenant_id', 'site_id', 'assigned_technician_id',
        'incident_number', 'title', 'description', 'severity',
        'status', 'affected_component', 'affected_clients_count',
        'started_at', 'resolved_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'assigned_technician_id');
    }
}
