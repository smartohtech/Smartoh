@extends('layouts.platform')

@section('title', 'Platform Executive Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-900 to-slate-950 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <div>
            <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Smartoh Platform Autonomous Control Center</h1>
            </div>
            <p class="text-xs text-slate-400 mt-1">Global oversight of tenant onboarding, 7-day trials, SaaS subscriptions, revenue, and infrastructure provisioning.</p>
        </div>
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('platform.applications.index', ['status' => 'pending_approval']) }}" class="px-3.5 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-amber-500/20 flex items-center space-x-1.5 transition">
                <span>Pending Approvals</span>
                <span class="px-1.5 py-0.2 rounded-full bg-slate-950 text-amber-300 text-[10px]">{{ $pendingApplications }}</span>
            </a>
            <a href="{{ route('platform.plans.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition">
                Manage Plans
            </a>
        </div>
    </div>

    <!-- 4 Key Telemetry Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total & Active ISPs -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="uppercase tracking-wider font-semibold text-[11px] text-slate-300">Registered ISPs</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {{ $approvedIsps }} Active
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono tracking-tight">{{ $totalIsps }}</div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ $pendingApplications }} Pending Review</span>
                <span class="text-rose-400">{{ $suspendedIsps }} Suspended</span>
            </div>
        </div>

        <!-- Card 2: 7-Day Trials Active -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="uppercase tracking-wider font-semibold text-[11px] text-cyan-400">7-Day Free Trials</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    Live Engine
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono tracking-tight">{{ $activeTrials }}</div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ $paidSubscriptions }} Paid Subscriptions</span>
                <span class="text-amber-400 font-mono">{{ $expiredTrials }} Expired</span>
            </div>
        </div>

        <!-- Card 3: Monthly Recurring Revenue (MRR) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="uppercase tracking-wider font-semibold text-[11px] text-indigo-400">Projected MRR</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    Recurring
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono tracking-tight">
                KES {{ number_format($mrr) }}
            </div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ $upcomingRenewals }} Renewing Soon</span>
                <span class="text-emerald-400 font-mono font-bold">+{{ $paidSubscriptions }} Active Contracts</span>
            </div>
        </div>

        <!-- Card 4: Total Revenue Collected -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="uppercase tracking-wider font-semibold text-[11px] text-emerald-400">Total Collected</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Verified
                </span>
            </div>
            <div class="text-3xl font-extrabold text-white font-mono tracking-tight">
                KES {{ number_format($totalRevenue) }}
            </div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ $successfulPaymentsCount }} Successful Payments</span>
                <span class="text-amber-400 font-mono">KES {{ number_format($pendingInvoicesSum) }} Due</span>
            </div>
        </div>

    </div>

    <!-- 2 Columns: Pending ISP Applications & Active Subscriptions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Col 1: Pending & Recent Applications -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <h2 class="text-sm font-bold text-white">Recent ISP Applications</h2>
                </div>
                <a href="{{ route('platform.applications.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono">
                    View All &rarr;
                </a>
            </div>

            @if($recentApplications->isEmpty())
                <div class="text-center py-8 text-xs text-slate-500 font-mono">No ISP applications received yet.</div>
            @else
                <div class="divide-y divide-slate-800/80">
                    @foreach($recentApplications as $app)
                    <div class="py-3 flex items-center justify-between text-xs hover:bg-slate-800/20 px-2 rounded-xl transition">
                        <div class="space-y-0.5">
                            <div class="font-bold text-white flex items-center space-x-2">
                                <span>{{ $app->company_name }}</span>
                                <span class="text-[10px] font-mono text-cyan-400">({{ $app->subdomain }}.smartoh.co.ke)</span>
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center space-x-2 font-mono">
                                <span>{{ $app->contact_name }}</span>
                                <span>•</span>
                                <span>{{ $app->saasPlan->name ?? 'Custom Tier' }}</span>
                                <span>•</span>
                                <span>{{ $app->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if($app->isPending())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                    PENDING
                                </span>
                            @elseif($app->isApproved())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                    APPROVED
                                </span>
                            @elseif($app->isInfoRequested())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-500/15 text-blue-300 border border-blue-500/30">
                                    INFO REQUESTED
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-rose-500/15 text-rose-300 border border-rose-500/30">
                                    REJECTED
                                </span>
                            @endif

                            <a href="{{ route('platform.applications.show', $app) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-300 rounded-lg text-[11px] transition font-medium">
                                Review &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Col 2: Active Subscriptions & 7-Day Trials -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    <h2 class="text-sm font-bold text-white">Live Subscriptions &amp; Trial Timers</h2>
                </div>
                <a href="{{ route('platform.subscriptions.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono">
                    View All &rarr;
                </a>
            </div>

            @if($recentSubscriptions->isEmpty())
                <div class="text-center py-8 text-xs text-slate-500 font-mono">No subscriptions activated yet.</div>
            @else
                <div class="divide-y divide-slate-800/80">
                    @foreach($recentSubscriptions as $sub)
                    <div class="py-3 flex items-center justify-between text-xs hover:bg-slate-800/20 px-2 rounded-xl transition">
                        <div class="space-y-0.5">
                            <div class="font-bold text-white flex items-center space-x-2">
                                <span>{{ $sub->tenant->name ?? 'Unknown ISP' }}</span>
                                <span class="text-[10px] font-mono text-slate-400">[{{ $sub->plan->name ?? 'Custom' }}]</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono flex items-center space-x-2">
                                <span>Amount: {{ $sub->currency }} {{ number_format($sub->amount) }}/{{ $sub->billing_interval }}</span>
                                @if($sub->isTrial())
                                <span>•</span>
                                <span class="text-cyan-400 font-bold">{{ $sub->daysLeftInTrial() }} days left</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono 
                                @if($sub->status === 'ACTIVE_PAID') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                @elseif(str_starts_with($sub->status, 'TRIAL')) bg-cyan-500/15 text-cyan-300 border border-cyan-500/30
                                @elseif($sub->status === 'GRACE_PERIOD') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                @else bg-rose-500/15 text-rose-300 border border-rose-500/30 @endif">
                                {{ $sub->status }}
                            </span>
                            <a href="{{ route('platform.subscriptions.show', $sub) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-300 rounded-lg text-[11px] transition font-medium">
                                Manage
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- Security & Audit Trail Feed -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-sm font-bold text-white flex items-center space-x-2">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Recent Platform Audit Stream</span>
            </h2>
            <a href="{{ route('platform.audit.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono">
                Full Security Log &rarr;
            </a>
        </div>

        <div class="space-y-2">
            @foreach($recentAuditLogs as $log)
            <div class="p-2.5 bg-slate-950/60 border border-slate-800/80 rounded-xl flex items-center justify-between text-xs font-mono">
                <div class="flex items-center space-x-2 truncate">
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-bold">{{ $log->action }}</span>
                    <span class="text-slate-300 truncate">{{ $log->details }}</span>
                </div>
                <div class="text-[10px] text-slate-500 whitespace-nowrap ml-4">
                    {{ $log->created_at->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
