@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ identifyModalOpen: false, selectedDevice: null }">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                    Unknown Device Laboratory
                    <span class="px-2 py-0.5 text-[10px] font-mono uppercase bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full">Fingerprint Triage</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">Identify, verify, and register unclassified network equipment into your tenant model catalog.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('udie.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Back to Device Inventory</span>
            </a>
        </div>
    </div>

    <!-- Rule 45 Banner -->
    <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl flex items-start gap-3 text-xs text-slate-400">
        <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <strong class="text-slate-200">Rule 45 Enforced (No Guesswork Policy):</strong> When incoming telemetry does not reach the 75% confidence threshold, the UDIE engine intentionally labels the device as <span class="text-rose-400 font-semibold">Unknown Device / Model Not Verified</span> rather than making false claims. Use this triage desk to review raw telemetry and assign verified catalog definitions.
        </div>
    </div>

    <!-- Devices List -->
    <div class="space-y-3">
        @forelse($devices as $device)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:border-amber-500/40 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <div class="flex items-start sm:items-center gap-4 min-w-0">
                    <div class="w-14 h-14 rounded-xl bg-amber-500/10 border border-amber-500/20 p-2 flex items-center justify-center shrink-0">
                        <img src="{{ $device->resolved_thumbnail_url }}" alt="" class="max-h-full max-w-full object-contain">
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ $device->display_name }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded-full {{ $device->confidence_score >= 50 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                                {{ round($device->confidence_score, 1) }}% {{ $device->confidence_category }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-mono uppercase bg-slate-100 dark:bg-slate-800 text-slate-400 rounded">
                                {{ strtoupper($device->device_type) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-mono mt-1">
                            <span class="text-slate-800 dark:text-slate-200 font-semibold">IP: {{ $device->ip_address ?? 'None' }}</span>
                            <span>MAC: {{ $device->mac_address ?? 'N/A' }}</span>
                            @if($device->vrf)
                                <span class="text-indigo-400">VRF: {{ $device->vrf->name }}</span>
                            @endif
                            @if($device->vlan_id)
                                <span class="text-amber-400">VLAN {{ $device->vlan_id }}</span>
                            @endif
                            <span>Evidence Collected: {{ $device->evidences->count() }} item(s)</span>
                        </div>

                        @if($device->customer)
                            <div class="text-[11px] text-slate-500 mt-1">
                                Assigned Client: <strong class="text-slate-700 dark:text-slate-300">{{ $device->customer->name }}</strong>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('udie.show', $device) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition">
                        Inspect Raw Telemetry
                    </a>

                    <button @click="selectedDevice = {{ json_encode($device) }}; identifyModalOpen = true" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Verify &amp; Classify</span>
                    </button>
                </div>

            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">All Discovered Devices Are Verified</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">There are currently zero unclassified or unverified network devices in your tenant domain.</p>
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $devices->links() }}
    </div>

    <!-- Modal for Manual Identification -->
    <div x-show="identifyModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="identifyModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Classify &amp; Verify Network Device</h3>
                <button @click="identifyModalOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <template x-if="selectedDevice">
                <form :action="'/devices/' + selectedDevice.id + '/identify'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <span class="text-[11px] text-slate-400 block mb-1">Target Scoped Device:</span>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 font-mono text-slate-300">
                            <span x-text="'IP: ' + (selectedDevice.ip_address || 'Unassigned') + ' • MAC: ' + (selectedDevice.mac_address || 'N/A')"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Manufacturer</label>
                        <input type="text" name="manufacturer" :value="selectedDevice.manufacturer || ''" placeholder="e.g. Huawei, ZTE, MikroTik, TP-Link" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Exact Device Model</label>
                        <input type="text" name="model" :value="selectedDevice.model || ''" placeholder="e.g. HG8145H, RB4011, Archer C6" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Device Type</label>
                            <select name="device_type" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="router">Router / Gateway</option>
                                <option value="ont">ONT / ONU</option>
                                <option value="olt">OLT</option>
                                <option value="switch">Switch</option>
                                <option value="ap">Access Point</option>
                                <option value="cpe">Customer CPE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Catalog Scope</label>
                            <select name="catalog_target" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="tenant">Tenant Catalog Override</option>
                                <option value="global">Global Shared Catalog</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Custom Display Name (Optional)</label>
                        <input type="text" name="custom_name" placeholder="Optional branding name" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="identifyModalOpen = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-md shadow-emerald-600/20 transition">Save &amp; Verify Model</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
