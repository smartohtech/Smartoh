@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider font-mono">Predictive Maintenance</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 font-mono">AI Anomaly Detection</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Device Health &amp; Failure Predictions</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Proactive network diagnostics predicting optical degradation, memory exhaustion, and wireless noise floor capping before customer impact.</p>
        </div>
    </div>

    <!-- Predictions Grid (Matching Base44 PredictiveMaintenance.js) -->
    <div class="space-y-4">
        @foreach($predictions as $pred)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs {{ $pred->severity === 'critical' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400' : ($pred->severity === 'warning' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $pred->device_name }}</h3>
                        <span class="text-[11px] text-slate-400 font-mono">{{ $pred->device_type }} • {{ $pred->metric_value }}</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded text-xs font-bold uppercase font-mono {{ $pred->severity === 'critical' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800' }}">
                    {{ $pred->severity }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs bg-slate-50 dark:bg-slate-950/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-[10px] uppercase font-mono text-slate-400 block">Est. Time to Failure:</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">{{ $pred->est_failure_time }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-mono text-slate-400 block">Predicted Impact:</span>
                    <span class="text-slate-800 dark:text-slate-200">{{ $pred->predicted_fault }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-mono text-slate-400 block">Recommended Action:</span>
                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $pred->recommended_action }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
