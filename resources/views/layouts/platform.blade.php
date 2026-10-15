<!DOCTYPE html>
<html lang="en" class="dark" x-data="{ 
    darkMode: localStorage.getItem('theme') !== 'light',
    sidebarOpen: false,
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
    <title>@yield('title', 'Platform Control Panel') - Smartoh Technologies</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex antialiased selection:bg-cyan-600 selection:text-white">

    <!-- Mobile Drawer Overlay -->
    <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-50 flex">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <div class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 border-r border-slate-800 shadow-2xl p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <span class="text-sm font-extrabold text-white">Platform Super Admin</span>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <!-- Navigation items repeated for mobile -->
            <nav class="space-y-1.5 text-xs font-medium">
                @include('layouts.platform-nav-links')
            </nav>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-slate-900/95 border-r border-slate-800 shrink-0 sticky top-0 h-screen overflow-y-auto">
        <!-- Brand Header -->
        <div class="p-5 border-b border-slate-800/80 flex items-center space-x-3">
            <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-slate-700">
            <div>
                <div class="text-xs font-extrabold text-white tracking-tight flex items-center space-x-1.5">
                    <span>Smartoh Technologies</span>
                </div>
                <div class="text-[10px] font-mono text-cyan-400 font-semibold uppercase tracking-wider flex items-center space-x-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                    <span>Super Admin Core</span>
                </div>
            </div>
        </div>

        <!-- Super Admin Navigation -->
        <nav class="flex-1 p-4 space-y-1.5 text-xs font-medium">
            @include('layouts.platform-nav-links')
        </nav>

        <!-- User Profile & Quick Exit -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/50 space-y-3">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center font-bold text-white text-xs">
                    {{ substr(auth()->user()->name ?? 'SA', 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                    <div class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@smartoh.co.ke' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                <a href="{{ route('dashboard') }}" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-center transition">
                    ISP Workspace
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-center transition">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar -->
        <header class="h-16 bg-slate-900/80 backdrop-blur border-b border-slate-800 sticky top-0 z-30 px-4 sm:px-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-mono font-bold bg-purple-500/10 text-purple-400 border border-purple-500/30 uppercase">
                        SaaS Platform Control Center
                    </span>
                    <span class="text-slate-600 hidden sm:inline">•</span>
                    <span class="text-xs text-slate-400 hidden sm:inline">Multitenant ISP SaaS Engine v2.0</span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('pricing') }}" target="_blank" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono hidden md:inline">
                    View Public Pricing &rarr;
                </a>
                <a href="{{ route('register') }}" target="_blank" class="text-xs text-slate-400 hover:text-slate-200 font-mono hidden md:inline">
                    Onboarding Portal &rarr;
                </a>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-xs flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs flex items-center justify-between shadow-sm mt-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
            </div>
            @endif
        </div>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- Platform Footer -->
        <footer class="bg-slate-950 border-t border-slate-800/80 py-4 text-xs text-slate-500 text-center">
            Smartoh Technologies Multitenant Autonomous ISP SaaS Platform • Strictly Isolated Control Plane
        </footer>
    </div>

</body>
</html>
