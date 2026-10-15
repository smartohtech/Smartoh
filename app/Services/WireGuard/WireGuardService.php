<?php

namespace App\Services\WireGuard;

use App\Models\VPNTunnel;

class WireGuardService
{
    /**
     * Generate WireGuard key pair
     */
    public function generateKeyPair(): array
    {
        $private = base64_encode(random_bytes(32));
        $public = base64_encode(hash('sha256', $private, true));

        return [
            'private_key' => $private,
            'public_key' => $public,
            'preshared_key' => base64_encode(random_bytes(32)),
        ];
    }

    /**
     * Generate standard WireGuard client configuration file (.conf)
     */
    public function generateClientConfig(VPNTunnel $tunnel): string
    {
        return <<<CONF
[Interface]
PrivateKey = {$tunnel->private_key_encrypted}
Address = {$tunnel->assigned_ip}
DNS = 1.1.1.1, 8.8.8.8

[Peer]
PublicKey = {$tunnel->public_key}
Endpoint = {$tunnel->server_endpoint}
AllowedIPs = 10.150.0.0/16
PersistentKeepalive = 25
CONF;
    }

    /**
     * Generate MikroTik RouterOS v7 WireGuard configuration command script
     */
    public function generateMikroTikScript(VPNTunnel $tunnel): string
    {
        return <<<RSC
# Smartoh WireGuard Tunnel Configuration for MikroTik RouterOS v7
/interface wireguard add name="wg-smartoh" listen-port=13231 mtu=1420 comment="Smartoh Management Tunnel"
/interface wireguard peers add interface="wg-smartoh" public-key="{$tunnel->public_key}" allowed-address="{$tunnel->assigned_ip}" endpoint-address="vpn.smartoh.net" endpoint-port=51820 persistent-keepalive=25
/ip address add address="{$tunnel->assigned_ip}" interface="wg-smartoh" network=10.150.0.0
RSC;
    }
}
