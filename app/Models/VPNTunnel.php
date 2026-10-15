<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VPNTunnel extends Model
{
    use BelongsToTenant;

    protected $table = 'vpn_tunnels';

    protected $fillable = [
        'tenant_id', 'client_router_id', 'tunnel_name', 'tunnel_type',
        'assigned_ip', 'server_endpoint', 'public_key', 'private_key_encrypted',
        'preshared_key_encrypted', 'allowed_ips', 'bytes_rx', 'bytes_tx',
        'last_handshake_at', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_handshake_at' => 'datetime',
    ];
}
