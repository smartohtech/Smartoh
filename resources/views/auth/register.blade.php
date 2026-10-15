<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your ISP — Smartoh Technologies</title>
    <meta name="description" content="Register your Internet Service Provider on the Smartoh Technologies billing and network management platform.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .step-panel { display: none; }
        .step-panel.active { display: block; }
        .step-indicator.done { background: #1565C0; border-color: #1565C0; color: white; }
        .step-indicator.active { border-color: #1565C0; color: #1565C0; }
        .step-connector.done { background: #1565C0; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        .field-group label { display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; }
        .field-input { width: 100%; padding: 0.625rem 0.875rem; border-radius: 0.75rem; border-width: 1px; font-size: 0.875rem; transition: border-color 0.15s; background: transparent; }
        .input-prefix { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; pointer-events: none; }
        .has-prefix input { padding-left: 4rem; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">

    <!-- Ambient bg -->
    <div class="fixed -top-60 -left-60 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-60 -right-60 w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 min-h-screen flex flex-col">

        <!-- Top bar -->
        <header class="px-6 py-4 flex items-center justify-between border-b border-slate-800/80">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-105 transition-transform">
                    <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-white leading-tight">Smartoh Technologies</div>
                    <div class="text-[10px] text-cyan-400 font-medium tracking-wider uppercase">ISP Platform</div>
                </div>
            </a>
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14"/></svg>
                Already have an account? Sign in
            </a>
        </header>

        <!-- Main content -->
        <main class="flex-1 flex items-start justify-center px-4 py-10">
            <div class="w-full max-w-2xl">

                <!-- Page heading -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 bg-blue-600/10 border border-blue-600/20 text-blue-400 text-xs font-semibold px-4 py-1.5 rounded-full mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                        ISP Onboarding
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-tight mb-2">Register Your ISP</h1>
                    <p class="text-slate-400 text-sm">Set up your company account on Smartoh Technologies in under 2 minutes.</p>
                </div>

                <!-- Step Indicators -->
                <div class="flex items-center justify-center gap-0 mb-8" id="step-indicators">
                    <div class="flex flex-col items-center">
                        <div id="ind-1" class="step-indicator active w-9 h-9 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all duration-300">1</div>
                        <span class="text-[10px] text-slate-400 mt-1 font-medium">Company</span>
                    </div>
                    <div id="conn-1" class="step-connector w-16 h-0.5 bg-slate-700 mb-4 transition-all duration-500"></div>
                    <div class="flex flex-col items-center">
                        <div id="ind-2" class="step-indicator w-9 h-9 rounded-full border-2 border-slate-600 text-slate-500 flex items-center justify-center text-sm font-bold transition-all duration-300">2</div>
                        <span class="text-[10px] text-slate-400 mt-1 font-medium">Admin</span>
                    </div>
                    <div id="conn-2" class="step-connector w-16 h-0.5 bg-slate-700 mb-4 transition-all duration-500"></div>
                    <div class="flex flex-col items-center">
                        <div id="ind-3" class="step-indicator w-9 h-9 rounded-full border-2 border-slate-600 text-slate-500 flex items-center justify-center text-sm font-bold transition-all duration-300">3</div>
                        <span class="text-[10px] text-slate-400 mt-1 font-medium">Network</span>
                    </div>
                    <div id="conn-3" class="step-connector w-16 h-0.5 bg-slate-700 mb-4 transition-all duration-500"></div>
                    <div class="flex flex-col items-center">
                        <div id="ind-4" class="step-indicator w-9 h-9 rounded-full border-2 border-slate-600 text-slate-500 flex items-center justify-center text-sm font-bold transition-all duration-300">4</div>
                        <span class="text-[10px] text-slate-400 mt-1 font-medium">Review</span>
                    </div>
                </div>

                <!-- Error display -->
                @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-sm text-rose-400 space-y-1">
                    @foreach ($errors->all() as $error)
                    <div class="flex items-start gap-2"><span>&#x26A0;</span> {{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register.post') }}" id="reg-form">
                    @csrf

                    <!-- ======== STEP 1: COMPANY INFO ======== -->
                    <div class="step-panel active" id="step-1">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-600/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Company Information</h2>
                                    <p class="text-xs text-slate-400">Tell us about your ISP business</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- ISP Name -->
                                <div class="field-group">
                                    <label class="text-slate-300">ISP / Company Name <span class="text-rose-400">*</span></label>
                                    <input type="text" name="company_name" id="company_name" required
                                        value="{{ old('company_name') }}"
                                        placeholder="e.g. AlphaNet Communications Ltd"
                                        class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                </div>

                                <!-- Subdomain -->
                                <div class="field-group">
                                    <label class="text-slate-300">Your Platform Subdomain <span class="text-rose-400">*</span></label>
                                    <div class="relative has-prefix">
                                        <span class="input-prefix text-slate-500">app/</span>
                                        <input type="text" name="subdomain" id="subdomain" required
                                            value="{{ old('subdomain') }}"
                                            placeholder="alphanet"
                                            pattern="[a-z0-9\-]+"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-1">Lowercase letters, numbers and hyphens only. This identifies your workspace.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Company Email -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Company Email <span class="text-rose-400">*</span></label>
                                        <input type="email" name="company_email" required
                                            value="{{ old('company_email') }}"
                                            placeholder="info@alphanet.co.ke"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                    <!-- Company Phone -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Company Phone <span class="text-rose-400">*</span></label>
                                        <input type="tel" name="company_phone" required
                                            value="{{ old('company_phone') }}"
                                            placeholder="+254 700 000 000"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="field-group">
                                    <label class="text-slate-300">Physical Address</label>
                                    <textarea name="address" rows="2"
                                        placeholder="e.g. Nairobi, Kenya — Westlands Business Centre"
                                        class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500 resize-none">{{ old('address') }}</textarea>
                                </div>

                                <!-- Subscription Tier -->
                                <div class="field-group">
                                    <label class="text-slate-300">Subscription Plan <span class="text-rose-400">*</span></label>
                                    <select name="subscription_tier" required
                                        class="field-input dark:bg-slate-950 border-slate-700/80 text-white">
                                        <option value="" disabled {{ old('subscription_tier') ? '' : 'selected' }}>-- Select a plan --</option>
                                        <option value="starter" {{ old('subscription_tier') === 'starter' ? 'selected' : '' }}>Starter — Free (up to 50 subscribers)</option>
                                        <option value="professional" {{ old('subscription_tier') === 'professional' ? 'selected' : '' }}>Professional — KES 4,999/mo (up to 1,000 subscribers)</option>
                                        <option value="enterprise" {{ old('subscription_tier') === 'enterprise' ? 'selected' : '' }}>Enterprise — Custom (unlimited)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="button" onclick="goStep(2)" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white text-sm font-semibold rounded-xl shadow-lg transition flex items-center gap-2">
                                    Next: Admin Account
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ======== STEP 2: ADMIN ACCOUNT ======== -->
                    <div class="step-panel" id="step-2">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600/15 border border-emerald-600/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Administrator Account</h2>
                                    <p class="text-xs text-slate-400">This will be the primary ISP admin login</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Full Name -->
                                <div class="field-group">
                                    <label class="text-slate-300">Full Name <span class="text-rose-400">*</span></label>
                                    <input type="text" name="admin_name" required
                                        value="{{ old('admin_name') }}"
                                        placeholder="e.g. John Kamau"
                                        class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Admin Email -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Admin Email <span class="text-rose-400">*</span></label>
                                        <input type="email" name="email" required
                                            value="{{ old('email') }}"
                                            placeholder="admin@alphanet.co.ke"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                    <!-- Phone -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Phone (WhatsApp) <span class="text-rose-400">*</span></label>
                                        <input type="tel" name="phone" required
                                            value="{{ old('phone') }}"
                                            placeholder="+254 712 345 678"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Password -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Password <span class="text-rose-400">*</span></label>
                                        <input type="password" name="password" id="pw1" required
                                            placeholder="Min 8 characters"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                    <!-- Confirm Password -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Confirm Password <span class="text-rose-400">*</span></label>
                                        <input type="password" name="password_confirmation" id="pw2" required
                                            placeholder="Repeat password"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white placeholder-slate-500">
                                    </div>
                                </div>

                                <!-- Password strength hint -->
                                <p id="pw-hint" class="text-[11px] text-slate-500">Use at least 8 characters with letters and numbers.</p>

                                <!-- Terms -->
                                <div class="flex items-start gap-3 pt-2">
                                    <input type="checkbox" name="terms" id="terms" required
                                        class="mt-0.5 rounded bg-slate-800 border-slate-600 text-blue-600 focus:ring-blue-500/20 flex-shrink-0">
                                    <label for="terms" class="text-xs text-slate-400 leading-relaxed cursor-pointer">
                                        I agree to Smartoh Technologies' <a href="#" class="text-blue-400 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-400 hover:underline">Privacy Policy</a>. I confirm I am authorised to register this ISP company on the platform.
                                    </label>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between">
                                <button type="button" onclick="goStep(1)" class="px-5 py-2.5 border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-sm font-medium rounded-xl transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button type="button" onclick="goStep(3)" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white text-sm font-semibold rounded-xl shadow-lg transition flex items-center gap-2">
                                    Next: Network Setup
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ======== STEP 3: NETWORK PREFERENCES ======== -->
                    <div class="step-panel" id="step-3">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-purple-600/15 border border-purple-600/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Network Setup Preferences</h2>
                                    <p class="text-xs text-slate-400">Tell us how your network is structured (you can change these later)</p>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <!-- Network type -->
                                <div class="field-group">
                                    <label class="text-slate-300 mb-2 block">ISP Network Type <span class="text-rose-400">*</span></label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach([['pppoe','PPPoE','Dial-up style auth'],['hotspot','HotSpot','Voucher-based WiFi'],['ftth','FTTH/GPON','Fiber to the home'],['wisp','WISP','Wireless ISP'],['both','PPPoE + HotSpot','Mixed network'],['other','Other','Custom setup']] as [$val,$label,$desc])
                                        <label class="flex flex-col items-center justify-center p-3 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-600/50 hover:bg-blue-600/5 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-600/10 text-center">
                                            <input type="radio" name="network_type" value="{{ $val }}" class="sr-only" {{ old('network_type') === $val ? 'checked' : '' }}>
                                            <span class="text-xs font-semibold text-white">{{ $label }}</span>
                                            <span class="text-[10px] text-slate-500 mt-0.5">{{ $desc }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Approx subscribers -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Approx. Current Subscribers</label>
                                        <select name="approx_subscribers"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white">
                                            <option value="" disabled {{ old('approx_subscribers') ? '' : 'selected' }}>-- Select range --</option>
                                            <option value="1-50" {{ old('approx_subscribers') === '1-50' ? 'selected' : '' }}>Just starting (1–50)</option>
                                            <option value="51-200" {{ old('approx_subscribers') === '51-200' ? 'selected' : '' }}>Growing (51–200)</option>
                                            <option value="201-1000" {{ old('approx_subscribers') === '201-1000' ? 'selected' : '' }}>Established (201–1,000)</option>
                                            <option value="1001+" {{ old('approx_subscribers') === '1001+' ? 'selected' : '' }}>Large ISP (1,000+)</option>
                                        </select>
                                    </div>
                                    <!-- Router brand -->
                                    <div class="field-group">
                                        <label class="text-slate-300">Primary Router Brand</label>
                                        <select name="router_brand"
                                            class="field-input dark:bg-slate-950 border-slate-700/80 text-white">
                                            <option value="" disabled {{ old('router_brand') ? '' : 'selected' }}>-- Select --</option>
                                            <option value="mikrotik" {{ old('router_brand') === 'mikrotik' ? 'selected' : '' }}>MikroTik (RouterOS)</option>
                                            <option value="cisco" {{ old('router_brand') === 'cisco' ? 'selected' : '' }}>Cisco</option>
                                            <option value="ubiquiti" {{ old('router_brand') === 'ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                            <option value="huawei" {{ old('router_brand') === 'huawei' ? 'selected' : '' }}>Huawei</option>
                                            <option value="mixed" {{ old('router_brand') === 'mixed' ? 'selected' : '' }}>Mixed / Multiple brands</option>
                                            <option value="other" {{ old('router_brand') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Payment methods -->
                                <div class="field-group">
                                    <label class="text-slate-300 block mb-2">Payment Methods You Use <span class="text-slate-500 font-normal">(select all that apply)</span></label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach([['mpesa','M-Pesa / Daraja'],['bank','Bank Transfer'],['cash','Cash Payments'],['card','Card Payments'],['airtel','Airtel Money'],['voucher','Voucher Codes']] as [$val,$label])
                                        <label class="flex items-center gap-2 p-2.5 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500/40 hover:bg-blue-600/5 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-600/10">
                                            <input type="checkbox" name="payment_methods[]" value="{{ $val }}"
                                                class="rounded bg-slate-800 border-slate-600 text-blue-600 focus:ring-blue-500/20 flex-shrink-0"
                                                {{ in_array($val, old('payment_methods', [])) ? 'checked' : '' }}>
                                            <span class="text-xs text-slate-300 font-medium">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- How did you hear -->
                                <div class="field-group">
                                    <label class="text-slate-300">How did you hear about Smartoh?</label>
                                    <select name="referral_source"
                                        class="field-input dark:bg-slate-950 border-slate-700/80 text-white">
                                        <option value="">-- Optional --</option>
                                        <option value="google">Google Search</option>
                                        <option value="social">Social Media</option>
                                        <option value="referral">Referred by another ISP</option>
                                        <option value="whatsapp">WhatsApp Group</option>
                                        <option value="event">ISP Event / Conference</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between">
                                <button type="button" onclick="goStep(2)" class="px-5 py-2.5 border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-sm font-medium rounded-xl transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button type="button" onclick="goStep(4)" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white text-sm font-semibold rounded-xl shadow-lg transition flex items-center gap-2">
                                    Review &amp; Submit
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ======== STEP 4: REVIEW & SUBMIT ======== -->
                    <div class="step-panel" id="step-4">
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                                <div class="w-10 h-10 rounded-xl bg-amber-600/15 border border-amber-600/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Review &amp; Submit</h2>
                                    <p class="text-xs text-slate-400">Confirm your details before creating your account</p>
                                </div>
                            </div>

                            <div class="space-y-4 mb-6">
                                <!-- Review summary -->
                                <div class="bg-slate-800/50 rounded-xl p-4 space-y-3 text-sm">
                                    <div class="flex justify-between items-start">
                                        <span class="text-slate-400 text-xs uppercase tracking-wider font-semibold">Company</span>
                                        <button type="button" onclick="goStep(1)" class="text-[11px] text-blue-400 hover:underline">Edit</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><span class="text-slate-500">Name:</span> <span id="rev-company" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Subdomain:</span> <span id="rev-subdomain" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Email:</span> <span id="rev-cemail" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Phone:</span> <span id="rev-cphone" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Plan:</span> <span id="rev-plan" class="text-cyan-400 font-semibold">—</span></div>
                                    </div>
                                </div>

                                <div class="bg-slate-800/50 rounded-xl p-4 space-y-3">
                                    <div class="flex justify-between items-start">
                                        <span class="text-slate-400 text-xs uppercase tracking-wider font-semibold">Admin Account</span>
                                        <button type="button" onclick="goStep(2)" class="text-[11px] text-blue-400 hover:underline">Edit</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><span class="text-slate-500">Name:</span> <span id="rev-aname" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Email:</span> <span id="rev-aemail" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Phone:</span> <span id="rev-aphone" class="text-white font-medium">—</span></div>
                                    </div>
                                </div>

                                <div class="bg-slate-800/50 rounded-xl p-4 space-y-3">
                                    <div class="flex justify-between items-start">
                                        <span class="text-slate-400 text-xs uppercase tracking-wider font-semibold">Network</span>
                                        <button type="button" onclick="goStep(3)" class="text-[11px] text-blue-400 hover:underline">Edit</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><span class="text-slate-500">Type:</span> <span id="rev-nettype" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Subscribers:</span> <span id="rev-subs" class="text-white font-medium">—</span></div>
                                        <div><span class="text-slate-500">Router:</span> <span id="rev-router" class="text-white font-medium">—</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- What happens next -->
                            <div class="bg-blue-600/8 border border-blue-600/20 rounded-xl p-4 mb-6 text-xs text-slate-300 space-y-2">
                                <div class="font-semibold text-blue-300 flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    What happens next?
                                </div>
                                <div class="flex items-start gap-2"><span class="text-blue-400 mt-0.5">1.</span> Your account will be created and sent for review by Smartoh admin.</div>
                                <div class="flex items-start gap-2"><span class="text-blue-400 mt-0.5">2.</span> You'll receive a confirmation email at your admin email address.</div>
                                <div class="flex items-start gap-2"><span class="text-blue-400 mt-0.5">3.</span> Once approved, you can log in and start adding your MikroTik routers and subscribers.</div>
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="goStep(3)" class="px-5 py-2.5 border border-slate-700 text-slate-300 hover:text-white hover:border-slate-500 text-sm font-medium rounded-xl transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button type="submit" id="submit-btn"
                                    class="px-7 py-2.5 bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white text-sm font-bold rounded-xl shadow-xl shadow-emerald-600/20 transition flex items-center gap-2 active:scale-[0.98]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Create My ISP Account
                                </button>
                            </div>
                        </div>
                    </div>

                </form>

                <!-- Sign in link -->
                <div class="text-center mt-6 text-xs text-slate-500">
                    Already registered? <a href="{{ route('login') }}" class="text-blue-400 hover:underline font-medium">Sign in to your console</a>
                </div>

            </div>
        </main>

    </div>

<script>
let currentStep = 1;

function goStep(step) {
    // Hide current
    document.getElementById('step-' + currentStep).classList.remove('active');

    // Update indicators
    for (let i = 1; i <= 4; i++) {
        const ind = document.getElementById('ind-' + i);
        ind.classList.remove('done', 'active');
        if (i < step) {
            ind.classList.add('done');
            ind.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
        } else if (i === step) {
            ind.classList.add('active');
            ind.textContent = i;
        } else {
            ind.textContent = i;
        }
    }

    // Update connectors
    for (let i = 1; i <= 3; i++) {
        const conn = document.getElementById('conn-' + i);
        conn.classList.toggle('done', i < step);
    }

    // Show new step
    currentStep = step;
    document.getElementById('step-' + step).classList.add('active');

    // Populate review on step 4
    if (step === 4) populateReview();

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getVal(name) {
    const el = document.querySelector('[name="' + name + '"]');
    return el ? el.value : '—';
}

function getRadioVal(name) {
    const el = document.querySelector('[name="' + name + '"]:checked');
    return el ? el.value : '—';
}

function populateReview() {
    const planMap = { starter: 'Starter (Free)', professional: 'Professional (KES 4,999/mo)', enterprise: 'Enterprise (Custom)' };
    document.getElementById('rev-company').textContent = getVal('company_name') || '—';
    document.getElementById('rev-subdomain').textContent = getVal('subdomain') || '—';
    document.getElementById('rev-cemail').textContent = getVal('company_email') || '—';
    document.getElementById('rev-cphone').textContent = getVal('company_phone') || '—';
    document.getElementById('rev-plan').textContent = planMap[getVal('subscription_tier')] || '—';
    document.getElementById('rev-aname').textContent = getVal('admin_name') || '—';
    document.getElementById('rev-aemail').textContent = getVal('email') || '—';
    document.getElementById('rev-aphone').textContent = getVal('phone') || '—';
    document.getElementById('rev-nettype').textContent = getRadioVal('network_type') || '—';
    document.getElementById('rev-subs').textContent = getVal('approx_subscribers') || '—';
    document.getElementById('rev-router').textContent = getVal('router_brand') || '—';
}

// Auto-generate subdomain from company name
document.getElementById('company_name').addEventListener('input', function() {
    const sub = document.getElementById('subdomain');
    if (!sub.dataset.edited) {
        sub.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('subdomain').addEventListener('input', function() {
    this.dataset.edited = 'true';
});

// Password strength hint
document.getElementById('pw1').addEventListener('input', function() {
    const hint = document.getElementById('pw-hint');
    const len = this.value.length;
    if (len === 0) { hint.textContent = 'Use at least 8 characters with letters and numbers.'; hint.className = 'text-[11px] text-slate-500'; }
    else if (len < 8) { hint.textContent = 'Too short — add more characters.'; hint.className = 'text-[11px] text-rose-400'; }
    else if (len < 12) { hint.textContent = 'Good — consider adding symbols for extra security.'; hint.className = 'text-[11px] text-amber-400'; }
    else { hint.textContent = 'Strong password!'; hint.className = 'text-[11px] text-emerald-400'; }
});

// Submit button loading state
document.getElementById('reg-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Creating Account...';
});
</script>
</body>
</html>

