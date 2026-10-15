<!DOCTYPE html>
<html lang="en" class="dark" x-data="{ billingInterval: 'monthly' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing & 7-Day Free Trial - Smartoh Technologies</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen selection:bg-cyan-600 selection:text-white antialiased">

    <!-- Header -->
    <header class="border-b border-slate-800/80 bg-slate-950/80 backdrop-blur sticky top-0 z-50 px-4 sm:px-8 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-slate-800">
                <span class="font-extrabold text-base tracking-tight text-white">Smartoh Technologies</span>
            </a>

            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold text-slate-300">
                <a href="{{ route('features') }}" class="hover:text-white transition">Features</a>
                <a href="{{ route('pricing') }}" class="text-cyan-400">Pricing &amp; 7-Day Trial</a>
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
            Transparent Pricing • Zero Setup Fees
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight max-w-3xl mx-auto leading-tight">
            Predictable Plans for Growing Networks
        </h1>
        <p class="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto">
            Every subscription includes an unrestricted <strong>7-day free trial</strong> with full access to our autonomous billing engine, device intelligence, and core network monitoring.
        </p>

        <!-- Interval Switcher -->
        <div class="inline-flex items-center p-1 bg-slate-900 border border-slate-800 rounded-2xl text-xs font-semibold mt-4">
            <button type="button" @click="billingInterval = 'monthly'" class="px-4 py-2 rounded-xl transition" :class="billingInterval === 'monthly' ? 'bg-cyan-600 text-white shadow' : 'text-slate-400 hover:text-white'">
                Monthly Billing
            </button>
            <button type="button" @click="billingInterval = 'annually'" class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5" :class="billingInterval === 'annually' ? 'bg-cyan-600 text-white shadow' : 'text-slate-400 hover:text-white'">
                <span>Annual Billing</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-emerald-500/20 text-emerald-300">Save 2 Months</span>
            </button>
        </div>
    </section>

    <!-- Pricing Cards Grid -->
    <section class="max-w-7xl mx-auto px-4 sm:px-8 pb-16 grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($plans as $plan)
        @php 
            $ver = $plan->currentVersion; 
            $isPro = $plan->slug === 'pro';
        @endphp
        <div class="bg-slate-900/90 border {{ $isPro ? 'border-cyan-500 shadow-cyan-500/10' : 'border-slate-800' }} rounded-3xl p-8 shadow-2xl flex flex-col justify-between space-y-6 relative group">
            
            @if($isPro)
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-extrabold text-[10px] font-mono uppercase tracking-wider rounded-full shadow-lg">
                Most Popular for WISPs
            </div>
            @endif

            <div class="space-y-4">
                <div>
                    <h3 class="text-xl font-extrabold text-white">{{ $plan->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 min-h-[32px]">{{ $plan->description }}</p>
                </div>

                <!-- Price display -->
                <div class="py-4 border-y border-slate-800/80 space-y-1">
                    <div class="flex items-baseline space-x-1">
                        <span class="text-3xl font-black text-white font-mono" x-show="billingInterval === 'monthly'">
                            {{ $plan->currency }} {{ number_format($plan->monthly_price) }}
                        </span>
                        <span class="text-3xl font-black text-white font-mono" x-show="billingInterval === 'annually'">
                            {{ $plan->currency }} {{ number_format($plan->annual_price) }}
                        </span>
                        <span class="text-xs text-slate-500 font-mono" x-text="billingInterval === 'monthly' ? '/month' : '/year'">/month</span>
                    </div>
                    <div class="text-[11px] text-cyan-400 font-mono">
                        {{ $plan->trial_days }}-Day Free Trial (No upfront payment)
                    </div>
                </div>

                <!-- Included Entitlements -->
                @if($ver)
                <ul class="space-y-2.5 text-xs text-slate-300 font-mono">
                    <li class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Up to <strong>{{ number_format($ver->features['max_subscribers'] ?? 0) }}</strong> Subscribers</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ $ver->features['max_routers'] ?? 0 }}</strong> Core MikroTik Gateways</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ $ver->features['max_staff'] ?? 0 }}</strong> Staff &amp; Tech Accounts</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ $ver->features['max_sites'] ?? 1 }}</strong> Operating Site Branches</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        @if(!empty($ver->features['has_api_access']))
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>REST API &amp; Webhook Triggers</span>
                        @else
                        <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-slate-500">REST API &amp; Webhooks</span>
                        @endif
                    </li>
                    <li class="flex items-center space-x-2">
                        @if(!empty($ver->features['has_white_label']))
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>White-Label Tenant Branding</span>
                        @else
                        <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-slate-500">White-Label Branding</span>
                        @endif
                    </li>
                </ul>
                @endif
            </div>

            <!-- CTA Button linking to registration with preselected plan -->
            <div>
                <a :href="'{{ route('register') }}?plan={{ $plan->slug }}&interval=' + billingInterval" class="block w-full py-3 px-4 rounded-xl font-bold text-xs text-center transition shadow-lg {{ $isPro ? 'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white shadow-cyan-600/20' : 'bg-slate-800 hover:bg-slate-700 text-white' }}">
                    Start 7-Day Free Trial &rarr;
                </a>
            </div>

        </div>
        @endforeach
    </section>

    <!-- Trial FAQ Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-8 py-12 border-t border-slate-800/80 space-y-6">
        <h2 class="text-xl font-bold text-white text-center">Frequently Asked Questions About the 7-Day Trial</h2>

        <div class="space-y-4 text-xs leading-relaxed text-slate-400">
            <div class="p-4 bg-slate-900 rounded-2xl border border-slate-800 space-y-1">
                <h3 class="font-bold text-white">How does the 7-day free trial work?</h3>
                <p>When your ISP application is approved, an isolated tenant workspace is provisioned with 7 full calendar days of unrestricted access. You can connect core MikroTik routers, test M-Pesa STK push, and provision subscriber accounts.</p>
            </div>

            <div class="p-4 bg-slate-900 rounded-2xl border border-slate-800 space-y-1">
                <h3 class="font-bold text-white">What happens when the trial expires?</h3>
                <p>Your subscriber, router, and billing data is <strong>never deleted</strong>. A 48-hour grace period allows you to complete payment. If unpaid after grace, operational modules are paused until a subscription payment is confirmed.</p>
            </div>

            <div class="p-4 bg-slate-900 rounded-2xl border border-slate-800 space-y-1">
                <h3 class="font-bold text-white">Can I upgrade or change my plan later?</h3>
                <p>Yes. You can upgrade, downgrade, or switch billing intervals at any time directly through the billing module or through your Super Admin account manager.</p>
            </div>
        </div>
    </section>

</body>
</html>
