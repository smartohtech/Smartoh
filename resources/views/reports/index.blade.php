@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Executive Intelligence</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Financial Analytics</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Advanced Reports &amp; ARPU Intelligence</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Revenue, ARPU, churn &amp; plan analytics, subscriber lifetime value, and capacity utilization reports.</p>
        </div>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 flex items-center space-x-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <span>Print / Export PDF</span>
        </button>
    </div>

    <!-- 4 Executive KPI Cards (Matching Base44 AdvancedReports.js) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Monthly Revenue</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 font-mono">KES {{ number_format($totalRevenue) }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-medium">+14.8% vs last month</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Average Revenue Per User (ARPU)</span>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 font-mono">KES {{ number_format($arpu, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Blended residential &amp; business</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Subscribers</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 font-mono">{{ $activeClients }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Paying subscriber base</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Churn Rate</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 font-mono">{{ $churnRate }}%</div>
            <div class="text-xs text-slate-400 mt-1">Healthy benchmark (&lt;3.5%)</div>
        </div>

    </div>

    <!-- Plan Adoption Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Plan Adoption &amp; Revenue Breakdown</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Distribution of active subscribers per service package</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Billing Plan</th>
                        <th class="p-4">Speed Profile</th>
                        <th class="p-4">Monthly Rate</th>
                        <th class="p-4">Active Clients</th>
                        <th class="p-4">Monthly Yield</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($plans as $pl)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-sans font-bold text-slate-900 dark:text-white">{{ $pl->name }}</td>
                        <td class="p-4 text-blue-600 dark:text-blue-400">{{ round($pl->download_speed_kbps / 1024) }} Mbps ↓ / {{ round($pl->upload_speed_kbps / 1024) }} Mbps ↑</td>
                        <td class="p-4">KES {{ number_format($pl->price) }}</td>
                        <td class="p-4 text-slate-800 dark:text-slate-200 font-bold">{{ $pl->clients_count }} clients</td>
                        <td class="p-4 text-emerald-600 dark:text-emerald-400 font-bold">KES {{ number_format($pl->clients_count * $pl->price) }}/mo</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
