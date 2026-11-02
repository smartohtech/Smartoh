@extends('layouts.platform')

@section('title', 'SaaS Subscriptions & Trial Lifecycle')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">SaaS Subscriptions &amp; Trial Timers</h1>
            <p class="text-xs text-slate-400 mt-1">Autonomous 12-state subscription lifecycle engine tracking 7-day trials, grace periods, mid-cycle proration upgrades, and paid renewals.</p>
        </div>
    </div>

    <!-- Pending ISP Tier Upgrade / Downgrade Requests -->
    @if(isset($pendingChangeRequests) && $pendingChangeRequests->isNotEmpty())
    <div class="bg-slate-900/90 border border-cyan-500/40 rounded-2xl p-5 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-sm font-bold text-white flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                <span>Pending ISP Tier Upgrade / Downgrade Requests ({{ $pendingChangeRequests->count() }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-2.5 px-3">Ref #</th>
                        <th class="py-2.5 px-3">ISP Tenant</th>
                        <th class="py-2.5 px-3">Transition</th>
                        <th class="py-2.5 px-3">Remaining Days</th>
                        <th class="py-2.5 px-3">Prorated Adjustment</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($pendingChangeRequests as $req)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-cyan-400">{{ $req->reference_number }}</td>
                        <td class="py-2.5 px-3 text-white font-sans font-bold">{{ $req->tenant->name ?? 'ISP' }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-1.5 py-0.5 rounded text-[10px] uppercase {{ $req->isUpgrade() ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">{{ $req->change_type }}</span>
                            <span class="text-slate-300 ml-1">{{ $req->currentPlan->name ?? 'Old' }} &rarr; <strong class="text-white">{{ $req->targetPlan->name ?? 'New' }}</strong></span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-300">{{ $req->remaining_days }}/{{ $req->total_cycle_days }}d</td>
                        <td class="py-2.5 px-3">
                            @if($req->isUpgrade())
                                <span class="text-emerald-400 font-bold">+{{ $req->currency }} {{ number_format($req->prorated_amount_to_add, 2) }} to add</span>
                            @else
                                <span class="text-amber-400 font-bold">{{ $req->currency }} {{ number_format($req->credit_carryover, 2) }} credit</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/15 text-amber-300">{{ $req->status }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <form method="POST" action="{{ route('platform.subscriptions.change-requests.approve', $req) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[11px] font-sans font-bold transition">
                                    Approve &amp; Apply
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Filters Strip -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('platform.subscriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by ISP name or subdomain..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono text-xs">
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                    <option value="">All 12 States</option>
                    <option value="TRIAL_ACTIVE" {{ request('status') === 'TRIAL_ACTIVE' ? 'selected' : '' }}>TRIAL_ACTIVE</option>
                    <option value="TRIAL_EXPIRING" {{ request('status') === 'TRIAL_EXPIRING' ? 'selected' : '' }}>TRIAL_EXPIRING</option>
                    <option value="TRIAL_EXPIRED" {{ request('status') === 'TRIAL_EXPIRED' ? 'selected' : '' }}>TRIAL_EXPIRED</option>
                    <option value="ACTIVE_PAID" {{ request('status') === 'ACTIVE_PAID' ? 'selected' : '' }}>ACTIVE_PAID</option>
                    <option value="GRACE_PERIOD" {{ request('status') === 'GRACE_PERIOD' ? 'selected' : '' }}>GRACE_PERIOD</option>
                    <option value="PAST_DUE" {{ request('status') === 'PAST_DUE' ? 'selected' : '' }}>PAST_DUE</option>
                    <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl text-xs transition">
                    Filter States
                </button>
                <a href="{{ route('platform.subscriptions.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 font-mono uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Sub ID</th>
                        <th class="py-3.5 px-4">Tenant ISP</th>
                        <th class="py-3.5 px-4">Plan &amp; Version</th>
                        <th class="py-3.5 px-4">Billing Rate</th>
                        <th class="py-3.5 px-4">Trial / Renewal Timeline</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($subscriptions as $sub)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-cyan-400">#{{ $sub->id }}</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $sub->tenant->name ?? 'Unknown ISP' }}</div>
                            <div class="text-[10px] font-mono text-slate-400">{{ $sub->tenant->subdomain ?? 'unknown' }}.smartoh.co.ke</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs">
                            <span class="text-slate-200">{{ $sub->plan->name ?? 'Custom' }}</span>
                            <span class="text-slate-500 text-[10px] block">v{{ $sub->planVersion->version_number ?? 1 }}.0</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs">
                            <span class="text-white font-bold">{{ $sub->currency }} {{ number_format($sub->amount) }}</span>
                            <span class="text-slate-400 text-[10px] block">/{{ $sub->billing_interval }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px]">
                            @if($sub->isTrial())
                                <div class="text-cyan-400 font-bold">Ends: {{ $sub->trial_ends_at?->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $sub->daysLeftInTrial() }} days remaining</div>
                            @elseif($sub->status === 'ACTIVE_PAID')
                                <div class="text-emerald-400 font-bold">Renews: {{ $sub->current_period_end?->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">Paid contract active</div>
                            @elseif($sub->status === 'GRACE_PERIOD')
                                <div class="text-amber-400 font-bold">Grace Ends: {{ $sub->grace_period_ends_at?->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">48-hour buffer active</div>
                            @else
                                <div class="text-rose-400 font-bold">Expired: {{ $sub->trial_ends_at?->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">Awaiting payment</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono 
                                @if($sub->status === 'ACTIVE_PAID') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                @elseif(str_starts_with($sub->status, 'TRIAL')) bg-cyan-500/15 text-cyan-300 border border-cyan-500/30
                                @elseif($sub->status === 'GRACE_PERIOD') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                @else bg-rose-500/15 text-rose-300 border border-rose-500/30 @endif">
                                {{ $sub->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('platform.subscriptions.show', $sub) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-200 rounded-xl text-xs font-semibold transition">
                                Manage &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 font-mono text-xs">
                            No subscriptions match the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
