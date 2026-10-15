@extends('layouts.app')

@section('content')
<div x-data="{ 
    step: 1, 
    addRouterModal: false, 
    guideExpanded: true,
    copyCode(text, el) {
        navigator.clipboard.writeText(text);
        let orig = el.innerHTML;
        el.innerHTML = '<svg class=\'w-3.5 h-3.5 text-emerald-500\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg><span class=\'text-emerald-500\'>Copied!</span>';
        setTimeout(() => { el.innerHTML = orig; }, 2000);
    }
}" class="space-y-6">

    <!-- Header Section (Matching Screenshot) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <div class="text-blue-600 dark:text-blue-400">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Connect Your MikroTik Router
                </h1>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 max-w-3xl">
                Connect via the RouterOS v7 API &mdash; no scripts on the router. The system talks to your router remotely, so its CPU and memory stay free for serving clients.
            </p>
        </div>

        <div class="flex items-center space-x-2.5 self-start sm:self-auto">
            <button @click="guideExpanded = !guideExpanded" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span x-text="guideExpanded ? 'Collapse Setup Guide' : 'Show Setup Guide'"></span>
            </button>
            <button @click="step = 4; guideExpanded = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-500/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add Router</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Setup Guide Area -->
    <div x-show="guideExpanded" x-collapse.duration.300ms class="space-y-6">

        <!-- Informative Callout Card (Matching Screenshot) -->
        <div class="p-4 sm:p-5 bg-blue-50/70 dark:bg-blue-950/25 border border-blue-100 dark:border-blue-900/40 rounded-2xl flex items-start space-x-3.5 sm:space-x-4">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-xl text-blue-600 dark:text-blue-400 shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </div>
            <div class="space-y-1 text-xs sm:text-sm">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">
                    Why API connection instead of scripts?
                </h3>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    Traditional approaches run scheduler scripts on the router every few minutes, spiking CPU and memory &mdash; especially under heavy client loads. The Smartoh platform keeps all billing, PPPoE, and hotspot logic in the cloud and pushes changes to your router over the RouterOS API only when needed. Your router stays light and your internet quality stays stable.
                </p>
            </div>
        </div>

        <!-- 5-Step Interactive Stepper Bar (Matching Screenshot) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between max-w-4xl mx-auto overflow-x-auto py-2">
                
                <!-- Step 1: Enable API Service -->
                <button @click="step = 1" class="flex flex-col items-center group shrink-0 focus:outline-none">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition"
                         :class="step === 1 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40 shadow-md shadow-blue-600/30' : (step > 1 ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700')">
                        <template x-if="step > 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="step <= 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l3 3-3 3m5 0h3" /></svg>
                        </template>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold mt-2 transition"
                          :class="step === 1 ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        Enable API Service
                    </span>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 bg-slate-200 dark:bg-slate-800" :class="step > 1 ? 'bg-emerald-500 dark:bg-emerald-500' : ''"></div>

                <!-- Step 2: Create API User -->
                <button @click="step = 2" class="flex flex-col items-center group shrink-0 focus:outline-none">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition"
                         :class="step === 2 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40 shadow-md shadow-blue-600/30' : (step > 2 ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700')">
                        <template x-if="step > 2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="step <= 2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                        </template>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold mt-2 transition"
                          :class="step === 2 ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        Create API User
                    </span>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 bg-slate-200 dark:bg-slate-800" :class="step > 2 ? 'bg-emerald-500 dark:bg-emerald-500' : ''"></div>

                <!-- Step 3: Firewall & Access -->
                <button @click="step = 3" class="flex flex-col items-center group shrink-0 focus:outline-none">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition"
                         :class="step === 3 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40 shadow-md shadow-blue-600/30' : (step > 3 ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700')">
                        <template x-if="step > 3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="step <= 3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </template>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold mt-2 transition"
                          :class="step === 3 ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        Firewall &amp; Access
                    </span>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 bg-slate-200 dark:bg-slate-800" :class="step > 3 ? 'bg-emerald-500 dark:bg-emerald-500' : ''"></div>

                <!-- Step 4: Connect Router -->
                <button @click="step = 4" class="flex flex-col items-center group shrink-0 focus:outline-none">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition"
                         :class="step === 4 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40 shadow-md shadow-blue-600/30' : (step > 4 ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700')">
                        <template x-if="step > 4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="step <= 4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </template>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold mt-2 transition"
                          :class="step === 4 ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        Connect Router
                    </span>
                </button>

                <div class="flex-1 h-0.5 mx-2 sm:mx-4 bg-slate-200 dark:bg-slate-800" :class="step > 4 ? 'bg-emerald-500 dark:bg-emerald-500' : ''"></div>

                <!-- Step 5: Verify -->
                <button @click="step = 5" class="flex flex-col items-center group shrink-0 focus:outline-none">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition"
                         :class="step === 5 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/40 shadow-md shadow-blue-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[11px] sm:text-xs font-semibold mt-2 transition"
                          :class="step === 5 ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400'">
                        Verify
                    </span>
                </button>

            </div>
        </div>

        <!-- Stepper Detail Cards (Matching Screenshot) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-sm transition">

            <!-- STEP 1 CONTENT: Enable API Service -->
            <div x-show="step === 1" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-mono font-bold text-sm">
                        &gt;_
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                        Enable the API Service on Your Router
                    </h2>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Log in to your MikroTik router (Winbox, WebFig, or terminal) and enable the <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-blue-600 dark:text-blue-400 text-xs">api</code> service. This opens TCP port 8728 so the platform can send commands to the router without running any scripts on it.
                </p>

                <!-- Code Terminal Box (Matching Screenshot) -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-950 text-slate-100">
                    <div class="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">In the MikroTik terminal (New Terminal):</span>
                        <button type="button" @click="copyCode('/ip service enable api\n/ip service set api port=8728', $el)" class="flex items-center space-x-1.5 text-slate-400 hover:text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Copy</span>
                        </button>
                    </div>
                    <pre class="p-4 font-mono text-xs sm:text-sm leading-relaxed overflow-x-auto text-emerald-400"><code>/ip service enable api
/ip service set api port=8728</code></pre>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <div class="text-[11px] text-slate-500">
                        Port 8728 is standard for RouterOS v7 API. For TLS encryption, port 8729 (api-ssl) is also supported.
                    </div>
                    <button type="button" @click="step = 2" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                        <span>Next: Create API User</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2 CONTENT: Create API User -->
            <div x-show="step === 2" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-mono font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                        Create a Dedicated API User &amp; Group
                    </h2>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Create a dedicated RouterOS group with non-interactive policies (API, read, write, test). This ensures the Smartoh cloud platform can provision PPPoE queues and vouchers safely without exposing full admin WinBox or console access.
                </p>

                <!-- Code Terminal Box -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-950 text-slate-100">
                    <div class="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">In the MikroTik terminal (New Terminal):</span>
                        <button type="button" @click="copyCode('/user group add name=smartoh-api policy=api,read,write,test,password,!local,!telnet,!ssh,!ftp,!reboot,!policy,!winbox,!sensitive,!sniff,!romon\n/user add name=api_smartoh group=smartoh-api password=&quot;SmartohPass2026!&quot; comment=&quot;Smartoh Cloud Platform API Connector&quot;', $el)" class="flex items-center space-x-1.5 text-slate-400 hover:text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Copy</span>
                        </button>
                    </div>
                    <pre class="p-4 font-mono text-xs sm:text-sm leading-relaxed overflow-x-auto text-emerald-400"><code>/user group add name=smartoh-api policy=api,read,write,test,password,!local,!telnet,!ssh,!ftp,!reboot,!policy,!winbox,!sensitive,!sniff,!romon
/user add name=api_smartoh group=smartoh-api password="SmartohPass2026!" comment="Smartoh Cloud Platform API Connector"</code></pre>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <button type="button" @click="step = 1" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span>Previous</span>
                    </button>
                    <button type="button" @click="step = 3" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                        <span>Next: Firewall &amp; Access</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3 CONTENT: Firewall & Access -->
            <div x-show="step === 3" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-mono font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                        Allow API Access Through Firewall
                    </h2>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    If your router has input firewall filters on the WAN interface, add an accept rule so Smartoh can communicate with port 8728. If your router is on a private CGNAT IP, you can connect it via WireGuard VPN.
                </p>

                <!-- Code Terminal Box -->
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-slate-950 text-slate-100">
                    <div class="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">In the MikroTik terminal (New Terminal):</span>
                        <button type="button" @click="copyCode('/ip firewall filter add chain=input protocol=tcp dst-port=8728 action=accept comment=&quot;Allow Smartoh RouterOS API&quot; place-before=1', $el)" class="flex items-center space-x-1.5 text-slate-400 hover:text-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Copy</span>
                        </button>
                    </div>
                    <pre class="p-4 font-mono text-xs sm:text-sm leading-relaxed overflow-x-auto text-emerald-400"><code>/ip firewall filter add chain=input protocol=tcp dst-port=8728 action=accept comment="Allow Smartoh RouterOS API" place-before=1</code></pre>
                </div>

                <div class="flex items-center justify-between pt-3">
                    <button type="button" @click="step = 2" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span>Previous</span>
                    </button>
                    <button type="button" @click="step = 4" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                        <span>Next: Connect Router</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4 CONTENT: Connect Router (Enrollment Form) -->
            <div x-show="step === 4" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-mono font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                        Enter Router Connection Credentials
                    </h2>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Provide the IP address and API credentials created in steps 1 and 2. Smartoh will test the API handshake and immediately register the router in your inventory.
                </p>

                <!-- Router Connection Form -->
                <form method="POST" action="{{ route('network.mikrotik.store') }}" class="p-5 bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-xl space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Router Display Name *</label>
                            <input type="text" name="name" placeholder="e.g. Core-CCR2004-Gateway" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Site / POP Location</label>
                            <select name="site_id" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Default Site</option>
                                @foreach($sites as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->location }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Router IP Address or Domain *</label>
                            <input type="text" name="ip_address" placeholder="192.168.88.1 or gateway.isp.co.ke" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">API Port *</label>
                            <input type="number" name="api_port" value="8728" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">API Username *</label>
                            <input type="text" name="api_username" value="api_smartoh" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">API Password *</label>
                            <input type="password" name="api_password" placeholder="Enter password from Step 2" required class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Hardware Model</label>
                            <input type="text" name="model" placeholder="CCR2004-16G-2S+ or hAP ax³" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">RouterOS Version</label>
                            <input type="text" name="routeros_version" value="v7.16" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="step = 3" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            <span>Previous</span>
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 flex items-center space-x-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Connect &amp; Validate Router</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 5 CONTENT: Verify & Health Status -->
            <div x-show="step === 5" class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-mono font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                        Verify Connection &amp; Live Telemetry
                    </h2>
                </div>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Once enrolled, your routers appear in the live dashboard below. You can trigger on-demand ping handshakes, review CPU load, monitor PPPoE sessions, and view active interfaces.
                </p>

                <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-bold text-emerald-900 dark:text-emerald-300">
                            Currently Managing {{ $routers->count() }} MikroTik Device(s) via RouterOS v7 API
                        </span>
                    </div>
                    <button type="button" @click="step = 4" class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline">
                        + Add Another Router
                    </button>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="step = 4" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span>Previous</span>
                    </button>
                    <button type="button" @click="step = 1" class="px-3.5 py-2 text-blue-600 dark:text-blue-400 font-semibold text-xs hover:underline">
                        Restart Setup Guide &rarr;
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Active Connected Routers Fleet Grid -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                <span>Active Router Fleet</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold">
                    {{ $routers->count() }} Online
                </span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($routers as $r)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4 relative overflow-hidden transition hover:border-blue-400 dark:hover:border-blue-500/50">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">{{ $r->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-mono">ONLINE</span>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $r->ip_address }}:{{ $r->api_port }} &bull; {{ $r->site?->name ?? 'Default Site' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1.5">
                        <form method="POST" action="{{ route('network.mikrotik.test', $r) }}">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-cyan-500 dark:hover:text-cyan-400 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700/60 transition" title="Test API Connection">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('network.mikrotik.reboot', $r) }}" onsubmit="return confirm('Reboot router {{ $r->name }}? This will momentarily drop active connections.')">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700/60 transition" title="Reboot Router">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Telemetry Stats -->
                <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 rounded-xl text-center font-mono text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase">CPU Load</div>
                        <div class="text-sm font-bold text-cyan-600 dark:text-cyan-400">{{ $r->cpu_load }}%</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase">Hardware / OS</div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $r->model }}</div>
                        <div class="text-[10px] text-slate-500">{{ $r->routeros_version }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 uppercase">Subscribers</div>
                        <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ $r->active_pppoe_sessions }} PPPoE</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 p-8 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 rounded-2xl">
                <svg class="w-10 h-10 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                <div class="font-bold text-slate-800 dark:text-white">No MikroTik Routers Connected</div>
                <p class="text-xs text-slate-500 mt-1">Follow the 5-step guide above to connect your first RouterOS device.</p>
                <button type="button" @click="step = 1; guideExpanded = true" class="mt-3 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl">Start Step 1</button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Live Interfaces Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Live Router Interfaces &amp; Throughput</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Current interface traffic on {{ $activeRouter?->name ?? 'Core Router' }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/50 font-mono">POLLING 1s</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Interface Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Link State</th>
                        <th class="p-4">RX (Download)</th>
                        <th class="p-4">TX (Upload)</th>
                        <th class="p-4">MAC Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($interfaces as $iface)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                        <td class="p-4 font-bold text-cyan-600 dark:text-cyan-400">{{ $iface['name'] }}</td>
                        <td class="p-4 uppercase text-slate-500 dark:text-slate-400">{{ $iface['type'] }}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center space-x-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>RUNNING</span>
                            </span>
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">{{ $iface['rx_rate'] }}</td>
                        <td class="p-4 font-bold text-blue-600 dark:text-blue-400">{{ $iface['tx_rate'] }}</td>
                        <td class="p-4 text-slate-400">{{ $iface['mac'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-500 font-sans">No interfaces mapped.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- BGP & OSPF Routing Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- BGP Peers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3 font-mono">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">BGP Transit Peering (RouterOS v7)</h3>
                <span class="text-xs text-cyan-600 dark:text-cyan-400 font-bold">AS 37492</span>
            </div>
            <div class="space-y-2">
                @forelse($bgpPeers as $bgp)
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs space-y-1">
                    <div class="flex justify-between font-bold">
                        <span class="text-slate-900 dark:text-white">{{ $bgp->peer_name }}</span>
                        <span class="text-emerald-600 dark:text-emerald-400 uppercase">{{ $bgp->status }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex justify-between">
                        <span>Peer IP: {{ $bgp->peer_address }}</span>
                        <span>Remote AS: {{ $bgp->peer_as }}</span>
                    </div>
                </div>
                @empty
                <div class="text-slate-500 text-xs text-center py-4 font-sans">No BGP peers configured.</div>
                @endforelse
            </div>
        </div>

        <!-- OSPF Areas -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3 font-mono">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-sans">OSPF Dynamic Routing</h3>
                <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">Backbone Area 0</span>
            </div>
            <div class="space-y-2">
                @forelse($ospfAreas as $ospf)
                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs space-y-1">
                    <div class="flex justify-between font-bold">
                        <span class="text-slate-900 dark:text-white">Interface: {{ $ospf->interface }}</span>
                        <span class="text-cyan-600 dark:text-cyan-400">Cost: {{ $ospf->cost }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Router ID: {{ $ospf->router_id }} &bull; Area: {{ $ospf->area }}</div>
                </div>
                @empty
                <div class="text-slate-500 text-xs text-center py-4 font-sans">No OSPF areas configured.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
