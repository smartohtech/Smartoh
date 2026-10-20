@extends('layouts.app')

@section('content')
<div x-data="{ accountModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-blue-600 dark:text-cyan-400 uppercase tracking-wider font-mono">Financial Operations</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Double-Entry Ledger</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-0.5">Multi-Site Finance, Ledger &amp; P&amp;L</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5">Double-entry accounting, site revenue consolidation, and chart of accounts.</p>
        </div>
        <button @click="accountModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-2 transition self-start sm:self-auto shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add General Ledger Account</span>
        </button>
    </div>

    <!-- P&L Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Billed Revenue</div>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-1.5">KES {{ number_format($pnl['total_revenue'], 2) }}</div>
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">Recognized subscriber collections</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Operating Expenses</div>
            <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 font-mono mt-1.5">KES {{ number_format($pnl['total_expense'], 2) }}</div>
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">Upstream transit &amp; POP operations</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Net Operating Margin</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1.5">KES {{ number_format($pnl['net_profit'], 2) }}</div>
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">Net earnings after operating costs</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Profit Margin %</div>
            <div class="text-2xl font-extrabold text-blue-600 dark:text-cyan-400 font-mono mt-1.5">{{ $pnl['margin_pct'] }}%</div>
            <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1">Operating profitability ratio</div>
        </div>
    </div>

    <!-- Chart of Accounts & General Ledger Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart of Accounts -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Chart of Accounts (COA)</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-mono">{{ count($accounts) }} Accounts</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase">
                            <th class="p-3.5">Code</th>
                            <th class="p-3.5">Account Name</th>
                            <th class="p-3.5">Class</th>
                            <th class="p-3.5 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-3.5 font-bold text-blue-600 dark:text-cyan-400">{{ $acc->code }}</td>
                            <td class="p-3.5 font-sans font-semibold text-slate-900 dark:text-white">{{ $acc->name }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $acc->type }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right font-bold {{ $acc->type === 'expense' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                KES {{ number_format($acc->current_balance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-600 dark:text-slate-300 font-sans text-xs font-medium">
                                No accounts registered yet. Click <strong>"Add General Ledger Account"</strong> above to add one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- General Ledger Journal Transactions -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">General Ledger Postings</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-mono">Double-Entry</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase">
                            <th class="p-3.5">TX Number</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Debit / Credit</th>
                            <th class="p-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $tx->transaction_number }}</td>
                            <td class="p-3.5 text-slate-600 dark:text-slate-300 text-[11px]">{{ $tx->transaction_date->format('d M Y') }}</td>
                            <td class="p-3.5 font-sans">
                                <div class="text-emerald-700 dark:text-emerald-400 font-semibold text-[11px]">Dr: {{ $tx->debitAccount?->name }}</div>
                                <div class="text-blue-700 dark:text-cyan-400 font-semibold text-[11px]">Cr: {{ $tx->creditAccount?->name }}</div>
                            </td>
                            <td class="p-3.5 text-right font-bold text-slate-900 dark:text-white">KES {{ number_format($tx->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-600 dark:text-slate-300 font-sans text-xs font-medium">
                                No ledger entries posted yet. Verified subscriber payments automatically post double-entry journal records here.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Create Account Modal -->
    <div x-show="accountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm">
        <div @click.away="accountModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Add Chart of Account</h3>
                <button @click="accountModal = false" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('finance.account.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Account Code *</label>
                        <input type="text" name="code" placeholder="e.g. 5020" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Account Class *</label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Account Name *</label>
                    <input type="text" name="name" placeholder="e.g. Fiber Maintenance & Splicing Expense" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-200 mb-1">Initial Balance (KES)</label>
                    <input type="number" step="0.01" name="current_balance" value="0.00" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="accountModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-md shadow-blue-600/20">Save Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
