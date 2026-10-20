@extends('layouts.app')

@section('content')
<div x-data="{ addModal: false, importModal: false, restoreModal: false, exportMenu: false, importTab: 'csv' }" class="space-y-6">

    <!-- Header & Fast Migration Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Subscribers &amp; CRM</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Manage &amp; migrate PPPoE, Hotspot, and Static IP subscribers with instant MikroTik connection restoration.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Export Clients Dropdown / Direct Buttons -->
            <div class="relative" @click.away="exportMenu = false">
                <button type="button" @click="exportMenu = !exportMenu" class="px-3.5 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl shadow-sm flex items-center space-x-2 transition">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export Clients</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="exportMenu" x-cloak class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-40 py-1.5 text-xs">
                    <a href="{{ route('clients.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="flex items-start space-x-2.5 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">CSV</span>
                        <div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">Export Subscribers (.CSV)</div>
                            <div class="text-[11px] text-slate-400">All Static IP, PPPoE &amp; Hotspot records</div>
                        </div>
                    </a>
                    <a href="{{ route('clients.export', array_merge(request()->query(), ['format' => 'rsc'])) }}" class="flex items-start space-x-2.5 px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-blue-600 dark:text-blue-400 font-bold mt-0.5">RSC</span>
                        <div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">Export MikroTik Script (.RSC)</div>
                            <div class="text-[11px] text-slate-400">Ready-to-paste RouterOS migration script</div>
                        </div>
                    </a>
                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                    <a href="{{ route('clients.export', ['format' => 'template']) }}" class="flex items-start space-x-2.5 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-amber-600 dark:text-amber-400 font-bold mt-0.5">TMP</span>
                        <div>
                            <div class="font-semibold text-slate-700 dark:text-slate-300">Download Sample CSV Template</div>
                            <div class="text-[11px] text-slate-400">Pre-formatted for fast bulk import</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Import & Migrate Clients Button -->
            <button type="button" @click="importModal = true" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-600/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import &amp; Migrate Clients</span>
            </button>

            <!-- 1-Click Restore Connections Button -->
            <button type="button" @click="restoreModal = true" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Restore Connections</span>
            </button>

            <!-- Register Single Subscriber Button -->
            <button type="button" @click="addModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Register Subscriber</span>
            </button>
        </div>
    </div>

    <!-- Service Type Breakdown & Connection Status Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('clients.index', array_merge(request()->query(), ['service_type' => request('service_type') === 'pppoe' ? null : 'pppoe'])) }}" class="p-3.5 rounded-2xl border transition {{ request('service_type') === 'pppoe' ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-300 dark:border-blue-700' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-blue-300' }}">
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">PPPoE Clients</div>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($serviceCounts['pppoe'] ?? 0) }}</div>
            <div class="text-[10px] text-blue-600 dark:text-blue-400 font-mono mt-0.5">/ppp secret profiles</div>
        </a>

        <a href="{{ route('clients.index', array_merge(request()->query(), ['service_type' => request('service_type') === 'hotspot' ? null : 'hotspot'])) }}" class="p-3.5 rounded-2xl border transition {{ request('service_type') === 'hotspot' ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-amber-300' }}">
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Hotspot Clients</div>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($serviceCounts['hotspot'] ?? 0) }}</div>
            <div class="text-[10px] text-amber-600 dark:text-amber-400 font-mono mt-0.5">/ip hotspot users</div>
        </a>

        <a href="{{ route('clients.index', array_merge(request()->query(), ['service_type' => request('service_type') === 'static' ? null : 'static'])) }}" class="p-3.5 rounded-2xl border transition {{ request('service_type') === 'static' ? 'bg-purple-50/80 dark:bg-purple-950/40 border-purple-300 dark:border-purple-700' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-purple-300' }}">
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Static IP Clients</div>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($serviceCounts['static'] ?? 0) }}</div>
            <div class="text-[10px] text-purple-600 dark:text-purple-400 font-mono mt-0.5">/queue simple &amp; ARP</div>
        </a>

        <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Verified Connected</div>
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($serviceCounts['connected_verified'] ?? 0) }}</div>
            <div class="text-[10px] text-slate-400 font-mono mt-0.5">Synced on MikroTik</div>
        </div>
    </div>

    <!-- Filter Pills & Search -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
            @foreach([
                'all' => 'All (' . $statusCounts['all'] . ')',
                'active' => 'Active (' . $statusCounts['active'] . ')',
                'suspended' => 'Suspended (' . $statusCounts['suspended'] . ')',
                'expiring_soon' => 'Expiring (' . $statusCounts['expired'] . ')',
                'fup_throttled' => 'FUP Throttled (' . $statusCounts['fup_throttled'] . ')',
                'overdue' => 'Overdue (' . $statusCounts['overdue'] . ')',
            ] as $key => $label)
                @php $isSelected = request('status', 'all') === $key || ($key === 'all' && !request('status')); @endphp
                <a href="{{ route('clients.index', array_merge(request()->query(), ['status' => $key === 'all' ? null : $key])) }}" class="px-3 py-1.5 text-xs rounded-lg font-medium transition {{ $isSelected ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-bold border border-blue-200 dark:border-blue-800' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('clients.index') }}" class="w-full md:w-80">
            @if(request('service_type'))
                <input type="hidden" name="service_type" value="{{ request('service_type') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, IP, PPPoE user..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </form>

    </div>

    <!-- Subscribers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Service &amp; Plan</th>
                        <th class="p-4">Credentials / IP</th>
                        <th class="p-4">Router &amp; Status</th>
                        <th class="p-4">Cycle / Expiry</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($clients as $client)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        
                        <!-- Client Info -->
                        <td class="p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $client->name }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $client->account_number }} • {{ $client->phone }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Plan & Service Type Badge -->
                        <td class="p-4">
                            <div class="flex items-center space-x-1.5">
                                @php
                                    $typeBadge = match(strtolower($client->service_type ?? 'pppoe')) {
                                        'hotspot' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                        'static', 'dhcp' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                        default => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                    };
                                @endphp
                                <span class="px-1.5 py-0.5 text-[10px] font-mono uppercase rounded border {{ $typeBadge }}">
                                    {{ $client->service_type === 'static' ? 'STATIC IP' : strtoupper($client->service_type ?? 'PPPOE') }}
                                </span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $client->billingPlan?->name ?? 'Custom Plan' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                {{ $client->billingPlan ? round($client->billingPlan->download_speed_kbps / 1024) . 'M / ' . round($client->billingPlan->upload_speed_kbps / 1024) . 'M' : 'Unlimited' }}
                            </div>
                        </td>

                        <!-- PPPoE / IP -->
                        <td class="p-4 font-mono text-xs">
                            <div class="text-blue-600 dark:text-blue-400">{{ $client->pppoe_username ?? 'static-cpe' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $client->assigned_ip ?? 'Dynamic IP' }} @if($client->mac_address) • {{ $client->mac_address }} @endif</div>
                        </td>

                        <!-- Status Pill & Router -->
                        <td class="p-4">
                            @php
                                $statusClasses = [
                                    'active' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    'suspended' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                    'expired' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                    'fup_throttled' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                ][$client->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <div class="flex flex-col items-start gap-1">
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border {{ $statusClasses }}">
                                    @if($client->status === 'active')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    @endif
                                    <span>{{ str_replace('_', ' ', $client->status) }}</span>
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">
                                    {{ $client->mikrotikDevice?->name ?? 'Unassigned Router' }}
                                    @if($client->connection_status === 'connected_verified')
                                        • <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Connected</span>
                                    @elseif($client->connection_status === 'activation_retrying')
                                        • <span class="text-amber-600 dark:text-amber-400">Retrying</span>
                                    @endif
                                </span>
                            </div>
                        </td>

                        <!-- Cycle / Expiry -->
                        <td class="p-4 font-mono text-xs">
                            @if($client->current_cycle_end)
                                <div class="text-slate-800 dark:text-slate-200">{{ $client->current_cycle_end->format('M d, Y') }}</div>
                                <div class="text-[11px] {{ $client->current_cycle_end->isPast() ? 'text-rose-500' : 'text-slate-400' }}">
                                    {{ $client->current_cycle_end->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('clients.show', $client) }}" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>

                                <form method="POST" action="{{ route('clients.toggle-status', $client) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg transition {{ $client->status === 'active' ? 'text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}" title="{{ $client->status === 'active' ? 'Disconnect / Suspend' : 'Reconnect' }}">
                                        @if($client->status === 'active')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            No subscribers found matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $clients->links() }}
        </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 1. FAST CLIENT IMPORT & MIKROTIK MIGRATION MODAL                          -->
    <!-- ========================================================================= -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div @click.away="importModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Self-Service Client Migration &amp; MikroTik Import</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Import Static IP, PPPoE &amp; Hotspot clients and restore their router connections in seconds.</p>
                </div>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">&times;</button>
            </div>

            <!-- Mode Selector Tabs -->
            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-950 rounded-xl text-xs font-semibold">
                <button type="button" @click="importTab = 'csv'" :class="importTab === 'csv' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400'" class="py-2 px-3 rounded-lg transition text-center">
                    1. Bulk CSV / Spreadsheet Import
                </button>
                <button type="button" @click="importTab = 'router_pull'" :class="importTab === 'router_pull' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400'" class="py-2 px-3 rounded-lg transition text-center">
                    2. Pull from New MikroTik / .RSC Script
                </button>
            </div>

            <form method="POST" action="{{ route('clients.import') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="import_mode" :value="importTab">

                <!-- TAB 1: CSV Upload or Paste -->
                <div x-show="importTab === 'csv'" class="space-y-3">
                    <div class="flex items-center justify-between bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60 rounded-xl p-3">
                        <div class="text-[11px] text-slate-700 dark:text-slate-300">
                            Supports <span class="font-mono font-semibold">pppoe</span>, <span class="font-mono font-semibold">hotspot</span>, and <span class="font-mono font-semibold">static</span> clients in a single file.
                        </div>
                        <a href="{{ route('clients.export', ['format' => 'template']) }}" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold text-[11px] shrink-0">
                            Download CSV Template
                        </a>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Upload Clients CSV File</label>
                        <input type="file" name="clients_file" accept=".csv,.txt" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Or Paste CSV Rows Directly (Fast Copy-Paste Migration)</label>
                        <textarea name="csv_payload" rows="4" placeholder="account_number,name,phone,email,service_type,pppoe_username,pppoe_password,assigned_ip,mac_address,plan_code,status&#10;CLI-101,John Kamau,254712345678,john@isp.co.ke,pppoe,john_pppoe,Pass123,10.200.1.10,,HF-10M,active&#10;CLI-102,Mary Hotspot,254722111222,,hotspot,mary_wifi,Wifi123,10.100.1.20,48:8F:5A:11:22:33,HF-10M,active&#10;CLI-103,Apex Static CPE,254733444555,,static,apex_cpe,Static123,192.168.88.50,48:8F:5A:44:55:66,BIZ-50M,active" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono text-[11px]"></textarea>
                    </div>
                </div>

                <!-- TAB 2: Pull from MikroTik Router or RouterOS .RSC Export -->
                <div x-show="importTab === 'router_pull'" class="space-y-3">
                    <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl p-3 text-[11px] text-slate-700 dark:text-slate-300">
                        Integrating an existing MikroTik router? Select the router below to auto-discover its <span class="font-mono font-semibold">/ppp secret</span> (PPPoE), <span class="font-mono font-semibold">/ip hotspot user</span> (Hotspot), and <span class="font-mono font-semibold">/queue simple</span> (Static IP) clients, or upload/paste a RouterOS <span class="font-mono font-semibold">.rsc</span> export.
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Upload RouterOS Export File (.rsc) (Optional if pulling live from router)</label>
                        <input type="file" name="rsc_file" accept=".rsc,.txt" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Or Paste RouterOS Export Commands (<span class="font-mono">/ppp secret</span>, <span class="font-mono">/ip hotspot user</span>, <span class="font-mono">/queue simple</span>)</label>
                        <textarea name="rsc_payload" rows="4" placeholder="/ppp secret&#10;add name=&quot;client_pppoe1&quot; password=&quot;123456&quot; profile=&quot;plan_10M&quot; remote-address=&quot;10.200.1.55&quot;&#10;/ip hotspot user&#10;add name=&quot;hotspot_voucher1&quot; password=&quot;abcd&quot; profile=&quot;plan_10M&quot;&#10;/queue simple&#10;add name=&quot;Static_Office&quot; target=&quot;192.168.88.120/32&quot; max-limit=&quot;25600k/25600k&quot;" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono text-[11px]"></textarea>
                    </div>
                </div>

                <!-- Target MikroTik, Fallback Billing Plan, Site & Duplicate Handling -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Target MikroTik Router</label>
                        <select name="mikrotik_device_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            @forelse($routers ?? [] as $router)
                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }}) {{ $router->is_online ? '• Online' : '• Offline' }}</option>
                            @empty
                                <option value="">Auto-select Online Router</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Default Billing Plan</label>
                        <select name="default_billing_plan_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">If Client Already Exists</label>
                        <select name="duplicate_mode" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="update">Update &amp; Sync Credentials</option>
                            <option value="skip">Skip Existing Clients</option>
                        </select>
                    </div>
                </div>

                <label class="flex items-center space-x-2.5 p-3 bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl cursor-pointer">
                    <input type="checkbox" name="restore_connections" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        Immediately provision &amp; restore client connections on the MikroTik router (PPPoE secrets, Hotspot users &amp; Static IP queues)
                    </span>
                </label>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="importModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-md shadow-indigo-600/20">
                        Import &amp; Restore Connections Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. 1-CLICK BULK RESTORE CLIENT CONNECTIONS MODAL                          -->
    <!-- ========================================================================= -->
    <div x-show="restoreModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="restoreModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Restore Client Connections on MikroTik</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Re-provision PPPoE, Hotspot, and Static IP clients onto a new or replacement MikroTik router in one pass.</p>
                </div>
                <button type="button" @click="restoreModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('clients.restore-connections') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Target MikroTik Router *</label>
                    <select name="mikrotik_device_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                        @forelse($routers ?? [] as $router)
                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }}) {{ $router->is_online ? '• Online' : '• Offline' }}</option>
                        @empty
                            <option value="">First Available Router</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Client Connection Types to Restore *</label>
                    <select name="service_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                        <option value="all">All Clients (Static IP + PPPoE + Hotspot)</option>
                        <option value="pppoe">PPPoE Clients Only (/ppp secret)</option>
                        <option value="hotspot">Hotspot Clients Only (/ip hotspot user)</option>
                        <option value="static">Static IP Clients Only (/queue simple &amp; ARP)</option>
                    </select>
                </div>

                <label class="flex items-center space-x-2.5 p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl cursor-pointer">
                    <input type="checkbox" name="reactivate_suspended" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-slate-700 dark:text-slate-300">
                        Also mark suspended/migrating clients as Active during cutover
                    </span>
                </label>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="restoreModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-md shadow-emerald-600/20">
                        Restore Connections Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. REGISTER SINGLE SUBSCRIBER MODAL                                       -->
    <!-- ========================================================================= -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Register New Subscriber</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('clients.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                        <input type="text" name="first_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number (M-Pesa) *</label>
                        <input type="text" name="phone" placeholder="254712345678" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Billing Plan *</label>
                        <select name="billing_plan_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} (KES {{ number_format($plan->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="pppoe">PPPoE Fiber / Wireless</option>
                            <option value="hotspot">Captive Portal Hotspot</option>
                            <option value="static">Dedicated Static IP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">MikroTik Router</label>
                        <select name="mikrotik_device_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            @forelse($routers ?? [] as $router)
                                <option value="{{ $router->id }}">{{ $router->name }}</option>
                            @empty
                                <option value="">Default Online Router</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Username (PPPoE / Hotspot)</label>
                        <input type="text" name="pppoe_username" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Password</label>
                        <input type="password" name="pppoe_password" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Assigned Static IP</label>
                        <input type="text" name="assigned_ip" placeholder="192.168.88.50" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Physical Installation Address</label>
                    <input type="text" name="address" placeholder="Estate / House No / Road" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Enroll Subscriber
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
