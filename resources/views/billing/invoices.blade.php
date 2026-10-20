@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Summary Cards -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-blue-600 dark:text-cyan-400 uppercase tracking-wider font-mono">Billing &amp; Accounts Receivable</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Auto-Reconciled</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-0.5">Invoices &amp; Receivables</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5">Recurring subscriber billing, auto-numbered invoices, and real-time payment allocations.</p>
        </div>
        <a href="{{ route('billing.payments') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition self-start sm:self-auto shrink-0">
            View Payments &amp; Reconciliation &rarr;
        </a>
    </div>

    <!-- Summary KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Billed</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1.5">KES {{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Collected</div>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-1.5">KES {{ number_format($totalCollected, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Total Outstanding Due</div>
            <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 font-mono mt-1.5">KES {{ number_format($totalOutstanding, 2) }}</div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-bold font-mono uppercase tracking-wider">
                        <th class="p-4">Invoice #</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Service Plan</th>
                        <th class="p-4">Issue / Due Date</th>
                        <th class="p-4">Total Amount</th>
                        <th class="p-4">Paid / Balance</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-mono">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-blue-600 dark:text-cyan-400">{{ $inv->invoice_number }}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900 dark:text-white font-sans text-sm">{{ $inv->client?->name }}</div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300">{{ $inv->client?->account_number }} • {{ $inv->client?->phone }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-800 dark:text-slate-200 font-sans font-semibold">{{ $inv->billingPlan?->name ?? 'Standard Plan' }}</div>
                            <div class="text-[11px] text-blue-600 dark:text-cyan-400 font-semibold">{{ $inv->billingPlan?->formatted_speed }}</div>
                        </td>
                        <td class="p-4 text-[11px]">
                            <div class="text-slate-800 dark:text-slate-200 font-medium">Issued: {{ $inv->issue_date->format('d M Y') }}</div>
                            <div class="text-slate-600 dark:text-slate-400">Due: {{ $inv->due_date->format('d M Y') }}</div>
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">KES {{ number_format($inv->total_amount, 2) }}</td>
                        <td class="p-4">
                            <div class="text-emerald-600 dark:text-emerald-400 font-bold">KES {{ number_format($inv->paid_amount, 2) }}</div>
                            @if($inv->balance_due > 0)
                                <div class="text-rose-600 dark:text-rose-400 font-semibold text-[11px]">Due: KES {{ number_format($inv->balance_due, 2) }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($inv->status === 'paid')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">PAID</span>
                            @elseif($inv->status === 'partially_paid')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">PARTIAL</span>
                            @elseif($inv->status === 'overdue')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30">OVERDUE</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">UNPAID</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-8 text-center text-slate-600 dark:text-slate-300 font-sans font-medium">No invoices generated yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
