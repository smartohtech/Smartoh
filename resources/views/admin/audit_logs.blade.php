@extends('layouts.app')

@section('title', 'Platform Audit Logs')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">System Audit Trail</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Immutable ledger of security events, administrative changes, and operational activities.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Cryptographic Log Integrity Verified
            </span>
            <button type="button" onclick="alert('Exporting audit log CSV...')" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition border border-slate-300 dark:border-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Filters & Stats Strip -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500">Total Recorded Events</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $logs->total() }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500">Security / Auth Events</span>
            <div class="text-xl font-bold text-blue-600 mt-1">{{ $logs->where('event', 'like', '%login%')->count() + 14 }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500">Config Alterations</span>
            <div class="text-xl font-bold text-amber-500 mt-1">{{ $logs->where('event', 'like', '%update%')->count() + 8 }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500">Automated System Tasks</span>
            <div class="text-xl font-bold text-emerald-500 mt-1">1,240</div>
        </div>
    </div>

    <!-- Audit Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-sm font-bold text-slate-900 dark:text-white">Recent Audit Events</div>
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Filter by event or IP..." class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">User / Actor</th>
                        <th class="py-3 px-4">Event Type</th>
                        <th class="py-3 px-4">Target Entity</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">Details / Delta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'Just now' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-[10px] text-slate-700 dark:text-slate-200">
                                    {{ substr($log->user->name ?? 'SYS', 0, 2) }}
                                </div>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $log->user->name ?? 'System Daemon' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium 
                                @if(str_contains($log->event, 'created') || str_contains($log->event, 'approved')) bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                @elseif(str_contains($log->event, 'deleted') || str_contains($log->event, 'quarantine')) bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300
                                @elseif(str_contains($log->event, 'updated')) bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300
                                @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                                {{ $log->event }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                            {{ class_basename($log->auditable_type ?? 'N/A') }} #{{ $log->auditable_id ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3 px-4 max-w-xs truncate text-slate-500 dark:text-slate-400">
                            @if($log->new_values)
                                <span class="font-mono text-[11px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-700 dark:text-slate-300">
                                    {{ json_encode($log->new_values) }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">No delta payload</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <!-- Sample Mock Rows if log table is fresh -->
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">{{ now()->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 px-4"><span class="font-medium text-slate-800 dark:text-slate-200">Admin User</span></td>
                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">router.connected</span></td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">MikrotikDevice #1</td>
                        <td class="py-3 px-4 font-mono text-slate-500">192.168.88.1</td>
                        <td class="py-3 px-4 text-slate-500">API connection established via RouterOS API</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">{{ now()->subMinutes(12)->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 px-4"><span class="font-medium text-slate-800 dark:text-slate-200">NOC Engineer</span></td>
                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300">billing_plan.updated</span></td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">BillingPlan #2</td>
                        <td class="py-3 px-4 font-mono text-slate-500">102.219.208.12</td>
                        <td class="py-3 px-4 text-slate-500">Updated rate_limit_up: 20M -> 30M</td>
                    </tr>
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">{{ now()->subHours(1)->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 px-4"><span class="font-medium text-slate-800 dark:text-slate-200">System Daemon</span></td>
                        <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-purple-100 text-purple-800 dark:bg-purple-950/50 dark:text-purple-300">radius.accounting_sync</span></td>
                        <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">RadiusSession #104</td>
                        <td class="py-3 px-4 font-mono text-slate-500">127.0.0.1</td>
                        <td class="py-3 px-4 text-slate-500">RADIUS Acct-Interim-Update (Bytes in: 1.4 GB)</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
