<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationJob extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'site_id', 'client_id', 'technician_id', 'fiber_box_id',
        'job_number', 'scheduled_at', 'latitude', 'longitude',
        'fiber_cable_meters', 'optical_power_rx_dbm', 'installed_cpe_serial',
        'installed_ont_serial', 'photo_attachments_json', 'status',
        'technician_notes', 'completed_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'photo_attachments_json' => 'array',
        'optical_power_rx_dbm' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
