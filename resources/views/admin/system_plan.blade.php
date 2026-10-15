@extends('layouts.app')

@section('title', 'Platform Subscription & Plan Quotas')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Subscription &amp; System Quotas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage your ISP license tier, monitor resource capacities, and adjust platform limits.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Plan Status: Active Enterprise
            </span>
            <button type="button" onclick="alert('Contacting Smartoh billing sales...')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                Upgrade Capacity
            </button>
        </div>
    </div>

    <!-- Active Tier Quota Meters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Subscribers Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Active Subscribers</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">412 / 2,500</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: 16.5%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>16.5% consumed</span>
                <span class="text-emerald-500 font-medium">2,088 remaining</span>
            </div>
        </div>

        <!-- MikroTik Routers Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">MikroTik RouterOS Fleet</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">4 / 25</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 16%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>16% consumed</span>
                <span class="text-emerald-500 font-medium">21 routers open</span>
            </div>
        </div>

        <!-- TR-069 CPE Devices Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">TR-069 CWMP Devices</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">186 / 1,000</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-cyan-500 h-2.5 rounded-full" style="width: 18.6%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>18.6% consumed</span>
                <span class="text-emerald-500 font-medium">814 CPEs open</span>
            </div>
        </div>

        <!-- Cloud Storage & Audit -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Telemetry &amp; Backups</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">12.4 GB / 50 GB</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: 24.8%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>24.8% consumed</span>
                <span class="text-emerald-500 font-medium">37.6 GB free</span>
            </div>
        </div>
    </div>

    <!-- Pricing / License Tiers Grid -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm space-y-6">
        <div class="text-center max-w-xl mx-auto">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Smartoh Technologies Tier Comparison</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Scale your WISP / FTTH operation with unlimited telemetry, automated M-Pesa billing, and multi-NAS AAA.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">

            <!-- Starter Tier -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Startup ISP</span>
                    <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">KES 4,500 <span class="text-xs font-medium text-slate-400">/ mo</span></div>
                    <p class="text-xs text-slate-500 mt-2">For local wireless setups &amp; building networks.</p>

                    <ul class="mt-6 space-y-3 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>300 Subscribers</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>3 MikroTik NAS Routers</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Daraja M-Pesa STK Gateway</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>SMS Reminder System</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                        Downgrade to Starter
                    </button>
                </div>
            </div>

            <!-- Professional / Current Tier -->
            <div class="rounded-2xl border-2 border-blue-600 bg-blue-50/20 dark:bg-blue-950/20 p-6 flex flex-col justify-between relative shadow-lg shadow-blue-500/10">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-blue-600 text-white text-[10px] font-bold uppercase rounded-full tracking-wider shadow">
                    Current Active Tier
                </div>

                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Enterprise ISP</span>
                    <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">KES 14,000 <span class="text-xs font-medium text-slate-400">/ mo</span></div>
                    <p class="text-xs text-slate-500 mt-2">For high-throughput FTTH, GPON &amp; multi-tower operations.</p>

                    <ul class="mt-6 space-y-3 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>2,500 Subscribers</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>25 MikroTik &amp; Huawei Routers</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>TR-069 ACS + TR-181 Auto-Provisioning</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>OSPF &amp; BGP Autonomous Routing</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Predictive Failure AI &amp; Rogue Sniffer</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 pt-4 border-t border-blue-200 dark:border-blue-900/60">
                    <button type="button" disabled class="w-full py-2.5 bg-blue-600/50 text-white text-xs font-bold rounded-xl cursor-default">
                        Current Plan
                    </button>
                </div>
            </div>

            <!-- Carrier Tier -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Carrier / Multi-Tenant</span>
                    <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">KES 35,000 <span class="text-xs font-medium text-slate-400">/ mo</span></div>
                    <p class="text-xs text-slate-500 mt-2">For regional telecom providers and franchise operators.</p>

                    <ul class="mt-6 space-y-3 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>Unlimited Subscribers</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><strong>Unlimited Core Routers &amp; OLTs</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Wholesale Marketplace Pools</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Multi-ISP Tenant White-Labeling</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Dedicated 24/7 NOC Line &amp; SLA</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="alert('Contacting Smartoh sales for Carrier upgrade!')" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold rounded-xl transition shadow">
                        Upgrade to Carrier
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
