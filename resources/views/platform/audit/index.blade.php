@extends('layouts.platform')

@section('title', 'Platform Security & Audit Logs')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Security &amp; Administrative Audit Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Immutable forensic trail recording approvals, rejections, pricing overrides, suspensions, and tenant actions.</p>
        </div>
    </div>

    <!-- Filter Strip -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('platform.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search details, admin name, or IP address..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono text-xs">
            </div>

            <div>
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action code (e.g. APPROVED, SUSPENDED)..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono text-xs">
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl text-xs transition">
                    Filter Logs
                </button>
                <a href="{{ route('platform.audit.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden font-mono text-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-950/80 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Timestamp (UTC)</th>
                        <th class="py-3 px-4">Action Code</th>
                        <th class="py-3 px-4">Administrator / User</th>
                        <th class="py-3 px-4">Tenant Scope</th>
                        <th class="py-3 px-4">Audit Details</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-800/30 transition text-[11px]">
                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-cyan-300 font-bold border border-slate-700">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-200 whitespace-nowrap">
                            {{ $log->user->name ?? 'System Service' }}
                            @if($log->user)
                            <div class="text-[10px] text-slate-500">{{ $log->user->email }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap">
                            {{ $log->tenant->name ?? 'Global Platform' }}
                        </td>
                        <td class="py-3 px-4 text-slate-300 max-w-md truncate font-sans">
                            {{ $log->details }}
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-[10px] whitespace-nowrap">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500 text-xs">
                            No audit logs match the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
