@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ configModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Access Management</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Port 1812/1813 Active</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">AAA / RADIUS Server Management</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Centralized subscriber authentication, real-time accounting, CoA disconnect, and anti-sharing enforcement.</p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="configModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span>RADIUS Settings</span>
            </button>
        </div>
    </div>

    <!-- Overview Spec Grid (Matching Base44 Radius.js) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Router Integration</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $config->bridge_name }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400 font-mono mt-0.5">{{ $config->bridge_ip }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Anti-Sharing Protection</span>
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                <span>Active (TTL = 1)</span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Blocks downstream unauthorized Wi-Fi sharing</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active RADIUS Sessions</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1 font-mono">{{ $activeSessions->count() }} Online</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Real-time interim accounting</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Persistence Engine</span>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">Auto-Reconnect</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Session recovery on power cycle</div>
        </div>

    </div>

    <!-- Active RADIUS Sessions Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active RADIUS &amp; PPPoE Sessions</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Live accounting packets received via RADIUS port 1813</p>
            </div>
            <span class="px-2.5 py-1 rounded text-xs font-mono bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                NAS IP: 10.0.0.1
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Session ID</th>
                        <th class="p-4">Subscriber / Username</th>
                        <th class="p-4">Framed IP &amp; MAC</th>
                        <th class="p-4">Plan &amp; Speed</th>
                        <th class="p-4">Session Uptime</th>
                        <th class="p-4">Traffic (Down / Up)</th>
                        <th class="p-4 text-right">CoA Disconnect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($activeSessions as $ses)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 text-blue-600 dark:text-blue-400 font-semibold">{{ $ses->session_id }}</td>
                        <td class="p-4">
                            <div class="font-sans font-semibold text-slate-900 dark:text-white">{{ $ses->username }}</div>
                            <div class="text-[10px] text-slate-400 uppercase font-mono">{{ $ses->service }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-slate-800 dark:text-slate-200">{{ $ses->ip_address }}</div>
                            <div class="text-[10px] text-slate-400">{{ $ses->mac_address }}</div>
                        </td>
                        <td class="p-4 font-sans text-slate-700 dark:text-slate-300">
                            {{ $ses->plan_name }}
                        </td>
                        <td class="p-4 text-emerald-600 dark:text-emerald-400">
                            {{ $ses->uptime }}
                        </td>
                        <td class="p-4 text-slate-800 dark:text-slate-200">
                            <span class="text-blue-600 dark:text-blue-400">{{ $ses->download_mb }} MB</span> ↓ / 
                            <span class="text-cyan-600 dark:text-cyan-400">{{ $ses->upload_mb }} MB</span> ↑
                        </td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('radius.disconnect') }}" class="inline">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $ses->session_id }}">
                                <input type="hidden" name="username" value="{{ $ses->username }}">
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-semibold transition" title="Send CoA Disconnect Request">
                                    Drop Session
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 font-sans">No active subscriber sessions detected.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RADIUS Settings Modal -->
    <div x-show="configModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="configModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Configure RADIUS &amp; Anti-Sharing</h3>
                <button @click="configModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('radius.config') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Bridge Name *</label>
                    <input type="text" name="bridge_name" value="{{ $config->bridge_name }}" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Bridge IP / Subnet *</label>
                    <input type="text" name="bridge_ip" value="{{ $config->bridge_ip }}" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2 border border-slate-200 dark:border-slate-700">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="anti_sharing" value="1" checked class="rounded bg-slate-100 dark:bg-slate-800 text-blue-600 focus:ring-blue-500">
                        <span class="font-medium text-slate-800 dark:text-slate-200">Enforce Anti-Sharing (TTL Lock = 1)</span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 pl-6">Prevents subscribers from attaching unauthorized secondary Wi-Fi routers.</p>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2 border border-slate-200 dark:border-slate-700">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="auto_reconnect" value="1" checked class="rounded bg-slate-100 dark:bg-slate-800 text-blue-600 focus:ring-blue-500">
                        <span class="font-medium text-slate-800 dark:text-slate-200">Session Persistence &amp; Auto-Reconnect</span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 pl-6">Restores subscriber IP leases automatically when power is restored.</p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="configModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Save RADIUS Rules
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
