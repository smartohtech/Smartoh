<div class="flex flex-col h-full overflow-hidden">

    <!-- Brand & Logo Header -->
    <div class="h-14 shrink-0 px-4 border-b border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group min-w-0">
            <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Technologies Logo" class="w-8 h-8 rounded-lg object-contain bg-white dark:bg-slate-950 shadow-md shadow-blue-500/10 shrink-0 group-hover:scale-105 transition-transform border border-slate-200 dark:border-slate-800 p-0.5">
            <div class="min-w-0">
                <h1 class="text-sm font-bold text-slate-900 dark:text-white truncate tracking-tight">
                    {{ $currentTenant->name ?? 'Smartoh Technologies' }}
                </h1>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate font-mono">ISP Operations &amp; Billing</p>
            </div>
        </a>
    </div>

    <!-- Navigation Scroll Container -->
    <nav class="flex-1 overflow-y-auto overscroll-none py-3 px-2 space-y-4 text-xs font-medium">

        @if(auth()->user()?->isSuperAdmin())
        <div class="px-1 pb-2">
            <a href="{{ route('platform.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 text-white font-bold text-xs shadow-lg shadow-cyan-600/20 hover:brightness-110 transition">
                <span class="flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Super Admin Panel</span>
                </span>
                <span class="text-[9px] bg-white/20 px-1.5 py-0.5 rounded font-mono uppercase">Platform</span>
            </a>
        </div>
        @endif

        <!-- 4 Primary Direct Quick Links (matching Base44 DM) -->
        <div class="space-y-0.5">
            @php
                $isHome = request()->routeIs('dashboard');
                $isClients = request()->routeIs('clients.*');
                $isTickets = request()->routeIs('tickets.*');
                $isDevices = request()->routeIs('network.*') || request()->routeIs('udie.*');
            @endphp

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ $isHome ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                <svg class="w-4 h-4 {{ $isHome ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span>Home</span>
            </a>

            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ $isClients ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                <svg class="w-4 h-4 {{ $isClients ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                <span>Clients</span>
            </a>

            <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ $isTickets ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                <svg class="w-4 h-4 {{ $isTickets ? 'text-white' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                <span>Support Tickets</span>
            </a>

            <a href="{{ route('udie.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ $isDevices ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                <svg class="w-4 h-4 {{ $isDevices ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span>Devices</span>
            </a>
        </div>

        <hr class="border-slate-200 dark:border-slate-800/80 my-2">

        <!-- 1. Network Operations -->
        <div class="space-y-1">
            <button @click="navOpen.network_ops = !navOpen.network_ops" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Network Operations</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.network_ops ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.network_ops" class="space-y-0.5 pl-2">
                <a href="{{ route('noc.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('noc.*') && !request()->has('ai') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    <span>NOC Center</span>
                </a>
                <a href="{{ route('noc.index') }}#ai" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-300"></span>
                    <span>AI Operations Copilot</span>
                </a>
                <a href="{{ route('udie.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('udie.index') || request()->routeIs('udie.show') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    <span>UDIE Devices</span>
                </a>
                <a href="{{ route('udie.unknown') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('udie.unknown') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Unknown Device Lab</span>
                </a>
                <a href="{{ route('udie.models') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('udie.models') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>Device Model Catalog</span>
                </a>
                <a href="{{ route('gis.map') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('gis.map') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Network Map / GIS</span>
                </a>
                <a href="{{ route('cpe.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('cpe.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                    <span>OLT / ONU Mgmt</span>
                </a>
                <a href="{{ route('network.mikrotik') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('network.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Connect MikroTik</span>
                </a>
                <a href="{{ route('operations.predictive') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('operations.predictive') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Predictive Maintenance</span>
                </a>
                <a href="{{ route('operations.routing') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('operations.routing') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>Routing Config (OSPF/BGP)</span>
                </a>
                <a href="{{ route('operations.detection') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('operations.detection') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    <span>Rogue Router Sniffer</span>
                </a>
            </div>
        </div>

        <!-- 2. Access Management -->
        <div class="space-y-1">
            <button @click="navOpen.access_mgmt = !navOpen.access_mgmt" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Access Management</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.access_mgmt ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.access_mgmt" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('radius.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('radius.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>AAA / RADIUS &amp; PPPoE</span>
                </a>
            </div>
        </div>

        <!-- 3. Payment Gateways -->
        <div class="space-y-1">
            <button @click="navOpen.payment_gw = !navOpen.payment_gw" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Payment Gateways</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.payment_gw ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.payment_gw" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('gateways.api') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('gateways.api') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>API Gateways (Daraja STK)</span>
                </a>
                <a href="{{ route('gateways.manual') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('gateways.manual') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Payment Gateways (Manual)</span>
                </a>
            </div>
        </div>

        <!-- 4. Customer Management -->
        <div class="space-y-1">
            <button @click="navOpen.customer_mgmt = !navOpen.customer_mgmt" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Customer Management</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.customer_mgmt ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.customer_mgmt" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('clients.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('clients.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    <span>Subscribers List</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('tickets.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>Support Tickets</span>
                </a>
                <a href="{{ route('billing.plans') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('billing.plans') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>Billing Plans &amp; FUP</span>
                </a>
                <a href="{{ route('crm.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('crm.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>CRM Sales Pipeline</span>
                </a>
                <a href="{{ route('client.portal') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('client.portal') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    <span>Client Portal</span>
                </a>
            </div>
        </div>

        <!-- 5. Communication -->
        <div class="space-y-1">
            <button @click="navOpen.communication = !navOpen.communication" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Communication</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.communication ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.communication" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('communication.whatsapp') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('communication.whatsapp') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>WhatsApp Messaging</span>
                </a>
                <a href="{{ route('communication.reminders') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('communication.reminders') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Automated Reminders</span>
                </a>
                <a href="{{ route('communication.sms') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('communication.sms') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>SMS Gateways</span>
                </a>
            </div>
        </div>

        <!-- 6. Network Marketplace -->
        <div class="space-y-1">
            <button @click="navOpen.marketplace = !navOpen.marketplace" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Network Marketplace</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.marketplace ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.marketplace" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('wholesale.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('wholesale.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Wholesale Bandwidth &amp; Pools</span>
                </a>
            </div>
        </div>

        <!-- 7. Field Operations -->
        <div class="space-y-1">
            <button @click="navOpen.field_ops = !navOpen.field_ops" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Field Operations</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.field_ops ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.field_ops" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('gis.jobs') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('gis.jobs') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Technician View &amp; Work Orders</span>
                </a>
            </div>
        </div>

        <!-- 8. Finance & Inventory -->
        <div class="space-y-1">
            <button @click="navOpen.finance_inv = !navOpen.finance_inv" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Finance &amp; Inventory</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.finance_inv ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.finance_inv" x-cloak class="space-y-0.5 pl-2">
                <a href="{{ route('billing.invoices') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('billing.invoices') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('admin.proration') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.proration') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-lime-400"></span>
                    <span>Mid-Cycle Proration</span>
                </a>
                <a href="{{ route('billing.vouchers') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('billing.vouchers') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    <span>Vouchers</span>
                </a>
                <a href="{{ route('finance.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('finance.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>General Ledger &amp; P&amp;L</span>
                </a>
                <a href="{{ route('inventory.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('inventory.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                    <span>Hardware Inventory</span>
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('reports.*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Advanced Reports</span>
                </a>
            </div>
        </div>

        <!-- 9. Administration & Settings -->
        <div class="space-y-1">
            <button @click="navOpen.admin_sec = !navOpen.admin_sec" class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white uppercase tracking-wider text-[11px] font-bold transition">
                <span>Administration &amp; Settings</span>
                <svg class="w-3.5 h-3.5 transform transition-transform" :class="navOpen.admin_sec ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            <div x-show="navOpen.admin_sec" x-cloak class="space-y-0.5 pl-2">
                @if(auth()->user()?->isSuperAdmin())
                <a href="{{ route('tenants.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('tenants.index') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    <span>ISP Tenants &amp; White-Label</span>
                </a>
                <a href="{{ route('admin.isp-approvals') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.isp-approvals') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>ISP Approvals</span>
                </a>
                @endif
                <a href="{{ route('admin.system-plan') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.system-plan') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    <span>ISP Plan &amp; Tier Preferences</span>
                </a>
                <a href="{{ route('billing.subscription') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('billing.subscription') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>SaaS Subscription &amp; Proration</span>
                </a>
                <a href="{{ route('admin.user-approvals') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.user-approvals') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    <span>Staff Role Approvals</span>
                </a>
                <a href="{{ route('admin.tenant-branding') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.tenant-branding') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-400"></span>
                    <span>Company Branding</span>
                </a>
                <a href="{{ route('admin.system-settings') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.system-settings') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>System Settings</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 {{ request()->routeIs('admin.audit-logs') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                    <span>Audit Trail Logs</span>
                </a>
            </div>
        </div>

    </nav>

    <!-- Bottom User Strip -->
    <div class="p-3 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/40">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2.5 min-w-0">
                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-[11px] font-bold">
                    {{ substr(auth()->user()->name ?? 'SA', 0, 2) }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-slate-800 dark:text-white truncate">{{ auth()->user()->name ?? 'Smartoh Admin' }}</div>
                    <div class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@smartoh.net' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </button>
            </form>
        </div>
    </div>

</div>
