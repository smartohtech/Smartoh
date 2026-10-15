@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider font-mono">Security Telemetry</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 font-mono">MNDP &amp; ARP Sniffer</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Rogue Router Detection &amp; Quarantine</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated detection of rogue DHCP servers, unauthenticated customer routers, and unauthorized bridge endpoints.</p>
        </div>
    </div>

    <!-- Detected Routers Table (Matching Base44 RouterDetection.js) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Discovered Network Hardware</h3>
            <span class="px-2.5 py-0.5 rounded text-xs font-mono bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                Passive ARP Monitoring
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4">MAC Address</th>
                        <th class="p-4">Discovered IP</th>
                        <th class="p-4">Hardware Vendor</th>
                        <th class="p-4">Detection Vector</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Discovered</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($detectedRouters as $r)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-slate-900 dark:text-white">{{ $r->mac_address }}</td>
                        <td class="p-4 text-blue-600 dark:text-blue-400">{{ $r->ip_address }}</td>
                        <td class="p-4 font-sans text-slate-800 dark:text-slate-200">{{ $r->vendor }}</td>
                        <td class="p-4 font-sans text-slate-500 dark:text-slate-400 text-[11px]">{{ $r->discovered_via }}</td>
                        <td class="p-4">
                            @if($r->status === 'authenticated')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase">Authenticated</span>
                            @elseif($r->status === 'denied')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 uppercase">Blocked</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase">Pending Review</span>
                            @endif
                        </td>
                        <td class="p-4 font-sans text-slate-400">{{ $r->detected_at }}</td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <form method="POST" action="{{ route('router.detection.action') }}">
                                    @csrf
                                    <input type="hidden" name="mac_address" value="{{ $r->mac_address }}">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-semibold transition">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('router.detection.action') }}">
                                    @csrf
                                    <input type="hidden" name="mac_address" value="{{ $r->mac_address }}">
                                    <input type="hidden" name="action" value="block">
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded-lg text-xs font-semibold transition">
                                        Quarantine
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
