@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">

    <!-- Back Navigation & Top Summary Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('udie.index') }}" class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ $device->display_name }}
                    </h1>
                    <span class="px-2.5 py-0.5 text-xs font-mono font-bold rounded-full border {{ match(true) {
                        (float)$device->confidence_score >= 90 => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                        (float)$device->confidence_score >= 75 => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                        (float)$device->confidence_score >= 50 => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                        default => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                    } }}">
                        {{ round($device->confidence_score, 1) }}% {{ $device->confidence_category }}
                    </span>
                    @if($device->status === 'online')
                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/30 rounded-full">
                            OFFLINE
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 font-mono">
                    <span>IP: {{ $device->ip_address ?? 'Unassigned' }}</span>
                    <span>•</span>
                    <span>MAC: {{ $device->mac_address ?? 'N/A' }}</span>
                    @if($device->vrf)
                        <span>•</span>
                        <span class="text-indigo-400">VRF: {{ $device->vrf->name }}</span>
                    @endif
                    @if($device->vlan_id)
                        <span>•</span>
                        <span class="text-amber-400">VLAN {{ $device->vlan_id }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('udie.reidentify', $device) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/20 flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>Re-Run Identification</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Rule 4 Notice if Bridged ONT or Downstream Router -->
    @if($device->isBridgedOnt() || $device->parent_device_id)
    <div class="p-4 bg-indigo-500/10 border border-indigo-500/30 rounded-2xl flex items-center justify-between text-indigo-300 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                @if($device->isBridgedOnt())
                    <strong>Architectural Rule 4 Enforced:</strong> This device is operating as a <strong>Bridged Optical Network Terminal (ONT)</strong>. Customer router(s) downstream are maintained as separate independent instances and do not inherit this ONT's model identity.
                @else
                    <strong>Architectural Rule 4 Enforced:</strong> This device is a <strong>Downstream Customer Router</strong> located behind Bridged ONT <strong>{{ $device->parentDevice->display_name }}</strong>. It retains its independent identity ({{ $device->display_name }}).
                @endif
            </div>
        </div>
        @if($device->parentDevice)
            <a href="{{ route('udie.show', $device->parentDevice) }}" class="underline font-semibold hover:text-white shrink-0 ml-2">Inspect Parent ONT</a>
        @endif
    </div>
    @endif

    <!-- Navigation Tabs Header -->
    <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 overflow-x-auto text-xs font-semibold">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Overview</span>
        </button>
        <button @click="activeTab = 'identity'" :class="activeTab === 'identity' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
            <span>Identity &amp; Catalog</span>
        </button>
        <button @click="activeTab = 'evidence'" :class="activeTab === 'evidence' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span>Evidence Graph ({{ $device->evidences->count() }})</span>
        </button>
        <button @click="activeTab = 'topology'" :class="activeTab === 'topology' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            <span>Topology &amp; Correlation</span>
        </button>
        <button @click="activeTab = 'management'" :class="activeTab === 'management' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span>Management &amp; Protocols</span>
        </button>
        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'" class="pb-3 px-3 transition whitespace-nowrap flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Audit &amp; Events ({{ $device->events->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Card: Resolved High-Res Device Image & Confidence -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-48 h-48 rounded-2xl bg-slate-50 dark:bg-slate-800/80 p-4 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center shadow-inner relative group">
                    <img src="{{ $resolvedImage }}" alt="{{ $device->display_name }}" class="max-h-full max-w-full object-contain filter drop-shadow-md group-hover:scale-105 transition-transform duration-300">
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white mt-4">{{ $device->display_name }}</h3>
                <p class="text-xs text-slate-500 font-mono">{{ $device->manufacturer ?? 'Vendor Unknown' }} • {{ $device->model ?? 'Model Unknown' }}</p>

                <!-- Confidence Gauge -->
                <div class="w-full mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between text-xs font-semibold text-slate-500 mb-1">
                        <span>Identity Confidence</span>
                        <span class="text-slate-900 dark:text-white font-mono">{{ round($device->confidence_score, 1) }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ match(true) {
                            (float)$device->confidence_score >= 90 => 'bg-emerald-500',
                            (float)$device->confidence_score >= 75 => 'bg-cyan-500',
                            (float)$device->confidence_score >= 50 => 'bg-amber-500',
                            default => 'bg-rose-500',
                        } }}" style="width: {{ min(100, (float)$device->confidence_score) }}%"></div>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-2 text-left">
                        Status: <strong class="text-slate-800 dark:text-slate-200 uppercase">{{ $device->identification_status }}</strong>
                    </div>
                </div>
            </div>

            <!-- Right: Technical Specification & Scoped Identity -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Hardware &amp; Network Scope</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">IP Address &amp; Family</span>
                            <span class="font-mono text-slate-900 dark:text-white font-semibold text-sm">{{ $device->ip_address ?? 'None Assigned' }}</span>
                            @if(!empty($device->ipv6_addresses))
                                <div class="text-[10px] font-mono text-slate-400 mt-1 truncate">{{ implode(', ', $device->ipv6_addresses) }}</div>
                            @endif
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Hardware MAC Address</span>
                            <span class="font-mono text-slate-900 dark:text-white font-semibold text-sm">{{ $device->mac_address ?? 'N/A' }}</span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Serial Number</span>
                            <span class="font-mono text-slate-900 dark:text-white">{{ $device->serial_number ?? 'Not Reported' }}</span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Hostname / FQDN</span>
                            <span class="font-mono text-slate-900 dark:text-white">{{ $device->hostname ?? 'None' }}</span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">VRF Routing Scope (Rule 5)</span>
                            <span class="font-semibold text-indigo-400">{{ $device->vrf?->name ?? 'Default Global VRF' }}</span>
                            <span class="text-[10px] text-slate-500 block">RD: {{ $device->vrf?->rd ?? 'Local' }}</span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">VLAN / Interface Segment</span>
                            <span class="font-semibold text-amber-400">VLAN {{ $device->vlan_id ?? 'Untagged' }}</span>
                            <span class="text-[10px] text-slate-500 block">Interface: {{ $device->interface ?? 'Unspecified' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer, Site, and Optical Association -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Service &amp; Customer Link</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Customer Account</span>
                            @if($device->customer)
                                <a href="{{ route('clients.show', $device->customer) }}" class="font-semibold text-blue-500 hover:underline">{{ $device->customer->name }}</a>
                                <span class="text-[10px] text-slate-500 block font-mono">{{ $device->customer->account_number }}</span>
                            @else
                                <span class="text-slate-400">Unassigned Customer</span>
                            @endif
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Site / POP</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $device->site?->name ?? 'Main POP' }}</span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">OLT / PON / ONU ID</span>
                            <span class="font-mono text-purple-400">{{ $device->olt?->name ?? ($device->olt_id ? "OLT #{$device->olt_id}" : 'Non-PON') }}</span>
                            @if($device->pon_id)
                                <span class="text-[10px] text-slate-500 block font-mono">PON: {{ $device->pon_id }} | ONU: {{ $device->onu_id ?? 'N/A' }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between text-[11px] text-slate-500 font-mono">
                    <span>First Ingested: {{ $device->first_seen?->diffForHumans() ?? 'Unknown' }}</span>
                    <span>Last Telemetry: {{ $device->last_seen?->diffForHumans() ?? 'Never' }}</span>
                    <span>Last Identification: {{ $device->last_identified_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
            </div>

        </div>
    </div>

    <!-- TAB 2: IDENTITY & CATALOG -->
    <div x-show="activeTab === 'identity'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Identification Specifications Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Device Identification Details</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Manufacturer (Vendor)</span>
                        <strong class="text-slate-900 dark:text-white">{{ $device->manufacturer ?? 'Unidentified' }}</strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Model Name / Designation</span>
                        <strong class="text-slate-900 dark:text-white">
                            @if($device->confidence_score < 75 && $device->identification_status === 'unknown')
                                <span class="text-rose-400">Model Not Verified (Rule 45)</span>
                            @else
                                {{ $device->model ?? 'Model Not Verified' }}
                            @endif
                        </strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Product Class</span>
                        <strong class="text-slate-900 dark:text-white">{{ $device->product_class ?? 'N/A' }}</strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Hardware Revision</span>
                        <strong class="text-slate-900 dark:text-white">{{ $device->hardware_version ?? 'N/A' }}</strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Firmware / Software Version</span>
                        <strong class="text-slate-900 dark:text-white font-mono">{{ $device->firmware_version ?? 'N/A' }}</strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Deterministic Fingerprint Hash</span>
                        <span class="text-[10px] font-mono text-cyan-400 truncate max-w-[200px]" title="{{ $device->fingerprint_hash }}">{{ $device->fingerprint_hash ?? 'Pending' }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Global Catalog Association</span>
                        @if($device->deviceModel)
                            <span class="text-emerald-400 font-semibold">{{ $device->deviceModel->manufacturer }} {{ $device->deviceModel->model }}</span>
                        @else
                            <span class="text-slate-500">None</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-400">Tenant Override Association</span>
                        @if($device->tenantDeviceModel)
                            <span class="text-blue-400 font-semibold">{{ $device->tenantDeviceModel->custom_name }}</span>
                        @else
                            <span class="text-slate-500">Default Catalog Image</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Manual Identification / Model Override Form -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Manual Model Verification / Override</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Override identification confidence or create a custom model definition for your tenant catalog.
                    </p>
                </div>

                <form action="{{ route('udie.devices.identify', $device) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Manufacturer (Vendor)</label>
                        <input type="text" name="manufacturer" value="{{ old('manufacturer', $device->manufacturer) }}" placeholder="e.g. Huawei, ZTE, MikroTik, TP-Link" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Model Name</label>
                        <input type="text" name="model" value="{{ old('model', $device->model) }}" placeholder="e.g. HG8145H, RB4011iGS+, Archer C6" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Device Type</label>
                            <select name="device_type" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="router" {{ $device->device_type === 'router' ? 'selected' : '' }}>Router / Gateway</option>
                                <option value="ont" {{ in_array($device->device_type, ['ont', 'onu']) ? 'selected' : '' }}>ONT / ONU</option>
                                <option value="olt" {{ $device->device_type === 'olt' ? 'selected' : '' }}>OLT Master</option>
                                <option value="switch" {{ $device->device_type === 'switch' ? 'selected' : '' }}>Switch</option>
                                <option value="ap" {{ $device->device_type === 'ap' ? 'selected' : '' }}>Access Point</option>
                                <option value="cpe" {{ $device->device_type === 'cpe' ? 'selected' : '' }}>Customer CPE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Catalog Scope</label>
                            <select name="catalog_target" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="tenant">Tenant Private Catalog</option>
                                <option value="global">Global Catalog</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Custom Display Name (Optional)</label>
                        <input type="text" name="custom_name" value="{{ old('custom_name') }}" placeholder="e.g. VIP Fiber Gateway Pro" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold shadow-md shadow-emerald-600/20 transition">
                        Verify &amp; Apply Model Identity
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- TAB 3: EVIDENCE GRAPH -->
    <div x-show="activeTab === 'evidence'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Multi-Source Telemetry Evidence</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Every attribute observation contributes weighted confidence or contradictory penalties.</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-mono">
                    <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Supporting
                    </span>
                    <span class="flex items-center gap-1.5 text-rose-400 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Contradictory
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase font-mono text-[10px]">
                            <th class="pb-2">Source</th>
                            <th class="pb-2">Observed Attribute</th>
                            <th class="pb-2">Attribute Value</th>
                            <th class="pb-2">Network Scope</th>
                            <th class="pb-2">Contribution</th>
                            <th class="pb-2">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                        @forelse($device->evidences as $evidence)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $evidence->source }}
                                    </span>
                                </td>
                                <td class="py-2.5 font-sans font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $evidence->attribute_name }}
                                </td>
                                <td class="py-2.5 text-slate-900 dark:text-white">
                                    {{ $evidence->attribute_value }}
                                </td>
                                <td class="py-2.5 text-slate-400 text-[11px]">
                                    @if(!empty($evidence->evidence_scope))
                                        {{ json_encode($evidence->evidence_scope) }}
                                    @else
                                        Global
                                    @endif
                                </td>
                                <td class="py-2.5">
                                    @if($evidence->is_contradictory)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                            -{{ (float)$evidence->confidence_weight }} (Penalty)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            +{{ (float)$evidence->confidence_weight }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-slate-500 text-[11px]">
                                    {{ $evidence->observed_at?->diffForHumans() ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No telemetry evidence records available for this device.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: TOPOLOGY & CORRELATION -->
    <div x-show="activeTab === 'topology'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">Network Topology Tree &amp; Parent-Child Hierarchy</h3>
            <p class="text-xs text-slate-500 mb-6">Strict separation of Bridged ONTs and downstream customer routers (Rule 4).</p>

            <div class="flex flex-col items-center max-w-2xl mx-auto space-y-4">
                
                <!-- Upstream OLT / Uplink (if present) -->
                @if($device->olt || $device->olt_id)
                <div class="w-full max-w-md p-4 bg-slate-800/80 border border-purple-500/40 rounded-2xl text-center shadow-lg">
                    <span class="text-[10px] uppercase font-bold text-purple-400 tracking-wider">Upstream OLT Chassis</span>
                    <h4 class="text-sm font-bold text-white mt-1">{{ $device->olt?->name ?? "OLT Master Chassis (#{$device->olt_id})" }}</h4>
                    <span class="text-xs text-slate-400 font-mono">PON Port: {{ $device->pon_id ?? '1' }}</span>
                </div>
                <div class="w-0.5 h-6 bg-slate-700"></div>
                @endif

                <!-- Parent Device (if this device is downstream) -->
                @if($device->parentDevice)
                <div class="w-full max-w-md p-4 bg-indigo-950/40 border border-indigo-500/40 rounded-2xl text-center shadow-lg relative">
                    <span class="px-2 py-0.5 text-[9px] uppercase font-bold bg-indigo-500/20 text-indigo-400 rounded-full border border-indigo-500/30">
                        Parent Device (Bridged ONT)
                    </span>
                    <h4 class="text-sm font-bold text-white mt-2">{{ $device->parentDevice->display_name }}</h4>
                    <p class="text-xs text-slate-400 font-mono">MAC: {{ $device->parentDevice->mac_address }} | SN: {{ $device->parentDevice->serial_number }}</p>
                    <a href="{{ route('udie.show', $device->parentDevice) }}" class="inline-block mt-2 text-xs text-indigo-400 hover:underline">View Parent Details →</a>
                </div>
                <div class="w-0.5 h-6 bg-indigo-500/50"></div>
                @endif

                <!-- CURRENT DEVICE (Center Stage) -->
                <div class="w-full max-w-lg p-5 bg-blue-950/40 border-2 border-blue-500 rounded-2xl text-center shadow-2xl relative">
                    <span class="px-3 py-1 text-[10px] uppercase font-extrabold bg-blue-600 text-white rounded-full shadow-md shadow-blue-500/30">
                        Current Node
                    </span>
                    <div class="w-16 h-16 mx-auto my-3 bg-slate-800 rounded-xl p-2 flex items-center justify-center">
                        <img src="{{ $resolvedImage }}" alt="" class="max-h-full max-w-full object-contain">
                    </div>
                    <h3 class="text-base font-extrabold text-white">{{ $device->display_name }}</h3>
                    <p class="text-xs text-blue-300 font-mono mt-0.5">
                        {{ strtoupper($device->device_type) }} • IP: {{ $device->ip_address ?? 'None' }}
                    </p>
                </div>

                <!-- Child / Downstream Devices (if this device is a parent/bridged ONT) -->
                @if($device->childDevices->count() > 0)
                <div class="w-0.5 h-6 bg-slate-700"></div>
                <div class="w-full max-w-md space-y-3">
                    <span class="block text-center text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                        Downstream Routers / CPEs ({{ $device->childDevices->count() }})
                    </span>
                    @foreach($device->childDevices as $child)
                        <div class="p-3 bg-purple-950/30 border border-purple-500/30 rounded-xl flex items-center justify-between text-xs">
                            <div>
                                <h5 class="font-bold text-white">{{ $child->display_name }}</h5>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    IP: {{ $child->ip_address ?? 'N/A' }} | MAC: {{ $child->mac_address }}
                                </div>
                            </div>
                            <a href="{{ route('udie.show', $child) }}" class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[11px] font-semibold hover:bg-purple-500/30 transition">
                                Inspect Child →
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- TAB 5: MANAGEMENT & PROTOCOLS -->
    <div x-show="activeTab === 'management'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Selected Management Routing</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Primary Management Path</span>
                        <strong class="text-blue-500 text-sm font-mono block">
                            {{ strtoupper(str_replace('_', ' ', $device->primary_management_protocol ?? 'None Selected')) }}
                        </strong>
                        <span class="text-[10px] text-slate-500">Autonomous selection based on verified capabilities.</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Fallback Management Path</span>
                        <strong class="text-slate-300 text-sm font-mono block">
                            {{ strtoupper(str_replace('_', ' ', $device->fallback_management_protocol ?? 'None Configured')) }}
                        </strong>
                        <span class="text-[10px] text-slate-500">Activated if primary management is unreachable.</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Telemetry &amp; Health Status</span>
                        <span class="font-bold uppercase {{ $device->status === 'online' ? 'text-emerald-400' : 'text-slate-400' }}">
                            {{ $device->status }}
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-1">Last Checked: {{ $device->last_management_check?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                </div>
            </div>

            <!-- Verified Capabilities Matrix -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Supported Protocols &amp; Capabilities</h3>
                
                @php
                    $supported = (array) ($device->supported_protocols ?? []);
                    $capabilities = (array) ($device->capabilities ?? []);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <!-- TR-069 CWMP -->
                    <div class="p-3 rounded-xl border {{ in_array('tr069', $supported) ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('tr069', $supported) ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">TR-069 / CWMP ACS</h4>
                            <p class="text-[11px] text-slate-500">Remote CPE configuration, WAN profiling &amp; diagnostics.</p>
                        </div>
                    </div>

                    <!-- TR-181 / USP -->
                    <div class="p-3 rounded-xl border {{ in_array('tr181_usp', $supported) ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('tr181_usp', $supported) ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">TR-181 / USP</h4>
                            <p class="text-[11px] text-slate-500">User Services Platform modern real-time data model.</p>
                        </div>
                    </div>

                    <!-- MikroTik API -->
                    <div class="p-3 rounded-xl border {{ in_array('mikrotik_api', $supported) ? 'bg-blue-500/10 border-blue-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('mikrotik_api', $supported) ? 'bg-blue-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">MikroTik RouterOS API</h4>
                            <p class="text-[11px] text-slate-500">Direct RouterOS v6/v7 REST &amp; binary API integration.</p>
                        </div>
                    </div>

                    <!-- WireGuard VPN -->
                    <div class="p-3 rounded-xl border {{ in_array('wireguard_vpn', $supported) ? 'bg-cyan-500/10 border-cyan-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('wireguard_vpn', $supported) ? 'bg-cyan-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">WireGuard VPN Tunnel</h4>
                            <p class="text-[11px] text-slate-500">Secure encrypted L3 telemetry and management backhaul.</p>
                        </div>
                    </div>

                    <!-- SNMP -->
                    <div class="p-3 rounded-xl border {{ in_array('snmp', $supported) ? 'bg-indigo-500/10 border-indigo-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('snmp', $supported) ? 'bg-indigo-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">SNMP v2c / v3 Polling</h4>
                            <p class="text-[11px] text-slate-500">Interface metrics, CPU/memory, and standard MIBs.</p>
                        </div>
                    </div>

                    <!-- OLT OMCI -->
                    <div class="p-3 rounded-xl border {{ in_array('olt_omci', $supported) ? 'bg-purple-500/10 border-purple-500/30' : 'bg-slate-50 dark:bg-slate-800/30 border-slate-200 dark:border-slate-800' }} flex items-start gap-3">
                        <span class="w-2.5 h-2.5 rounded-full mt-1 {{ in_array('olt_omci', $supported) ? 'bg-purple-500' : 'bg-slate-600' }}"></span>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white">OLT OMCI Channel</h4>
                            <p class="text-[11px] text-slate-500">Fiber optical power, distance, and bridge profiles.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- TAB 6: AUDIT & EVENTS -->
    <div x-show="activeTab === 'history'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Historical Events &amp; Anomaly Detection Log</h3>

            <div class="space-y-3">
                @forelse($device->events as $event)
                    <div class="p-4 rounded-xl border {{ match($event->severity) {
                        'danger' => 'bg-rose-500/10 border-rose-500/30 text-rose-300',
                        'warning' => 'bg-amber-500/10 border-amber-500/30 text-amber-300',
                        default => 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-300',
                    } }} flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-[10px] font-mono uppercase font-bold rounded-md {{ match($event->severity) {
                                    'danger' => 'bg-rose-500/20 text-rose-400',
                                    'warning' => 'bg-amber-500/20 text-amber-400',
                                    default => 'bg-blue-500/20 text-blue-400',
                                } }}">
                                    {{ $event->event_type }}
                                </span>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ $event->title }}</h4>
                                <span class="text-slate-500 text-[11px] font-mono">• {{ $event->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $event->description }}</p>
                        </div>

                        @if(!$event->is_resolved && in_array($event->severity, ['danger', 'warning']))
                            <form action="{{ route('udie.events.resolve', $event) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold whitespace-nowrap transition">
                                    Acknowledge &amp; Resolve
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 text-xs">No audit events recorded for this device yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
