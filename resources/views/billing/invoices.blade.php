@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Summary Cards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Invoices & Receivables</h1>
            <p class="text-xs text-slate-400 mt-0.5">Recurring subscriber billing, auto-numbered invoices, and receipts.</p>
        </div>
    </div>

    <!-- Summary KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Billed</div>
            <div class="text-2xl font-bold text-white font-mono mt-1">KES {{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Total Collected</div>
            <div class="text-2xl font-bold text-emerald-400 font-mono mt-1">KES {{ number_format($totalCollected, 2) }}</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-400">Total Outstanding Due</div>
            <div class="text-2xl font-bold text-amber-400 font-mono mt-1">KES {{ number_format($totalOutstanding, 2) }}</div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-mono uppercase tracking-wider">
                        <th class="p-4">Invoice #</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Service Plan</th>
                        <th class="p-4">Issue / Due Date</th>
                        <th class="p-4">Total Amount</th>
                        <th class="p-4">Paid / Balance</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-4 font-bold text-cyan-400">{{ $inv->invoice_number }}</td>
                        <td class="p-4">
                            <div class="font-bold text-white font-sans text-sm">{{ $inv->client?->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $inv->client?->account_number }} • {{ $inv->client?->phone }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-200 font-sans font-medium">{{ $inv->billingPlan?->name ?? 'Standard Plan' }}</div>
                            <div class="text-[11px] text-cyan-400">{{ $inv->billingPlan?->formatted_speed }}</div>
                        </td>
                        <td class="p-4 text-[11px]">
                            <div class="text-slate-200">Issued: {{ $inv->issue_date->format('d M Y') }}</div>
                            <div class="text-slate-500">Due: {{ $inv->due_date->format('d M Y') }}</div>
                        </td>
                        <td class="p-4 font-bold text-white">KES {{ number_format($inv->total_amount, 2) }}</td>
                        <td class="p-4">
                            <div class="text-emerald-400 font-bold">KES {{ number_format($inv->paid_amount, 2) }}</div>
                            @if($inv->balance_due > 0)
                                <div class="text-rose-400 text-[11px]">Due: KES {{ number_format($inv->balance_due, 2) }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($inv->status === 'paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">PAID</span>
                            @elseif($inv->status === 'partially_paid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">PARTIAL</span>
                            @elseif($inv->status === 'overdue')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">OVERDUE</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-500/10 text-slate-300 border border-slate-500/30">UNPAID</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-8 text-center text-slate-500">No invoices generated yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
