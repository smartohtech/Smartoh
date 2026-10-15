@extends('layouts.app')

@section('content')
<div x-data="{ planModal: false }" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Bandwidth & Billing Plans</h1>
            <p class="text-xs text-slate-400 mt-0.5">Configure CIR/PIR queue tree profiles, FUP throttling rules, and pricing.</p>
        </div>
        <button @click="planModal = true" class="px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Create New Plan</span>
        </button>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between hover:border-slate-700 transition group relative overflow-hidden">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-mono">
                        {{ $plan->type }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">1:{{ $plan->contention_ratio }} Contention</span>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white group-hover:text-cyan-400 transition">{{ $plan->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ $plan->description ?? 'Broadband Internet Access Profile' }}</p>
                </div>

                <div class="text-2xl font-black text-white font-mono">
                    KES {{ number_format($plan->price, 2) }}
                    <span class="text-xs font-normal text-slate-500">/ {{ $plan->validity_days }} Days</span>
                </div>

                <!-- Technical Queue Specifications -->
                <div class="p-3 bg-slate-950/70 border border-slate-800/80 rounded-xl space-y-2 text-xs font-mono">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Download / Upload:</span>
                        <span class="text-cyan-400 font-bold">{{ $plan->formatted_speed }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">FUP Data Cap:</span>
                        <span class="text-slate-200">{{ $plan->fup_limit_gb ? $plan->fup_limit_gb . ' GB' : 'Unlimited' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Burst Limit:</span>
                        <span class="text-purple-400">{{ $plan->burst_limit ?: 'None' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">RouterOS Profile:</span>
                        <span class="text-slate-400 truncate">{{ $plan->mikrotik_profile_name ?: 'default' }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs font-mono">
                <span class="text-slate-400">{{ $plan->clients_count }} active subscribers</span>
                <span class="text-emerald-400 font-bold">VLAN: {{ $plan->vlan_id ?? '100' }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Create Plan Modal -->
    <div x-show="planModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="planModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Create Service Plan</h3>
                <button @click="planModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.plans.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Plan Name *</label>
                        <input type="text" name="name" placeholder="e.g. Home Fiber Ultra 50M" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Billing Cycle Type *</label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="monthly">Monthly (30 Days)</option>
                            <option value="weekly">Weekly (7 Days)</option>
                            <option value="daily">Daily (24 Hours)</option>
                            <option value="unlimited">Unlimited Corporate</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Price (KES) *</label>
                        <input type="number" step="0.01" name="price" placeholder="4500.00" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Validity Days *</label>
                        <input type="number" name="validity_days" value="30" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                    <div class="text-[11px] font-bold text-cyan-400 uppercase tracking-wider font-mono">Speed & Queue Trees</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1">Download (Kbps) *</label>
                            <input type="number" name="download_speed_kbps" value="25600" required class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">Upload (Kbps) *</label>
                            <input type="number" name="upload_speed_kbps" value="15360" required class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1">Contention Ratio (1:X)</label>
                            <input type="number" name="contention_ratio" value="2" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">FUP Data Cap (GB)</label>
                            <input type="number" name="fup_limit_gb" placeholder="e.g. 500" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="planModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">Save Plan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
