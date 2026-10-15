@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Payment Gateways</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">Manual Settlement</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Manual Payment Gateways</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Direct bank deposit accounts, office walk-in cash desks, and manual payment reconciliations.</p>
        </div>
    </div>

    <!-- Manual Gateway Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($manualGateways as $gw)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $gw->name }}</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase font-mono">
                        {{ $gw->status }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-xs font-mono">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Account / Till:</span>
                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $gw->account_number }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Branch:</span>
                        <span class="text-slate-700 dark:text-slate-300 font-sans">{{ $gw->branch }}</span>
                    </div>
                    <div class="pt-2 text-slate-500 dark:text-slate-400 font-sans text-[11px]">
                        {{ $gw->instructions }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Reconciled in General Ledger</span>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
