@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero / Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        Universal Device Intelligence Engine
                        <span class="px-2 py-0.5 text-[10px] font-mono uppercase bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-full">UDIE v2.0</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">Automated multi-vendor device discovery, progressive fingerprinting, parent-child correlation, and protocol management.</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('udie.unknown') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                <span>Unknown Lab ({{ $unknown }})</span>
            </a>

            <a href="{{ route('udie.models') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                <span>Device Model Catalog</span>
            </a>

            <form action="{{ route('udie.discover') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-600/30 flex items-center gap-2 transition">
                    <svg class="w-4 h-4 animate-spin-reverse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>Trigger Network Sweep</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Anomaly / Notice Alerts Bar (if any detected) -->
    @if($contradictions > 0 || $replacements > 0 || $topologyChanges > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @if($contradictions > 0)
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 flex items-center justify-between text-rose-300 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                <span><strong>{{ $contradictions }} Contradictory Evidence</strong> detected in network telemetry.</span>
            </div>
            <a href="{{ route('udie.index', ['role' => 'contradictions']) }}" class="underline font-semibold hover:text-white">Review</a>
        </div>
        @endif

        @if($replacements > 0)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 flex items-center justify-between text-amber-300 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span><strong>{{ $replacements }} Hardware Replacements</strong> detected on active circuits.</span>
            </div>
            <a href="{{ route('udie.index') }}" class="underline font-semibold hover:text-white">Audit</a>
        </div>
        @endif

        @if($topologyChanges > 0)
        <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-3 flex items-center justify-between text-purple-300 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                <span><strong>{{ $topologyChanges }} Topology Movements</strong> (parent ONT/Port relocations).</span>
            </div>
            <a href="{{ route('udie.index') }}" class="underline font-semibold hover:text-white">View</a>
        </div>
        @endif
    </div>
    @endif

    <!-- Section 43 Spec: Summary Operational Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-3">
        <!-- Total Discovered -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Discovered</div>
            <div class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($totalDevices) }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Scraped &amp; Ingested</div>
        </div>

        <!-- Online vs Offline -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Online / Offline</div>
            <div class="text-xl font-extrabold text-emerald-500 mt-1 flex items-baseline gap-1">
                {{ $online }}
                <span class="text-xs text-rose-500 font-semibold">/ {{ $offline }}</span>
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5">{{ $totalDevices > 0 ? round(($online / $totalDevices) * 100, 1) : 0 }}% Reachable</div>
        </div>

        <!-- Verified Models -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Verified Models</div>
            <div class="text-xl font-extrabold text-cyan-400 mt-1">{{ $verified }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Confidence ≥ 90%</div>
        </div>

        <!-- Unknown Devices -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Unknown Devices</div>
            <div class="text-xl font-extrabold text-rose-400 mt-1">{{ $unknown }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Needs Fingerprint</div>
        </div>

        <!-- Bridged ONTs -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Bridged ONTs</div>
            <div class="text-xl font-extrabold text-indigo-400 mt-1">{{ $bridgedOnts }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Rule 4 Separated</div>
        </div>

        <!-- Downstream Routers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Downstream Routers</div>
            <div class="text-xl font-extrabold text-purple-400 mt-1">{{ $downstreamRouters }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Independent CPEs</div>
        </div>

        <!-- TR-069 / TR-181 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">TR-069 / USP</div>
            <div class="text-xl font-extrabold text-emerald-400 mt-1">{{ $tr069Count + $tr181Count }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">CWMP Active</div>
        </div>

        <!-- MikroTik & WireGuard -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-sm">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">MikroTik / VPN</div>
            <div class="text-xl font-extrabold text-blue-400 mt-1">{{ $mikrotikCount + $vpnCount }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">API &amp; Secure Tunnels</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
        <form method="GET" action="{{ route('udie.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Search Keywords</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="IP, MAC, Serial, Hostname, Model..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- Device Type -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Device Type</label>
                <select name="device_type" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="">All Device Types</option>
                    <option value="router" {{ request('device_type') == 'router' ? 'selected' : '' }}>Router / Gateway</option>
                    <option value="ont" {{ request('device_type') == 'ont' ? 'selected' : '' }}>ONT / ONU</option>
                    <option value="olt" {{ request('device_type') == 'olt' ? 'selected' : '' }}>OLT Master</option>
                    <option value="switch" {{ request('device_type') == 'switch' ? 'selected' : '' }}>Switch</option>
                    <option value="ap" {{ request('device_type') == 'ap' ? 'selected' : '' }}>Access Point / Wi-Fi</option>
                    <option value="cpe" {{ request('device_type') == 'cpe' ? 'selected' : '' }}>Customer CPE</option>
                </select>
            </div>

            <!-- Identification Status -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Identification</label>
                <select name="ident_status" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="">All Identification Levels</option>
                    <option value="verified" {{ request('ident_status') == 'verified' ? 'selected' : '' }}>Verified (≥90%)</option>
                    <option value="high_confidence" {{ request('ident_status') == 'high_confidence' ? 'selected' : '' }}>High Confidence (75-89%)</option>
                    <option value="partially_identified" {{ request('ident_status') == 'partially_identified' ? 'selected' : '' }}>Partially Identified (50-74%)</option>
                    <option value="unknown" {{ request('ident_status') == 'unknown' ? 'selected' : '' }}>Unknown (&lt;50%)</option>
                </select>
            </div>

            <!-- Topology Role -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Topology Role</label>
                <select name="role" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="">All Topologies</option>
                    <option value="bridged_onts" {{ request('role') == 'bridged_onts' ? 'selected' : '' }}>Bridged ONTs (with Downstream)</option>
                    <option value="downstream_routers" {{ request('role') == 'downstream_routers' ? 'selected' : '' }}>Downstream Customer Routers</option>
                    <option value="parents" {{ request('role') == 'parents' ? 'selected' : '' }}>Parent / Hub Nodes</option>
                </select>
            </div>

            <!-- VRF Scope -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">VRF Routing Scope</label>
                <select name="vrf_id" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="">All VRFs / Default</option>
                    @foreach($vrfs as $vrf)
                        <option value="{{ $vrf->id }}" {{ request('vrf_id') == $vrf->id ? 'selected' : '' }}>{{ $vrf->name }} (RD: {{ $vrf->rd ?? 'Local' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons Row -->
            <div class="lg:col-span-6 flex items-center justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('udie.index') }}" class="px-3 py-1.5 rounded-xl text-xs text-slate-400 hover:text-slate-200 transition">Reset Filters</a>
                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/20 transition">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Device Inventory List -->
    <div class="space-y-3">
        @forelse($devices as $device)
            @php
                $confidence = (float) $device->confidence_score;
                $badgeBg = match(true) {
                    $confidence >= 90 => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                    $confidence >= 75 => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                    $confidence >= 50 => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                    default => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                };
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:border-blue-500/50 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                <!-- Left: Device Thumbnail + Identity Specs -->
                <div class="flex items-start sm:items-center gap-4 min-w-0">
                    <!-- Resolved Device Image -->
                    <div class="relative w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-800 p-2 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700/60 overflow-hidden">
                        <img src="{{ $device->resolved_thumbnail_url }}" alt="{{ $device->display_name }}" class="w-full h-full object-contain filter drop-shadow">
                        @if($device->status === 'online')
                            <span class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900" title="Online / Reachable"></span>
                        @else
                            <span class="absolute bottom-1 right-1 w-2.5 h-2.5 rounded-full bg-slate-500 ring-2 ring-white dark:ring-slate-900" title="Offline"></span>
                        @endif
                    </div>

                    <!-- Identity info -->
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('udie.show', $device) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-blue-500 transition truncate">
                                {{ $device->display_name }}
                            </a>
                            
                            <!-- Identification Status Badge -->
                            <span class="px-2 py-0.5 text-[10px] font-mono uppercase font-bold border rounded-full {{ $badgeBg }}">
                                {{ round($confidence, 1) }}% {{ $device->confidence_category }}
                            </span>

                            <!-- Device Type Tag -->
                            <span class="px-2 py-0.5 text-[10px] font-mono uppercase bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-300 rounded-md">
                                {{ strtoupper($device->device_type ?? 'DEVICE') }}
                            </span>

                            <!-- Rule 4 Parent/Child Markers -->
                            @if($device->childDevices->count() > 0)
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    PARENT ({{ $device->childDevices->count() }} Downstream)
                                </span>
                            @endif

                            @if($device->parentDevice)
                                <a href="{{ route('udie.show', $device->parentDevice) }}" class="px-2 py-0.5 text-[10px] font-semibold bg-purple-500/10 text-purple-400 border border-purple-500/30 rounded-full flex items-center gap-1 hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                                    BEHIND {{ $device->parentDevice->manufacturer }} ({{ $device->parentDevice->serial_number ?? $device->parentDevice->mac_address }})
                                </a>
                            @endif
                        </div>

                        <!-- Scoped IP & Hardware Address -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono">
                            <span class="text-slate-800 dark:text-slate-200 font-semibold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                                IP: {{ $device->ip_address ?? 'Unassigned' }}
                            </span>
                            <span>MAC: {{ $device->mac_address ?? 'N/A' }}</span>
                            @if($device->serial_number)
                                <span>SN: {{ $device->serial_number }}</span>
                            @endif
                            @if($device->vrf)
                                <span class="text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded text-[10px] font-sans">VRF: {{ $device->vrf->name }}</span>
                            @endif
                            @if($device->vlan_id)
                                <span class="text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded text-[10px] font-sans">VLAN {{ $device->vlan_id }}</span>
                            @endif
                        </div>

                        <!-- Customer & Site correlation -->
                        @if($device->customer || $device->site)
                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                            @if($device->customer)
                                <span>Customer: <strong class="text-slate-700 dark:text-slate-300">{{ $device->customer->name }}</strong></span>
                            @endif
                            @if($device->site)
                                <span>• Site: <strong class="text-slate-700 dark:text-slate-300">{{ $device->site->name }}</strong></span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Management Protocol Matrix & Action Buttons -->
                <div class="flex flex-wrap items-center justify-between lg:justify-end gap-3 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
                    <!-- Primary / Fallback Management Path -->
                    <div class="text-right hidden sm:block">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Management Path</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if($device->primary_management_protocol)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">
                                    {{ strtoupper(str_replace('_', ' ', $device->primary_management_protocol)) }}
                                </span>
                            @else
                                <span class="text-[11px] text-slate-500">Unenrolled</span>
                            @endif

                            @if($device->fallback_management_protocol)
                                <span class="text-slate-500 text-[10px]">↳</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono text-slate-400 bg-slate-800 border border-slate-700">
                                    {{ strtoupper(str_replace('_', ' ', $device->fallback_management_protocol)) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('udie.show', $device) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5">
                            <span>Inspect</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">No Discovered Devices Found</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    No network devices matched your current filter criteria or the discovery engine hasn't initiated a sweep yet.
                </p>
                <div class="mt-4">
                    <form action="{{ route('udie.discover') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-blue-600/30 transition">
                            Run Initial Network Discovery Sweep
                        </button>
                    </form>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $devices->links() }}
    </div>

</div>
@endsection
