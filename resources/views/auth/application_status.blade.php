<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - Smartoh Technologies</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4 sm:px-6 selection:bg-cyan-600 selection:text-white">

    <div class="max-w-xl mx-auto space-y-6">

        <!-- Top Header -->
        <div class="text-center space-y-2">
            <a href="{{ route('landing') }}" class="inline-flex items-center space-x-2.5">
                <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-10 h-10 rounded-xl object-contain bg-white p-0.5 border border-slate-800 shadow-lg">
                <span class="text-xl font-extrabold text-white tracking-tight">Smartoh Technologies</span>
            </a>
            <h1 class="text-2xl font-black text-white tracking-tight">ISP Onboarding Application Tracker</h1>
            <p class="text-xs text-slate-400">Track the review, approval, and workspace provisioning status of your ISP.</p>
        </div>

        <!-- Search Bar -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <form method="GET" action="{{ route('application.status') }}" class="space-y-3 text-xs">
                <label class="block font-medium text-slate-300">Enter your Application Number (APP-...) or Registered Email *</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="ref" value="{{ request('ref') }}" required placeholder="e.g. APP-20261015-ABCD or admin@isp.co.ke" class="flex-1 px-4 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-500">
                    <button type="submit" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl transition text-xs">
                        Lookup
                    </button>
                </div>
            </form>
        </div>

        <!-- Flash alerts -->
        @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-emerald-300 text-xs">
            {{ session('success') }}
        </div>
        @endif

        <!-- Application Result Details -->
        @if($application)
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase">{{ $application->application_number }}</span>
                    <h2 class="text-lg font-bold text-white">{{ $application->company_name }}</h2>
                    <span class="text-xs text-slate-400 font-mono">{{ $application->subdomain }}.smartoh.co.ke</span>
                </div>

                <div>
                    @if($application->isPending())
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                            PENDING APPROVAL
                        </span>
                    @elseif($application->isApproved())
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                            APPROVED &amp; ACTIVE
                        </span>
                    @elseif($application->isInfoRequested())
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-blue-500/15 text-blue-300 border border-blue-500/30">
                            ADDITIONAL INFO NEEDED
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-rose-500/15 text-rose-300 border border-rose-500/30">
                            APPLICATION REJECTED
                        </span>
                    @endif
                </div>
            </div>

            <!-- Details Section based on status -->
            @if($application->isPending())
                <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-2 text-xs">
                    <div class="font-bold text-amber-400">Application Under Review</div>
                    <p class="text-slate-300 leading-relaxed">
                        Our Super Admin team is currently verifying your business details and requested subdomain. Once approved, your <strong>7-day free trial</strong> will start immediately, and you will receive access credentials.
                    </p>
                </div>
            @elseif($application->isApproved())
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl space-y-3 text-xs">
                    <div class="font-bold text-emerald-400">Congratulations! Your ISP Workspace is Active.</div>
                    <p class="text-slate-300 leading-relaxed">
                        Your application was approved on <strong>{{ $application->reviewed_at?->format('M d, Y') }}</strong>. Your 7-day free trial on the {{ $application->saasPlan->name ?? 'selected' }} plan is running.
                    </p>
                    <a href="{{ route('login') }}" class="block w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-center rounded-xl transition text-xs shadow-lg shadow-emerald-600/20">
                        Sign In to Your ISP Workspace &rarr;
                    </a>
                </div>
            @elseif($application->isInfoRequested())
                <div class="space-y-4">
                    <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-2xl space-y-1.5 text-xs">
                        <div class="font-bold text-blue-400">Additional Information Requested:</div>
                        <p class="text-slate-200">{{ $application->info_request_notes }}</p>
                    </div>

                    <form method="POST" action="{{ route('application.respond', $application) }}" class="space-y-3 text-xs">
                        @csrf
                        <label class="block font-medium text-slate-300">Submit Requested Details / Documents *</label>
                        <textarea name="resubmitted_notes" rows="4" required placeholder="Provide the requested information here..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white text-xs"></textarea>

                        <button type="submit" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl transition text-xs shadow-lg shadow-cyan-600/20">
                            Resubmit Information for Review
                        </button>
                    </form>
                </div>
            @else
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl space-y-1.5 text-xs">
                    <div class="font-bold text-rose-400">Application Declined</div>
                    <p class="text-slate-300 leading-relaxed">{{ $application->rejection_reason }}</p>
                    <p class="text-slate-400 text-[11px] pt-1">If you have questions, please contact platform support at billing@smartoh.co.ke.</p>
                </div>
            @endif

        </div>
        @elseif(request('ref'))
        <div class="p-6 bg-slate-900 border border-slate-800 rounded-2xl text-center text-xs text-slate-400 font-mono">
            No application found matching ref: <span class="text-white">{{ request('ref') }}</span>. Please check your spelling and try again.
        </div>
        @endif

    </div>

</body>
</html>
