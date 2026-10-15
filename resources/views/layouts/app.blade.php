<!DOCTYPE html>
<html lang="en" class="dark" x-data="{ 
    darkMode: localStorage.getItem('theme') !== 'light',
    sidebarOpen: false,
    navOpen: {
        network_ops: true,
        access_mgmt: false,
        payment_gw: false,
        customer_mgmt: false,
        communication: false,
        marketplace: false,
        field_ops: false,
        finance_inv: false,
        admin_sec: false
    },
    notificationsOpen: false,
    userMenuOpen: false,
    tenantMenuOpen: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
    init() {
        if (!this.darkMode) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    }
}" x-init="init()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $currentTenant->name ?? 'Smartoh Technologies' }} - ISP Billing & Network Management</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --brand-color: {{ $currentTenant->brand_color ?? '#1565C0' }};
            --accent-color: {{ $currentTenant->accent_color ?? '#0ea5e9' }};
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex antialiased selection:bg-blue-600 selection:text-white transition-colors duration-200">

    <!-- Mobile Drawer Overlay -->
    <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-50 flex">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shadow-2xl">
            <div class="absolute top-3 right-3 z-10">
                <button @click="sidebarOpen = false" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <!-- Include Mobile Sidebar Content -->
            @include('layouts.sidebar-content')
        </div>
    </div>

    <!-- Desktop Sidebar (Fixed Left) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800/80 sticky top-0 h-screen z-30 select-none">
        @include('layouts.sidebar-content')
    </aside>

    <!-- Main Application Column -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-x-hidden">

        <!-- Top Header Navigation Bar -->
        <header class="h-14 shrink-0 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-800/80 sticky top-0 z-40 px-3 sm:px-6 flex items-center justify-between transition-colors">
            
            <!-- Left: Mobile Trigger & Search Bar -->
            <div class="flex items-center space-x-2 sm:space-x-3 flex-1 max-w-xs sm:max-w-md">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <!-- Retained Search Bar (Command Palette) -->
                <div class="relative w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" placeholder="Quick search or jump to... (⌘K)" class="w-full pl-9 pr-3 py-1.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- Right Controls: Notifications, Theme, User -->
            <div class="flex items-center space-x-2 sm:space-x-3">

                <!-- AI Operations Copilot Header Trigger -->
                <a href="{{ route('noc.index') }}#ai" title="AI Operations Copilot" class="p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 border border-cyan-500/30 flex items-center gap-1.5 transition text-xs font-semibold">
                    <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span class="hidden md:inline">AI Copilot</span>
                </a>

                <!-- Notification Bell Drawer -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 relative transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-blue-600 ring-2 ring-white dark:ring-slate-900"></span>
                    </button>

                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden z-50">
                        <div class="p-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">Notifications</h4>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">3 unread</span>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <div class="font-medium text-slate-800 dark:text-slate-200">M-Pesa STK Push Completed</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Account ACC-1092 renewed for KES 2,500.</div>
                                <div class="text-[10px] text-slate-400 mt-1">2 mins ago</div>
                            </div>
                            <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <div class="font-medium text-slate-800 dark:text-slate-200">Optical Signal Alert (-26.4 dBm)</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">ONT in Kilimani POP sector 2 degraded.</div>
                                <div class="text-[10px] text-slate-400 mt-1">14 mins ago</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle (Dark / Light) -->
                <button @click="toggleTheme()" :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <template x-if="darkMode">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </template>
                    <template x-if="!darkMode">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    </template>
                </button>

                <!-- User Profile & Avatar -->
                @auth
                <div class="relative" x-data="{ userOpen: false }">
                    <button @click="userOpen = !userOpen" class="flex items-center space-x-2 pl-2 border-l border-slate-200 dark:border-slate-800 transition">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-blue-500/20">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-semibold text-slate-800 dark:text-white leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                        </div>
                    </button>

                    <div x-show="userOpen" @click.away="userOpen = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl py-1 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</div>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('platform.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/30">Super Admin Control Panel</a>
                        @endif
                        <a href="{{ route('billing.subscription') }}" class="block px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">SaaS Subscription &amp; Plan</a>
                        <a href="{{ route('admin.tenant-branding') }}" class="block px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Company Branding</a>
                        <a href="{{ route('client.portal') }}" class="block px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">Client Portal View</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 dark:border-slate-800">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 font-medium">Sign Out</button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <!-- Real-time 7-Day Trial / Subscription Status Banner -->
        @auth
            @if(auth()->user()->tenant_id && auth()->user()->role !== 'super_admin')
                @php
                    $tenantSub = \App\Models\SaasSubscription::where('tenant_id', auth()->user()->tenant_id)->latest()->first();
                @endphp
                @if($tenantSub)
                    @if(in_array($tenantSub->status, ['trial_active', 'trial_expiring']))
                        @php
                            $daysLeft = $tenantSub->daysRemainingInTrial();
                            $hoursLeft = $tenantSub->hoursRemainingInTrial();
                        @endphp
                        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-sky-600 text-white px-4 py-2.5 text-xs shadow-md flex items-center justify-between border-b border-white/10">
                            <div class="flex items-center space-x-2.5 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white uppercase tracking-wider">
                                    7-Day Free Trial
                                </span>
                                <span class="font-medium">
                                    @if($daysLeft > 0)
                                        You have <strong class="underline decoration-white/50">{{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }}</strong> remaining in your free trial.
                                    @else
                                        Your trial expires in <strong class="underline decoration-white/50">{{ $hoursLeft }} hour{{ $hoursLeft > 1 ? 's' : '' }}</strong>!
                                    @endif
                                </span>
                                <span class="hidden lg:inline text-blue-200">Enjoy full ISP network access. Upgrade anytime to avoid interruption.</span>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <a href="{{ route('billing.subscription') }}" class="px-3 py-1 bg-white text-blue-700 hover:bg-blue-50 font-bold rounded-lg text-xs shadow-sm transition">
                                    View Subscription &rarr;
                                </a>
                            </div>
                        </div>
                    @elseif(in_array($tenantSub->status, ['trial_expired', 'past_due', 'grace_period', 'payment_pending']))
                        <div class="bg-gradient-to-r from-amber-600 to-rose-600 text-white px-4 py-2.5 text-xs shadow-md flex items-center justify-between border-b border-white/10">
                            <div class="flex items-center space-x-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-white/20 text-white uppercase tracking-wider">
                                    Subscription Notice
                                </span>
                                <span class="font-medium">
                                    Status: <strong>{{ strtoupper(str_replace('_', ' ', $tenantSub->status)) }}</strong>.
                                    Operational network actions are paused until renewal payment is processed.
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <a href="{{ route('billing.subscription') }}" class="px-3 py-1 bg-white text-rose-700 hover:bg-rose-50 font-bold rounded-lg text-xs shadow-sm transition">
                                    Pay Now &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                @endif
            @elseif(auth()->user()->role === 'super_admin')
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-cyan-950 text-slate-200 px-4 py-2 text-xs flex items-center justify-between border-b border-cyan-500/20">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            SUPER ADMIN
                        </span>
                        <span class="text-slate-300">You are logged in as Platform Super Administrator.</span>
                    </div>
                    <div>
                        <a href="{{ route('platform.dashboard') }}" class="px-2.5 py-1 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 border border-cyan-500/40 rounded-lg text-xs font-semibold transition">
                            Open Control Panel &rarr;
                        </a>
                    </div>
                </div>
            @endif
        @endauth

        <!-- Flash Alert Messages -->
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl text-emerald-800 dark:text-emerald-400 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200">&times;</button>
            </div>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-xl text-rose-800 dark:text-rose-400 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200">&times;</button>
            </div>
        </div>
        @endif

        <!-- Main Content Slot -->
        <main class="flex-1 max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- Clean Footer -->
        <footer class="bg-white/60 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-800/80 py-4 text-xs text-slate-500 dark:text-slate-400">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-5 h-5 rounded object-contain bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-0.5">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Smartoh Technologies</span>
                    <span>•</span>
                    <span>Smartoh Technologies Autonomous ISP Platform</span>
                </div>
                <div class="flex items-center space-x-4 text-[11px] font-mono">
                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>MikroTik v7 API Sync</span>
                    </span>
                    <span>•</span>
                    <span>CWMP ACS: 7547</span>
                    <span>•</span>
                    <span>WireGuard: 51820</span>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
