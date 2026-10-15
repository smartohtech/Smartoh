@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-[1600px] mx-auto">

    <!-- Top Headline Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center space-x-3.5">
            <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Technologies" class="w-12 h-12 rounded-xl object-contain bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-1 shadow-sm shrink-0">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Operations Command</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Live Telemetry Active</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Smartoh Technologies Dashboard</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time network client monitoring, revenue analytics, and remote device management.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <a href="{{ route('clients.index') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add Subscriber</span>
            </a>
            <a href="{{ route('billing.payments') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                <span>Trigger M-Pesa STK</span>
            </a>
        </div>
    </div>

    <!-- 4 Primary KPI Metric Cards (Matching Base44 Dashboard ke) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Clients -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    <span>+12.4%</span>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-4">{{ $totalClients }}</p>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Subscribers &amp; Clients</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $activeClients }} active · {{ $expiringClients }} expiring</p>
        </div>

        <!-- Network Devices -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                    <span>{{ round(($onlineDevices / max(1, $totalDevices)) * 100) }}% Up</span>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-4">{{ $totalDevices }}</p>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Network Devices</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $onlineDevices }} online · {{ $offlineDevices }} offline</p>
        </div>

        <!-- Offline APs / Hardware Attention -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-full">
                    <span>Critical</span>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-4">{{ $offlineAps }}</p>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Offline APs &amp; Routers</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Requires immediate attention</p>
        </div>

        <!-- Open Incidents -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="w-11 h-11 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded-full">
                    <span>Active</span>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-4">{{ $openIncidents }}</p>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Open Incidents</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">NOC monitoring &amp; SLA tracking</p>
        </div>

    </div>

    <!-- Row 2: Payment Revenue by Method & SMS Usage (Bar Charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Payment Revenue Bar Chart -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Payment Revenue</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Monthly revenue stacked by payment method (KES)</p>
                </div>
                <div class="flex items-center space-x-3 text-xs font-medium">
                    <span class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span><span class="text-slate-600 dark:text-slate-400">M-Pesa</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-600"></span><span class="text-slate-600 dark:text-slate-400">Card</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-500"></span><span class="text-slate-600 dark:text-slate-400">Cash</span></span>
                </div>
            </div>
            <div class="h-64 relative">
                <canvas id="paymentRevenueChart"></canvas>
            </div>
        </div>

        <!-- SMS Usage Bar Chart -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">SMS &amp; Alert Delivery</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Weekly SMS sent by type (Notifications, Vouchers, Reminders)</p>
                </div>
                <div class="flex items-center space-x-2 text-xs text-blue-600 dark:text-blue-400 font-medium">
                    <span>99.8% Delivered</span>
                </div>
            </div>
            <div class="h-64 relative">
                <canvas id="smsUsageChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Row 3: Revenue Trends Section (me component from Base44) -->
    <div class="space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pt-2">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    <span>Revenue Trends</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Income growth over time, broken down by payment source</p>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <p class="text-[11px] text-slate-400">This Month</p>
                    <p class="text-base font-bold text-slate-900 dark:text-white">KES {{ number_format($monthlyRevenue, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-slate-400">vs Last Month</p>
                    <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        <span>14.8%</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 3 Cards for M-Pesa, Card, Cash -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- M-Pesa Card -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white text-xs bg-emerald-600 shadow-sm shadow-emerald-600/20">
                            M
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Safaricom M-Pesa</span>
                    </div>
                    <span class="flex items-center gap-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        <span>16.2%</span>
                    </span>
                </div>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-3 font-mono">KES {{ number_format($mpesaTotal, 2) }}</p>
                <div class="flex items-center justify-between mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $mpesaShare }}% of total revenue</span>
                    <span class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400">Primary Channel</span>
                </div>
            </div>

            <!-- Card Payments -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white text-xs bg-blue-600 shadow-sm shadow-blue-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Visa / Mastercard</span>
                    </div>
                    <span class="flex items-center gap-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        <span>8.1%</span>
                    </span>
                </div>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-3 font-mono">KES {{ number_format($cardTotal, 2) }}</p>
                <div class="flex items-center justify-between mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $cardShare }}% of total revenue</span>
                    <span class="text-[11px] font-mono text-blue-600 dark:text-blue-400">Online Gateway</span>
                </div>
            </div>

            <!-- Cash / Bank Till -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white text-xs bg-amber-500 shadow-sm shadow-amber-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">Cash &amp; Bank Till</span>
                    </div>
                    <span class="flex items-center gap-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        <span>3.5%</span>
                    </span>
                </div>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-3 font-mono">KES {{ number_format($cashTotal, 2) }}</p>
                <div class="flex items-center justify-between mt-1 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $cashShare }}% of total revenue</span>
                    <span class="text-[11px] font-mono text-amber-500">Manual Recon</span>
                </div>
            </div>

        </div>

        <!-- Stacked Area Chart (2 cols) & Revenue Share Donut (1 col) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">Revenue by Payment Source</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Monthly income stacked by M-Pesa, Card &amp; Cash</p>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">Past 6 Months</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="stackedAreaRevenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1">Revenue Share</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Distribution across payment methods</p>
                    <div class="h-44 relative flex items-center justify-center">
                        <canvas id="revenueShareDonutChart"></canvas>
                    </div>
                </div>

                <div class="space-y-2 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>M-Pesa</span>
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $mpesaShare }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span>Card</span>
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $cardShare }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Cash</span>
                        </span>
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $cashShare }}%</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Row 4: Real-time Bandwidth (2 cols) & Network Devices Table (1 col) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Real-time Bandwidth Chart -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 lg:col-span-2 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Real-time Bandwidth Throughput</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Aggregate network throughput (Mbps) from MikroTik queue trees</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-mono font-medium">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span><span class="text-slate-700 dark:text-slate-300">Download</span></span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span><span class="text-slate-700 dark:text-slate-300">Upload</span></span>
                </div>
            </div>
            <div class="h-60 relative">
                <canvas id="realtimeThroughputChart"></canvas>
            </div>
        </div>

        <!-- Network Devices List -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Network Devices</h3>
                    <a href="{{ route('network.mikrotik') }}" class="text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">View all</a>
                </div>

                <div class="space-y-2.5 max-h-[260px] overflow-y-auto">
                    @forelse($devicesList as $dev)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $dev->name }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $dev->type }} · {{ $dev->ip_address }}</p>
                            </div>
                        </div>
                        @if($dev->status === 'online')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase">Online</span>
                        @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 uppercase">Offline</span>
                        @endif
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-400 text-xs">No devices enrolled.</div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 mt-3 text-[11px] text-slate-400 flex items-center justify-between font-mono">
                <span>{{ $onlineDevices }}/{{ $totalDevices }} Devices Online</span>
                <span class="text-emerald-600 dark:text-emerald-400">RouterOS v7 Synced</span>
            </div>
        </div>

    </div>

    <!-- Row 5: Recent Tickets & NOC Incidents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Tickets -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Recent Support Tickets</h3>
                <a href="{{ route('noc.index') }}" class="text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">View all</a>
            </div>

            <div class="space-y-3">
                @forelse($recentTickets as $tick)
                <div class="flex items-start justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800/80 hover:border-slate-200 dark:hover:border-slate-700 transition">
                    <div class="flex-1 min-w-0 pr-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] font-mono text-blue-600 dark:text-blue-400 font-semibold">{{ $tick->ticket_number }}</span>
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $tick->subject }}</p>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $tick->client ? $tick->client->first_name . ' ' . $tick->client->last_name : 'System Alert' }} · Opened {{ $tick->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        @php
                            $ticketPills = [
                                'open' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                                'in_progress' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                                'resolved' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
                            ][$tick->status] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $ticketPills }}">
                            {{ str_replace('_', ' ', $tick->status) }}
                        </span>
                        <span class="text-[10px] text-slate-400 capitalize font-mono">{{ $tick->priority }} Priority</span>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">No pending trouble tickets.</div>
                @endforelse
            </div>
        </div>

        <!-- NOC Incidents -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active NOC Alarms</h3>
                <a href="{{ route('noc.index') }}" class="text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">View NOC Board</a>
            </div>

            <div class="space-y-3">
                @forelse($recentIncidents as $inc)
                <div class="flex items-start justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800/80 hover:border-slate-200 dark:hover:border-slate-700 transition">
                    <div class="flex-1 min-w-0 pr-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] font-mono text-cyan-600 dark:text-cyan-400 font-semibold">{{ $inc->incident_number }}</span>
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $inc->title }}</p>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Target: <span class="font-mono text-slate-700 dark:text-slate-300">{{ $inc->affected_component }}</span> · {{ $inc->started_at ? $inc->started_at->diffForHumans() : 'Just now' }}
                        </p>
                    </div>
                    <div>
                        @if($inc->severity === 'critical')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 uppercase">Critical</span>
                        @elseif($inc->severity === 'warning')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase">Warning</span>
                        @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 uppercase">Info</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">No active network incidents. All POP links healthy.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<!-- Interactive Chart.js Scripts matching Smartoh Technologies ISP Platform -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? '#1e293b' : '#f1f5f9';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // 1. Payment Revenue Stacked Bar Chart
    const payCtx = document.getElementById('paymentRevenueChart')?.getContext('2d');
    if (payCtx) {
        new Chart(payCtx, {
            type: 'bar',
            data: {
                labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'M-Pesa',
                        data: [120000, 145000, 160000, 172000, 180000, 195000],
                        backgroundColor: '#16a34a',
                        borderRadius: 4
                    },
                    {
                        label: 'Card',
                        data: [30000, 35000, 38000, 41000, 40000, 45000],
                        backgroundColor: '#1565C0',
                        borderRadius: 4
                    },
                    {
                        label: 'Cash',
                        data: [12000, 15000, 14000, 16000, 15000, 18000],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                    y: { stacked: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => (v/1000) + 'k' } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 2. SMS Usage Bar Chart
    const smsCtx = document.getElementById('smsUsageChart')?.getContext('2d');
    if (smsCtx) {
        new Chart(smsCtx, {
            type: 'bar',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4'],
                datasets: [
                    { label: 'Notifications', data: [1200, 1450, 1300, 1600], backgroundColor: '#1565C0', borderRadius: 4 },
                    { label: 'Vouchers', data: [600, 800, 750, 920], backgroundColor: '#8b5cf6', borderRadius: 4 },
                    { label: 'Reminders', data: [400, 550, 500, 680], backgroundColor: '#38bdf8', borderRadius: 4 },
                    { label: 'Alerts', data: [120, 90, 80, 110], backgroundColor: '#ef4444', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, color: textColor, font: { size: 10 } } }
                }
            }
        });
    }

    // 3. Stacked Area Revenue Chart (Gradients)
    const areaCtx = document.getElementById('stackedAreaRevenueChart')?.getContext('2d');
    if (areaCtx) {
        new Chart(areaCtx, {
            type: 'line',
            data: {
                labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'M-Pesa',
                        data: [120000, 145000, 160000, 172000, 180000, 195000],
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.15)',
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Card',
                        data: [30000, 35000, 38000, 41000, 40000, 45000],
                        borderColor: '#1565C0',
                        backgroundColor: 'rgba(21, 101, 192, 0.12)',
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Cash',
                        data: [12000, 15000, 14000, 16000, 15000, 18000],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => (v/1000) + 'k' } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 4. Revenue Share Donut Chart
    const donutCtx = document.getElementById('revenueShareDonutChart')?.getContext('2d');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['M-Pesa', 'Card', 'Cash'],
                datasets: [{
                    data: [{{ $mpesaShare }}, {{ $cardShare }}, {{ $cashShare }}],
                    backgroundColor: ['#16a34a', '#1565C0', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 5. Real-time Throughput Chart
    const tpCtx = document.getElementById('realtimeThroughputChart')?.getContext('2d');
    if (tpCtx) {
        new Chart(tpCtx, {
            type: 'line',
            data: {
                labels: ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'],
                datasets: [
                    {
                        label: 'Download (Mbps)',
                        data: [420, 310, 580, 1420, 1850, 1920, 2450, 2180],
                        borderColor: '#1565C0',
                        backgroundColor: 'rgba(21, 101, 192, 0.15)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Upload (Mbps)',
                        data: [150, 110, 230, 680, 890, 940, 1120, 980],
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endsection
