@extends('layouts.app')

@section('content')
<div x-data="{ orderModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Wholesale Network Marketplace & Capacity Pools</h1>
            <p class="text-xs text-slate-400 mt-0.5">Carrier exchange points, QinQ VLAN transit handoffs, CIR/PIR reservations, and reseller wallets.</p>
        </div>
        <button @click="orderModal = true" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-blue-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Order Wholesale Capacity</span>
        </button>
    </div>

    <!-- Capacity Pool Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Upstream Capacity</div>
            <div class="text-2xl font-bold text-white font-mono mt-1">{{ number_format($totalBandwidthCapacity) }} Mbps (10 Gbps)</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-cyan-400">Allocated Wholesale CIR</div>
            <div class="text-2xl font-bold text-cyan-400 font-mono mt-1">{{ number_format($totalAllocatedCapacity) }} Mbps</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Available Headroom</div>
            <div class="text-2xl font-bold text-emerald-400 font-mono mt-1">{{ number_format($totalAvailableCapacity) }} Mbps</div>
        </div>
    </div>

    <!-- Capacity Pools & Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Capacity Pools -->
        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white">IXP Interconnect Pools</h3>
            <div class="space-y-3 font-mono">
                @foreach($pools as $p)
                <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white text-sm font-sans">{{ $p->name }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">{{ $p->exchange_point }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>Capacity: {{ number_format($p->total_capacity_mbps) }} Mbps</span>
                        <span class="text-emerald-400 font-bold">Free: {{ number_format($p->available_capacity_mbps) }} Mbps</span>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ round(($p->allocated_capacity_mbps / $p->total_capacity_mbps) * 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Wholesale Products Catalog -->
        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white">Wholesale Catalog (CIR / PIR)</h3>
            <div class="space-y-3 font-mono">
                @foreach($products as $prod)
                <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white text-sm font-sans">{{ $prod->name }}</div>
                        <div class="text-[11px] text-slate-400 mt-1">CIR: {{ $prod->committed_bandwidth_cir_mbps }}M • PIR: {{ $prod->burst_bandwidth_pir_mbps }}M • 1:{{ $prod->contention_ratio }}</div>
                        <div class="text-[10px] text-cyan-400 uppercase mt-0.5">Handoff: {{ str_replace('_', ' ', $prod->handoff_type) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-emerald-400">${{ number_format($prod->price_per_mbps, 2) }} <span class="text-[10px] text-slate-500 font-normal">/ Mbps</span></div>
                        <div class="text-[10px] text-slate-500">Setup: ${{ number_format($prod->setup_fee, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Active Wholesale Orders -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Active Wholesale Service Orders</h3>
            <span class="text-xs text-slate-400 font-mono">{{ count($orders) }} Provisioned Links</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase">
                        <th class="p-4">Order #</th>
                        <th class="p-4">Reseller Client</th>
                        <th class="p-4">Product</th>
                        <th class="p-4">Bandwidth</th>
                        <th class="p-4">VLAN / Handoff</th>
                        <th class="p-4">Monthly Rate</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-slate-800/30">
                        <td class="p-4 font-bold text-cyan-400">{{ $ord->order_number }}</td>
                        <td class="p-4 font-sans font-medium text-white">{{ $ord->wallet?->reseller_company_name }}</td>
                        <td class="p-4 font-sans text-slate-300">{{ $ord->product?->name }}</td>
                        <td class="p-4 font-bold text-white">{{ $ord->bandwidth_mbps }} Mbps</td>
                        <td class="p-4 text-cyan-400">VLAN {{ $ord->vlan_id ?? '304' }} ({{ $ord->allocated_subnet ?? 'Subnet /29' }})</td>
                        <td class="p-4 font-bold text-emerald-400">${{ number_format($ord->monthly_charge, 2) }}/mo</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 uppercase">
                                {{ $ord->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-6 text-center text-slate-500 font-sans">No wholesale capacity orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Capacity Modal -->
    <div x-show="orderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="orderModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Order Wholesale Bandwidth Capacity</h3>
                <button @click="orderModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('wholesale.order.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Reseller Wallet *</label>
                    <select name="wholesale_wallet_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach($wallets as $w)
                        <option value="{{ $w->id }}">{{ $w->reseller_company_name }} (Balance: ${{ number_format($w->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Wholesale Product *</label>
                    <select name="wholesale_product_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} - ${{ $prod->price_per_mbps }}/Mbps</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Requested Bandwidth (Mbps) *</label>
                    <input type="number" name="bandwidth_mbps" value="250" min="10" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="orderModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30">
                        Submit Order & Allocate VLAN
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
