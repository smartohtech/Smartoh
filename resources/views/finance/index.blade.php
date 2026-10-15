@extends('layouts.app')

@section('content')
<div x-data="{ accountModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Multi-Site Finance, Ledger & P&L</h1>
            <p class="text-xs text-slate-400 mt-0.5">Double-entry accounting, site revenue consolidation, and chart of accounts.</p>
        </div>
        <button @click="accountModal = true" class="px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add General Ledger Account</span>
        </button>
    </div>

    <!-- P&L Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Billed Revenue</div>
            <div class="text-2xl font-bold text-emerald-400 font-mono mt-1">KES {{ number_format($pnl['total_revenue'], 2) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Operating Expenses</div>
            <div class="text-2xl font-bold text-rose-400 font-mono mt-1">KES {{ number_format($pnl['total_expense'], 2) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Net Operating Margin</div>
            <div class="text-2xl font-bold text-white font-mono mt-1">KES {{ number_format($pnl['net_profit'], 2) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Profit Margin %</div>
            <div class="text-2xl font-bold text-cyan-400 font-mono mt-1">{{ $pnl['margin_pct'] }}%</div>
        </div>
    </div>

    <!-- Chart of Accounts & General Ledger Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart of Accounts -->
        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Chart of Accounts (COA)</h3>
                <span class="text-xs text-slate-400 font-mono">{{ count($accounts) }} Accounts</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase">
                            <th class="p-3">Code</th>
                            <th class="p-3">Account Name</th>
                            <th class="p-3">Class</th>
                            <th class="p-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-800/30">
                            <td class="p-3 font-bold text-cyan-400">{{ $acc->code }}</td>
                            <td class="p-3 font-sans font-medium text-white">{{ $acc->name }}</td>
                            <td class="p-3 uppercase text-slate-400 text-[10px]">{{ $acc->type }}</td>
                            <td class="p-3 text-right font-bold {{ $acc->type === 'expense' ? 'text-rose-400' : 'text-emerald-400' }}">
                                KES {{ number_format($acc->current_balance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-500 font-sans">No accounts registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- General Ledger Journal Transactions -->
        <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">General Ledger Postings</h3>
                <span class="text-xs text-slate-400 font-mono">Double-Entry</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase">
                            <th class="p-3">TX Number</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Debit / Credit</th>
                            <th class="p-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-800/30">
                            <td class="p-3 font-bold text-white">{{ $tx->transaction_number }}</td>
                            <td class="p-3 text-slate-400 text-[11px]">{{ $tx->transaction_date->format('d M Y') }}</td>
                            <td class="p-3">
                                <div class="text-emerald-400 text-[11px]">Dr: {{ $tx->debitAccount?->name }}</div>
                                <div class="text-cyan-400 text-[11px]">Cr: {{ $tx->creditAccount?->name }}</div>
                            </td>
                            <td class="p-3 text-right font-bold text-white">KES {{ number_format($tx->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-500 font-sans">No ledger entries posted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create Account Modal -->
    <div x-show="accountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="accountModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Add Chart of Account</h3>
                <button @click="accountModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('finance.account.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Account Code *</label>
                        <input type="text" name="code" placeholder="e.g. 5020" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Account Class *</label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Account Name *</label>
                    <input type="text" name="name" placeholder="e.g. Fiber Maintenance & Splicing Expense" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Initial Balance (KES)</label>
                    <input type="number" step="0.01" name="current_balance" value="0.00" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="accountModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">Save Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
