@extends('layouts.platform')

@section('title', 'Platform Invoices & Revenue')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Platform Invoices &amp; Revenue</h1>
            <p class="text-xs text-slate-400 mt-1">SaaS subscription billing, automated invoicing cycles, and verified payment reconciliation.</p>
        </div>
    </div>

    <!-- 2 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-mono">Total Verified Revenue</span>
                <div class="text-3xl font-extrabold text-emerald-400 font-mono mt-1">KES {{ number_format($totalPaid) }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="p-5 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-mono">Outstanding / Pending Due</span>
                <div class="text-3xl font-extrabold text-amber-400 font-mono mt-1">KES {{ number_format($totalPending) }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 font-mono uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Tenant ISP</th>
                        <th class="py-3.5 px-4">Plan / Interval</th>
                        <th class="py-3.5 px-4">Period</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-cyan-400">
                            {{ $inv->invoice_number }}
                            <div class="text-[10px] text-slate-500 font-normal">Issued: {{ $inv->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-white">
                            {{ $inv->tenant->name ?? 'Unknown ISP' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-300">
                            {{ $inv->subscription->plan->name ?? 'Platform Core' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[10px] text-slate-400">
                            {{ $inv->billing_period_start->format('M d') }} - {{ $inv->billing_period_end->format('M d, Y') }}
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-white text-xs">
                            {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($inv->isPaid())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                    PAID
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                    PENDING
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('platform.invoices.show', $inv) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-200 rounded-xl text-xs font-semibold transition">
                                View Bill &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 font-mono text-xs">
                            No SaaS invoices have been created yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
