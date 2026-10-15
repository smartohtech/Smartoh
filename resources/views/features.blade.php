<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - Smartoh Technologies Autonomous ISP Platform</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen selection:bg-cyan-600 selection:text-white antialiased">

    <!-- Navigation Header -->
    <header class="border-b border-slate-800/80 bg-slate-950/80 backdrop-blur sticky top-0 z-50 px-4 sm:px-8 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-slate-800">
                <span class="font-extrabold text-base tracking-tight text-white">Smartoh Technologies</span>
            </a>

            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold text-slate-300">
                <a href="{{ route('features') }}" class="text-cyan-400">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-white transition">Pricing &amp; 7-Day Trial</a>
                <a href="{{ route('application.status') }}" class="hover:text-white transition">Application Status</a>
            </nav>

            <div class="flex items-center space-x-3 text-xs font-semibold">
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-900 transition">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white shadow-lg shadow-cyan-600/20 transition">
                    Start 7-Day Free Trial
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-8 py-16 text-center space-y-4">
        <span class="px-3 py-1 rounded-full text-[11px] font-bold font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30 uppercase tracking-wider">
            Carrier-Grade ISP Platform Architecture
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight max-w-3xl mx-auto leading-tight">
            Built for Modern Fiber &amp; Wireless Operators
        </h1>
        <p class="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto">
            Everything your ISP needs: autonomous M-Pesa billing, multi-vendor device intelligence, GenieACS TR-069, NOC upstream monitoring, and double-entry accounting.
        </p>
    </section>

    <!-- 6 Primary Engineering Pillars -->
    <section class="max-w-7xl mx-auto px-4 sm:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Pillar 1: NOC & Upstream Capacity -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">Autonomous NOC Telemetry</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Real-time upstream bandwidth monitoring, live traffic pulled from core routers, peak load surge simulation, and AI-assisted circuit diagnostics.
            </p>
        </div>

        <!-- Pillar 2: UDIE Discovery -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">UDIE Multi-Protocol Discovery</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Universal Device Intelligence &amp; Discovery Engine identifying MikroTik, Huawei, ZTE, and Ubiquiti hardware via SNMP, MAC OUI, and TR-069.
            </p>
        </div>

        <!-- Pillar 3: GenieACS TR-069 & CPE -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">TR-069 &amp; TR-181 USP CPE Control</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Zero-touch customer premises equipment provisioning, remote optical attenuation tests (dBm), Wi-Fi SSID reconfiguration, and automated firmware pushes.
            </p>
        </div>

        <!-- Pillar 4: Autonomous Billing & M-Pesa -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">Automated M-Pesa STK Push</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Safaricom Daraja API integration with automatic queue reactivation on MikroTik upon verified callback. Prorated upgrades and voucher engines.
            </p>
        </div>

        <!-- Pillar 5: GIS & Field Operations -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">Leaflet GIS Fiber Mapping</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Map fiber splices, distribution FAT boxes, and OLT splitter ports. Dispatch field technicians with GPS job tracking.
            </p>
        </div>

        <!-- Pillar 6: Multi-Site Accounting -->
        <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-3xl space-y-4 shadow-xl">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-white">Double-Entry ISP Accounting</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Multi-site Chart of Accounts, revenue ledgers, inter-branch inventory transfers, and automated financial period closing.
            </p>
        </div>

    </section>

    <!-- CTA Strip -->
    <section class="max-w-7xl mx-auto px-4 sm:px-8 py-16 text-center">
        <div class="p-10 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-3xl shadow-2xl space-y-4 max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold text-white">Ready to automate your ISP operations?</h2>
            <p class="text-xs text-slate-400">Apply today and experience full carrier-grade autonomy with our unrestricted 7-day free trial.</p>
            <div class="pt-2">
                <a href="{{ route('register') }}" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition inline-block">
                    Start 7-Day Free Trial &rarr;
                </a>
            </div>
        </div>
    </section>

</body>
</html>
