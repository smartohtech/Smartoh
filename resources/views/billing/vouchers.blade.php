@extends('layouts.app')

@section('content')
<div x-data="{ voucherModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider font-mono">Hotspot &amp; Prepaid Access</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Instant Redemption</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-0.5">Vouchers &amp; Captive Portal PINs</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5">Generate, batch export, and track Hotspot and PPPoE recharge codes.</p>
        </div>
        <button @click="voucherModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-md shadow-purple-600/20 flex items-center space-x-2 transition self-start sm:self-auto shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Batch Generate Vouchers</span>
        </button>
    </div>

    <!-- Summary KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Active Unused Vouchers</div>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-1.5">{{ $activeCount }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 font-mono">READY TO SELL</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Redeemed Vouchers</div>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1.5">{{ $usedCount }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 font-mono">REDEEMED</span>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase tracking-wider">
                        <th class="p-4">Voucher PIN Code</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Face Value</th>
                        <th class="p-4">Validity</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Redeemed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-blue-600 dark:text-cyan-400 tracking-wider text-sm">{{ $v->code }}</td>
                        <td class="p-4 uppercase text-slate-800 dark:text-slate-200 font-semibold font-sans">{{ $v->type }}</td>
                        <td class="p-4 font-bold text-emerald-600 dark:text-emerald-400">KES {{ number_format($v->price, 2) }}</td>
                        <td class="p-4 text-slate-700 dark:text-slate-300 font-medium">{{ round($v->duration_minutes / 60) }} Hours</td>
                        <td class="p-4">
                            @if($v->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">ACTIVE</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">USED</span>
                            @endif
                        </td>
                        <td class="p-4 font-sans font-medium text-slate-700 dark:text-slate-300">
                            {{ $v->client?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-600 dark:text-slate-300 font-sans font-medium">No vouchers created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>

    <!-- Batch Voucher Modal -->
    <div x-show="voucherModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm">
        <div @click.away="voucherModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Batch Generate Vouchers</h3>
                <button @click="voucherModal = false" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.vouchers.generate') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Service Plan Profile *</label>
                    <select name="billing_plan_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                        @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} - KES {{ number_format($p->price) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Voucher Type *</label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                            <option value="hotspot">Hotspot Wi-Fi</option>
                            <option value="pppoe">PPPoE Extension</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Quantity to Generate *</label>
                        <input type="number" name="quantity" value="10" min="1" max="100" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Duration (Hours) *</label>
                    <input type="number" name="duration_hours" value="24" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="voucherModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl shadow-md shadow-purple-600/20">
                        Generate Codes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
