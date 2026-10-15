<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class FiberInfrastructure extends Model
{
    use BelongsToTenant;

    protected $table = 'fiber_infrastructure';

    protected $fillable = [
        'tenant_id', 'site_id', 'type', 'name', 'code',
        'latitude', 'longitude', 'route_coordinates_json',
        'capacity_cores', 'used_cores', 'status', 'notes'
    ];

    protected $casts = [
        'route_coordinates_json' => 'array',
    ];
}
