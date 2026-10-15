@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeCatalog: 'global', addTenantModelModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                    Device Model Intelligence Registry
                    <span class="px-2 py-0.5 text-[10px] font-mono uppercase bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-full">Rules 11-14 Catalog</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">Strict separation between Global Master Catalog and Tenant-Specific Branding Overrides.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button @click="addTenantModelModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/20 flex items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add Tenant Model Override</span>
            </button>
            <a href="{{ route('udie.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                Back to Inventory
            </a>
        </div>
    </div>

    <!-- Architectural Rules Callout (Rules 11, 12, 13, 14) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl">
            <h4 class="font-bold text-cyan-400 uppercase tracking-wider text-[11px] mb-1">Global Catalog Isolation (Rule 11)</h4>
            <p class="text-slate-400">
                Shared hardware master definitions (Huawei, ZTE, MikroTik, TP-Link, VSOL, Cisco). Changes to the Global Catalog provide baseline fingerprints across all tenants without altering custom tenant branding.
            </p>
        </div>
        <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl">
            <h4 class="font-bold text-indigo-400 uppercase tracking-wider text-[11px] mb-1">Tenant Catalog Overrides &amp; Hierarchy (Rules 13 &amp; 14)</h4>
            <p class="text-slate-400">
                Tenant-specific images, custom marketing names, and override definitions take immediate priority in the NOC and client portal: <span class="font-mono text-slate-300">Tenant Model &gt; Global Model &gt; Tenant Type &gt; Global Type &gt; Generic</span>.
            </p>
        </div>
    </div>

    <!-- Catalog View Selector -->
    <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-4 text-xs font-semibold">
        <button @click="activeCatalog = 'global'" :class="activeCatalog === 'global' ? 'text-cyan-400 border-b-2 border-cyan-400' : 'text-slate-500 hover:text-slate-300'" class="pb-3 px-2 transition flex items-center gap-1.5">
            <span>Global Master Catalog</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] text-cyan-300">{{ $globalModels->total() }}</span>
        </button>
        <button @click="activeCatalog = 'tenant'" :class="activeCatalog === 'tenant' ? 'text-indigo-400 border-b-2 border-indigo-400' : 'text-slate-500 hover:text-slate-300'" class="pb-3 px-2 transition flex items-center gap-1.5">
            <span>Tenant Private Overrides</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[10px] text-indigo-300">{{ $tenantModels->total() }}</span>
        </button>
    </div>

    <!-- GLOBAL CATALOG VIEW -->
    <div x-show="activeCatalog === 'global'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($globalModels as $model)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:border-cyan-500/40 transition flex flex-col justify-between">
                    <div>
                        <div class="w-full h-32 rounded-xl bg-slate-50 dark:bg-slate-800/80 p-3 flex items-center justify-center mb-3 border border-slate-100 dark:border-slate-800">
                            <img src="{{ $model->image_url ?? asset('images/devices/generic_router.svg') }}" alt="{{ $model->manufacturer }} {{ $model->model }}" class="max-h-full max-w-full object-contain filter drop-shadow">
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                                {{ $model->manufacturer }}
                            </span>
                            <span class="text-[10px] uppercase font-mono text-slate-400">{{ strtoupper($model->device_type) }}</span>
                        </div>

                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1.5">{{ $model->model }}</h3>
                        
                        @if($model->product_class)
                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">Class: {{ $model->product_class }}</p>
                        @endif

                        @if(!empty($model->supported_protocols))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($model->supported_protocols as $proto)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-400">
                                        {{ strtoupper(str_replace('_', ' ', $proto)) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Active Devices: <strong class="text-slate-900 dark:text-white font-mono">{{ $model->devices_count }}</strong></span>
                        <span class="text-[10px] text-emerald-400 font-semibold">Global Baseline</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 text-xs">No global models configured in catalog.</div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $globalModels->links() }}
        </div>
    </div>

    <!-- TENANT OVERRIDES VIEW -->
    <div x-show="activeCatalog === 'tenant'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($tenantModels as $tModel)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm hover:border-indigo-500/40 transition flex flex-col justify-between">
                    <div>
                        <div class="w-full h-32 rounded-xl bg-slate-50 dark:bg-slate-800/80 p-3 flex items-center justify-center mb-3 border border-slate-100 dark:border-slate-800">
                            <img src="{{ $tModel->custom_image_url ?? $tModel->globalModel?->image_url ?? asset('images/devices/generic_router.svg') }}" alt="{{ $tModel->custom_name }}" class="max-h-full max-w-full object-contain filter drop-shadow">
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                                Tenant Override
                            </span>
                            <span class="text-[10px] font-mono text-emerald-400">Approved</span>
                        </div>

                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mt-1.5">{{ $tModel->custom_name }}</h3>

                        @if($tModel->globalModel)
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Base: {{ $tModel->globalModel->manufacturer }} {{ $tModel->globalModel->model }}
                            </p>
                        @endif

                        @if($tModel->custom_description)
                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $tModel->custom_description }}</p>
                        @endif
                    </div>

                    <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Associated: <strong class="text-slate-900 dark:text-white font-mono">{{ $tModel->devices->count() }}</strong></span>
                        <span class="text-[10px] text-indigo-400 font-semibold">Priority 1 Active</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">No Tenant Overrides Defined</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Your tenant is currently using the shared Global Master Catalog defaults for all device names and images.</p>
                    <div class="mt-4">
                        <button @click="addTenantModelModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                            Create First Tenant Override
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="pt-2">
            {{ $tenantModels->links() }}
        </div>
    </div>

    <!-- Modal to Add Tenant Model Override -->
    <div x-show="addTenantModelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addTenantModelModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">New Tenant Device Model Override</h3>
                <button @click="addTenantModelModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('udie.models.tenant.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Global Base Model (Optional)</label>
                    <select name="global_device_model_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">None (Custom Standalone Model)</option>
                        @foreach($globalModels as $gm)
                            <option value="{{ $gm->id }}">{{ $gm->manufacturer }} - {{ $gm->model }} ({{ $gm->device_type }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Custom Display Name</label>
                    <input type="text" name="custom_name" placeholder="e.g. Smartoh Enterprise Gateway Pro" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Custom High-Resolution Image URL (Optional)</label>
                    <input type="url" name="custom_image_url" placeholder="https://..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-400 uppercase tracking-wider mb-1">Custom Description / Notes</label>
                    <textarea name="custom_description" rows="3" placeholder="Notes on this hardware model profile..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addTenantModelModal = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-md shadow-indigo-600/20 transition">Save Tenant Model</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
