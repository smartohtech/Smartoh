<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISP Onboarding Registration - Smartoh Technologies</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4 sm:px-6 lg:px-8 selection:bg-cyan-600 selection:text-white">

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Top Brand Header -->
        <div class="text-center space-y-2">
            <a href="{{ route('landing') }}" class="inline-flex items-center space-x-2.5">
                <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-10 h-10 rounded-xl object-contain bg-white p-0.5 border border-slate-800 shadow-lg">
                <span class="text-xl font-extrabold text-white tracking-tight">Smartoh Technologies</span>
            </a>
            <h1 class="text-2xl font-black text-white tracking-tight">Start Your 7-Day Free ISP Trial</h1>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Join modern operators automating subscriber provisioning, M-Pesa collections, and core MikroTik routing.
            </p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-6">

            @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-xs space-y-1">
                <div class="font-bold">Please correct the following errors:</div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-6 text-xs">
                @csrf

                <!-- Section 1: Business Profile -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider font-mono border-b border-slate-800 pb-2">
                        1. ISP Business Identity
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Company / Brand Name *</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. Apex Fiber Networks" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Desired Workspace Subdomain *</label>
                            <div class="flex items-center">
                                <input type="text" name="subdomain" value="{{ old('subdomain') }}" required placeholder="apexfiber" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-l-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                                <span class="px-3 py-2.5 bg-slate-800 border border-l-0 border-slate-700/80 rounded-r-xl text-slate-400 font-mono text-xs">.smartoh.co.ke</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact Information -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider font-mono border-b border-slate-800 pb-2">
                        2. Administrator Credentials &amp; Contact
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}" required placeholder="e.g. Kevin Otieno" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Official Business Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@apexfiber.co.ke" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Primary Telephone Number *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+254 712 345678" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Operating Country *</label>
                            <input type="text" name="country" value="{{ old('country', 'Kenya') }}" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-medium text-slate-300 mb-1">Headquarters / Physical Location</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Town, Building, Office Suite..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Network Topology & Plan Selection -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider font-mono border-b border-slate-800 pb-2">
                        3. Infrastructure &amp; SaaS Tier Selection
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Network Topology *</label>
                            <select name="network_type" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                                <option value="Fiber FTTH / GPON">Fiber FTTH / GPON</option>
                                <option value="Wireless WISP / Fixed Wireless">Wireless WISP / PtMP</option>
                                <option value="Hybrid Fiber + Wireless">Hybrid Fiber + Wireless</option>
                                <option value="Metro Carrier / BGP Transit">Metro Carrier / BGP Transit</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Current Active Clients</label>
                            <input type="number" name="current_subscribers" value="{{ old('current_subscribers', 50) }}" min="0" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Core Gateway Hardware</label>
                            <input type="text" name="hardware_type" value="{{ old('hardware_type', 'MikroTik CCR2004') }}" placeholder="e.g. MikroTik CCR / CHR" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                        </div>
                    </div>

                    <!-- Plan Selection Grid -->
                    <div class="pt-2">
                        <label class="block font-medium text-slate-300 mb-2">Select Target SaaS Subscription Plan &amp; Benefit Level *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($plans as $plan)
                            @php
                                $checked = (old('saas_plan_id') == $plan->id || (!old('saas_plan_id') && $plan->slug === $selectedPlanSlug));
                                $pFeatures = $plan->currentVersion?->features ?? [];
                                $pBenefits = array_slice($plan->includedBenefits(), 0, 4);
                            @endphp
                            <label class="p-3.5 bg-slate-950 border rounded-2xl cursor-pointer transition flex flex-col justify-between space-y-2.5 {{ $checked ? 'border-cyan-500 bg-cyan-950/20 ring-1 ring-cyan-500/40' : 'border-slate-800 hover:border-slate-700' }}">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-white text-xs">{{ $plan->name }}</span>
                                        <input type="radio" name="saas_plan_id" value="{{ $plan->id }}" {{ $checked ? 'checked' : '' }} class="text-cyan-600 focus:ring-0">
                                    </div>
                                    <div class="font-mono text-cyan-400 font-extrabold text-sm">
                                        {{ $plan->currency }} {{ number_format($plan->monthly_price) }}<span class="text-[10px] text-slate-500 font-normal">/mo</span>
                                    </div>
                                    <div class="text-[10px] text-emerald-400 font-mono">
                                        {{ $pFeatures['tier_badge'] ?? ($plan->trial_days . '-Day Free Trial') }}
                                    </div>
                                    <ul class="space-y-1 pt-1 border-t border-slate-800/80 text-[10px] text-slate-300">
                                        @foreach($pBenefits as $benefitItem)
                                            <li class="flex items-start gap-1">
                                                <span class="text-cyan-400">✓</span>
                                                <span>{{ $benefitItem }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono pt-1 border-t border-slate-800/60">
                                    {{ $plan->trial_days }}-Day Free Trial • Prorated Upgrades
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Preferred Billing Cycle After Trial *</label>
                        <select name="billing_interval" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white font-mono focus:outline-none focus:border-cyan-500 text-xs">
                            <option value="monthly">Monthly Cycle (Flexible)</option>
                            <option value="annually">Annual Billing (2 Months Free Discount)</option>
                        </select>
                    </div>
                </div>

                <!-- Section 4: Security Password -->
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-cyan-400 uppercase tracking-wider font-mono border-b border-slate-800 pb-2">
                        4. Security Credentials
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Account Password *</label>
                            <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>

                        <div>
                            <label class="block font-medium text-slate-300 mb-1">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required minlength="8" placeholder="Re-type password" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="pt-2">
                    <label class="flex items-start space-x-2.5 cursor-pointer text-slate-300">
                        <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded bg-slate-950 border-slate-700 text-cyan-600 focus:ring-0">
                        <span class="text-[11px] leading-relaxed">
                            I accept the Smartoh Technologies terms of service, acceptable use policy, and understand my account will be reviewed by the Super Admin team prior to 7-day trial workspace activation.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold rounded-2xl shadow-xl shadow-cyan-600/30 transition text-xs tracking-wider uppercase font-mono">
                    Submit Application &amp; Start 7-Day Free Trial
                </button>
            </form>

            <div class="text-center text-xs text-slate-400 pt-2 border-t border-slate-800 flex items-center justify-between">
                <span>Already registered? <a href="{{ route('login') }}" class="text-cyan-400 hover:underline">Sign In</a></span>
                <a href="{{ route('application.status') }}" class="text-slate-400 hover:text-white underline">Check Application Status</a>
            </div>

        </div>

    </div>

</body>
</html>
