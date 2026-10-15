@extends('layouts.app')

@section('content')
<div x-data="{ stkModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Payment Collections & M-Pesa Gateway</h1>
            <p class="text-xs text-slate-400 mt-0.5">Safaricom Daraja STK Push, Till/Paybill reconciliation, and Bank settlements.</p>
        </div>
        <button @click="stkModal = true" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            <span>Trigger M-Pesa STK Push</span>
        </button>
    </div>

    <!-- Active Gateways Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($gateways as $gw)
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-white">{{ $gw->name }}</div>
                <div class="text-[11px] text-cyan-400 font-mono mt-0.5">{{ strtoupper(str_replace('_', ' ', $gw->provider)) }}</div>
            </div>
            <div class="text-right">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono">
                    {{ $gw->is_sandbox ? 'SANDBOX ACTIVE' : 'LIVE PRODUCTION' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Payments Ledger -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Payment Transactions Ledger</h3>
            <span class="text-xs text-slate-400 font-mono">Total Volume: KES {{ number_format($totalVolume, 2) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Receipt / Ref #</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Payment Method</th>
                        <th class="p-4">Amount</th>
                        <th class="p-4">Phone / Account</th>
                        <th class="p-4">Date & Time</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-4 font-bold text-cyan-400">{{ $p->external_transaction_id ?? $p->transaction_reference }}</td>
                        <td class="p-4 font-sans font-medium text-white">{{ $p->payer_name ?? $p->client?->name ?? 'Subscriber' }}</td>
                        <td class="p-4 uppercase text-slate-300">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                        <td class="p-4 font-bold text-emerald-400">+KES {{ number_format($p->amount, 2) }}</td>
                        <td class="p-4 text-slate-400">{{ $p->phone_number ?? 'N/A' }}</td>
                        <td class="p-4 text-slate-400 text-[11px]">{{ $p->created_at->format('d M Y H:i:s') }}</td>
                        <td class="p-4">
                            @if($p->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">COMPLETED</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">{{ strtoupper($p->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-8 text-center text-slate-500 font-sans">No payment records logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- Trigger M-Pesa STK Modal -->
    <div x-show="stkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="stkModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        M
                    </div>
                    <h3 class="text-base font-bold text-white">Trigger M-Pesa STK Push</h3>
                </div>
                <button @click="stkModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.payments.stk-push') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Select Subscriber *</label>
                    <select name="client_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach(App\Models\Client::all() as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }}) - {{ $client->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Amount to Bill (KES) *</label>
                    <input type="number" step="0.01" name="amount" value="4000.00" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center space-x-2">
                    <input type="checkbox" name="simulate_instant_payment" value="1" checked id="sim_check" class="rounded bg-slate-800 border-slate-700 text-emerald-500">
                    <label for="sim_check" class="text-slate-300 cursor-pointer">
                        Simulate instant callback confirmation & unblock service (Demo mode)
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="stkModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">
                        Dispatch STK Prompt
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
