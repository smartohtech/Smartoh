<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryJob extends Model
{
    use BelongsToTenant;

    protected $table = 'discovery_jobs';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'vrf_id',
        'source',
        'target_scope',
        'status',
        'devices_discovered_count',
        'devices_identified_count',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function vrf(): BelongsTo
    {
        return $this->belongsTo(Vrf::class);
    }
}
