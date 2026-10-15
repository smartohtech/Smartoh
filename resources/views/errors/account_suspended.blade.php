<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - Smartoh Technologies</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/smartoh_logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-900 border border-rose-500/30 rounded-2xl p-8 shadow-2xl text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center mx-auto text-rose-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>

        <div class="space-y-2">
            <h1 class="text-xl font-bold text-white">ISP Account Suspended</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                The workspace for <strong class="text-white">{{ $tenant->name ?? 'this ISP' }}</strong> has been temporarily suspended by the platform administration. All customer, network, and financial records are securely preserved.
            </p>
        </div>

        <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 text-[11px] text-slate-400 font-mono text-left">
            <div>Tenant ID: <span class="text-cyan-400">{{ $tenant->id ?? 'N/A' }}</span></div>
            <div>Subdomain: <span class="text-slate-300">{{ $tenant->subdomain ?? 'N/A' }}.smartoh.co.ke</span></div>
            <div>Status: <span class="text-rose-400 font-bold uppercase">{{ $tenant->status ?? 'SUSPENDED' }}</span></div>
        </div>

        <div class="space-y-3">
            <a href="mailto:support@smartoh.co.ke?subject=Account%20Suspension%20Inquiry%20{{ $tenant->name ?? '' }}" class="block w-full py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold text-xs rounded-xl transition shadow-lg shadow-blue-600/20">
                Contact Platform Support
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-200 underline">Sign Out</button>
            </form>
        </div>
    </div>
</body>
</html>
