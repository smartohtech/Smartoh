@extends('layouts.app')

@section('content')
<div x-data="{ wgModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Universal CPE, TR-069 ACS & OLT Provisioning</h1>
            <p class="text-xs text-slate-400 mt-0.5">Hardware-agnostic management engine: CWMP, TR-181 USP, GPON ONTs, and WireGuard remote tunnels.</p>
        </div>
        <button @click="wgModal = true" class="px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Provision WireGuard Peer</span>
        </button>
    </div>

    <!-- Management Path Abstraction Counters -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach([
            ['label' => 'TR-069 CWMP', 'count' => $pathCounts['tr069'], 'color' => 'cyan'],
            ['label' => 'TR-181 USP', 'count' => $pathCounts['tr181_usp'], 'color' => 'blue'],
            ['label' => 'WireGuard VPN', 'count' => $pathCounts['wireguard'], 'color' => 'purple'],
            ['label' => 'Smart OLT API', 'count' => $pathCounts['olt_api'], 'color' => 'emerald'],
            ['label' => 'LAN Direct', 'count' => $pathCounts['lan_direct'], 'color' => 'amber'],
            ['label' => 'Manual Profile', 'count' => $pathCounts['manual'], 'color' => 'slate'],
        ] as $item)
        <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-3 text-center">
            <div class="text-[10px] uppercase font-mono font-bold text-slate-400 truncate">{{ $item['label'] }}</div>
            <div class="text-xl font-bold text-white font-mono mt-1">{{ $item['count'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Section 1: GPON OLT & Optical Power ONTs -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">GPON Optical Line Terminals (OLT) & Connected ONUs</h3>
                <p class="text-xs text-slate-400">Huawei MA5800 / ZTE / VSOL / SmartOLT adapter integration</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono">OPTICAL LINK MONITOR</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider">
                        <th class="p-4">ONT Serial Number</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">OLT & PON Port</th>
                        <th class="p-4">Optical RX Power</th>
                        <th class="p-4">Signal Quality</th>
                        <th class="p-4">WAN Mode</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($onts as $ont)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-4 font-bold text-cyan-400">{{ $ont->serial_number }}</td>
                        <td class="p-4 font-sans font-medium text-white">{{ $ont->client?->name ?? 'Unassigned' }}</td>
                        <td class="p-4 text-slate-300">{{ $ont->olt?->name }} (Port {{ $ont->pon_port }})</td>
                        <td class="p-4 font-bold text-white">{{ $ont->rx_power_dbm }} dBm</td>
                        <td class="p-4">
                            @if($ont->rx_power_dbm >= -24.0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">GOOD (-15 to -24 dBm)</span>
                            @elseif($ont->rx_power_dbm >= -27.0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">WARNING (-25 to -27 dBm)</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">CRITICAL LOS (&lt; -27 dBm)</span>
                            @endif
                        </td>
                        <td class="p-4 uppercase text-slate-400">{{ $ont->wan_mode }} (VLAN {{ $ont->vlan_id }})</td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('cpe.ont.test', $ont) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-cyan-400 rounded-lg border border-slate-700 transition">
                                    Test Optical Power
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-8 text-center text-slate-500 font-sans">No ONTs enrolled.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Universal CPEs & TR-069 ACS Inform Log -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">TR-069 / TR-181 Auto Configuration Server (ACS) Devices</h3>
                <p class="text-xs text-slate-400">Zero-touch provisioning, periodic informs, and parameter configuration</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/30 font-mono">ACS PORT: 7547</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Serial / Model</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Management Path</th>
                        <th class="p-4">Wi-Fi SSID & Key</th>
                        <th class="p-4">Last Inform</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($routers as $r)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $r->serial_number }}</div>
                            <div class="text-[10px] text-slate-400">{{ $r->manufacturer }} • {{ $r->model }}</div>
                        </td>
                        <td class="p-4 font-sans font-medium text-white">{{ $r->client?->name ?? 'Unassigned' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 uppercase">
                                {{ $r->preferred_management_path }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="text-white">{{ $r->wifi_ssid ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-500">Key: {{ $r->wifi_password ?? 'N/A' }}</div>
                        </td>
                        <td class="p-4 text-slate-400 text-[11px]">{{ $r->last_inform_at ? $r->last_inform_at->diffForHumans() : 'Never' }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">ONLINE</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-500 font-sans">No CPE routers synced to ACS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: WireGuard VPN Remote Tunnels -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">WireGuard Remote Management Tunnels</h3>
                <p class="text-xs text-slate-400">Encrypted peer-to-peer overlay tunnels for non-TR-069 equipment</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 font-mono">PORT 51820</span>
        </div>
        <div class="divide-y divide-slate-800/60 p-4 font-mono text-xs space-y-3">
            @forelse($vpnTunnels as $tun)
            <div class="flex items-center justify-between p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                <div>
                    <div class="font-bold text-white flex items-center space-x-2">
                        <span>{{ $tun->tunnel_name }}</span>
                        <span class="text-cyan-400">({{ $tun->assigned_ip }})</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 truncate max-w-lg">Public Key: {{ $tun->public_key }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Endpoint: {{ $tun->server_endpoint }} • Last Handshake: {{ $tun->last_handshake_at ? $tun->last_handshake_at->diffForHumans() : 'Never' }}</div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">TUNNEL UP</span>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-slate-500 font-sans">No WireGuard tunnels active.</div>
            @endforelse
        </div>
    </div>

    <!-- WireGuard Modal -->
    <div x-show="wgModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="wgModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Provision WireGuard Peer</h3>
                <button @click="wgModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('cpe.wireguard.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Tunnel Name *</label>
                    <input type="text" name="tunnel_name" placeholder="e.g. wg-cpe-branch01" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Assigned Overlay IP *</label>
                    <input type="text" name="assigned_ip" value="10.150.0.15/32" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl text-slate-400 text-[11px]">
                    Private & Public Curve25519 cryptographic keypairs will be automatically generated and encrypted at rest.
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="wgModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Generate & Provision
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
