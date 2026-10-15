<div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 py-1 font-mono">Platform Core</div>

<a href="{{ route('platform.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.dashboard') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    <span>Executive Dashboard</span>
</a>

<a href="{{ route('platform.applications.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.applications.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <div class="flex items-center space-x-2.5">
        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>ISP Applications</span>
    </div>
    @php
        $pendingAppCount = \App\Models\IspApplication::where('status', 'pending_approval')->count();
    @endphp
    @if($pendingAppCount > 0)
    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
        {{ $pendingAppCount }}
    </span>
    @endif
</a>

<a href="{{ route('platform.tenants.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.tenants.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <span>ISP Tenants</span>
</a>

<div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 py-1 font-mono pt-3">Monetization & Plans</div>

<a href="{{ route('platform.plans.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.plans.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
    <span>SaaS Plans &amp; Tiers</span>
</a>

<a href="{{ route('platform.subscriptions.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.subscriptions.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Subscriptions &amp; Trials</span>
</a>

<a href="{{ route('platform.invoices.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.invoices.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
    <span>Invoices &amp; Revenue</span>
</a>

<div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 py-1 font-mono pt-3">Governance</div>

<a href="{{ route('platform.audit.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl transition {{ request()->routeIs('platform.audit.*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    <span>Security &amp; Audit Logs</span>
</a>
