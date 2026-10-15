@extends('layouts.platform')

@section('title', 'SaaS Plans & Pricing Tiers')

@section('content')
<div x-data="{ createModal: false, editModal: false, activePlan: null }" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">SaaS Subscription Plans &amp; Versions</h1>
            <p class="text-xs text-slate-400 mt-1">Configure pricing, resource entitlements, and immutable version snapshots for ISP tiers.</p>
        </div>
        <button @click="createModal = true" class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition flex items-center space-x-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Create New Plan</span>
        </button>
    </div>

    <!-- 3 Tier Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        @php $ver = $plan->currentVersion; @endphp
        <div class="bg-slate-900/90 border {{ $plan->is_active ? 'border-slate-800' : 'border-rose-500/30 opacity-75' }} rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-6 relative overflow-hidden group">
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-white">{{ $plan->name }}</h2>
                        <span class="text-[10px] font-mono text-cyan-400">Slug: {{ $plan->slug }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $plan->is_active ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-300 border border-rose-500/30' }}">
                        {{ $plan->is_active ? 'ACTIVE' : 'ARCHIVED' }}
                    </span>
                </div>

                <p class="text-xs text-slate-400 min-h-[36px]">{{ $plan->description }}</p>

                <!-- Pricing Display -->
                <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                    <div class="flex items-baseline space-x-1">
                        <span class="text-2xl font-black text-white font-mono">{{ $plan->currency }} {{ number_format($plan->monthly_price) }}</span>
                        <span class="text-xs text-slate-500 font-mono">/month</span>
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                        {{ $plan->currency }} {{ number_format($plan->annual_price) }} billed annually
                    </div>
                    <div class="text-[10px] text-cyan-400 font-mono pt-1">
                        {{ $plan->trial_days }}-Day Free Trial Default
                    </div>
                </div>

                <!-- Limits & Features -->
                @if($ver)
                <div class="space-y-1.5 text-xs font-mono border-t border-slate-800 pt-3">
                    <div class="text-[10px] uppercase text-slate-500 font-bold mb-1">Version {{ $ver->version_number }}.0 Limits:</div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Max Clients:</span>
                        <strong class="text-white">{{ number_format($ver->features['max_subscribers'] ?? 0) }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Routers:</span>
                        <strong class="text-white">{{ $ver->features['max_routers'] ?? 0 }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Staff Accounts:</span>
                        <strong class="text-white">{{ $ver->features['max_staff'] ?? 0 }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>Branches/Sites:</span>
                        <strong class="text-white">{{ $ver->features['max_sites'] ?? 1 }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>API Access:</span>
                        <span class="{{ !empty($ver->features['has_api_access']) ? 'text-emerald-400 font-bold' : 'text-slate-600' }}">
                            {{ !empty($ver->features['has_api_access']) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span>White-Label Branding:</span>
                        <span class="{{ !empty($ver->features['has_white_label']) ? 'text-emerald-400 font-bold' : 'text-slate-600' }}">
                            {{ !empty($ver->features['has_white_label']) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Card Footer: Subscriptions & Version History -->
            <div class="pt-4 border-t border-slate-800 space-y-3">
                <div class="flex items-center justify-between text-[11px] font-mono text-slate-400">
                    <span>{{ $plan->subscriptions_count }} Active Subscriptions</span>
                    <span>{{ $plan->versions->count() }} Historic Versions</span>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="button" @click="activePlan = {{ $plan->toJson() }}; editModal = true" class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition text-center">
                        Edit &amp; Version
                    </button>
                    <form method="POST" action="{{ route('platform.plans.toggle-status', $plan) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-slate-950 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white rounded-xl text-xs transition">
                            {{ $plan->is_active ? 'Archive' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <!-- CREATE PLAN MODAL -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="createModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Create New SaaS Plan</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('platform.plans.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Plan Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Regional Core" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Slug Identifier *</label>
                        <input type="text" name="slug" required placeholder="e.g. regional-core" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief explanation of target audience and highlights..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Monthly Price *</label>
                        <input type="number" step="0.01" name="monthly_price" required placeholder="5000" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Annual Price *</label>
                        <input type="number" step="0.01" name="annual_price" required placeholder="50000" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Currency *</label>
                        <input type="text" name="currency" required value="KES" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Free Trial Days *</label>
                        <input type="number" name="trial_days" required value="7" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Subscribers *</label>
                        <input type="number" name="max_subscribers" required value="500" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Routers</label>
                        <input type="number" name="max_routers" required value="5" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Staff</label>
                        <input type="number" name="max_staff" required value="5" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Sites</label>
                        <input type="number" name="max_sites" required value="2" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <label class="flex items-center space-x-2 text-slate-300 cursor-pointer">
                        <input type="checkbox" name="has_api_access" value="1" class="rounded bg-slate-950 border-slate-700 text-cyan-600">
                        <span>API Access</span>
                    </label>
                    <label class="flex items-center space-x-2 text-slate-300 cursor-pointer">
                        <input type="checkbox" name="has_white_label" value="1" class="rounded bg-slate-950 border-slate-700 text-cyan-600">
                        <span>White-Label Branding</span>
                    </label>
                    <label class="flex items-center space-x-2 text-slate-300 cursor-pointer">
                        <input type="checkbox" name="has_advanced_noc" value="1" class="rounded bg-slate-950 border-slate-700 text-cyan-600">
                        <span>Advanced NOC Engine</span>
                    </label>
                    <label class="flex items-center space-x-2 text-slate-300 cursor-pointer">
                        <input type="checkbox" name="has_gis_maps" value="1" class="rounded bg-slate-950 border-slate-700 text-cyan-600">
                        <span>GIS Fiber Maps</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT & VERSION PLAN MODAL -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="editModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">
                    Update Plan: <span x-text="activePlan?.name" class="text-cyan-400"></span>
                </h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-amber-400/90 leading-relaxed bg-amber-500/10 p-3 rounded-xl border border-amber-500/20">
                <strong>Versioning Guard Active:</strong> Saving changes will automatically generate an immutable incremented plan version snapshot. Existing ISP subscriber contracts will remain bound to their agreed version terms.
            </p>

            <form :action="'/platform/plans/' + activePlan?.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Plan Display Name *</label>
                    <input type="text" name="name" :value="activePlan?.name" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" :value="activePlan?.description" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Monthly Price *</label>
                        <input type="number" step="0.01" name="monthly_price" :value="activePlan?.monthly_price" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Annual Price *</label>
                        <input type="number" step="0.01" name="annual_price" :value="activePlan?.annual_price" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Currency *</label>
                        <input type="text" name="currency" :value="activePlan?.currency" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Free Trial Days *</label>
                        <input type="number" name="trial_days" :value="activePlan?.trial_days ?? 7" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Subscribers *</label>
                        <input type="number" name="max_subscribers" :value="activePlan?.current_version?.features?.max_subscribers ?? 500" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Routers</label>
                        <input type="number" name="max_routers" :value="activePlan?.current_version?.features?.max_routers ?? 5" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Staff</label>
                        <input type="number" name="max_staff" :value="activePlan?.current_version?.features?.max_staff ?? 5" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Max Sites</label>
                        <input type="number" name="max_sites" :value="activePlan?.current_version?.features?.max_sites ?? 2" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Version Changelog Summary</label>
                    <input type="text" name="changelog" placeholder="e.g. Expanded router quota and added white-label support" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="editModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Save as New Version
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
