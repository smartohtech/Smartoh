@extends('layouts.platform')

@section('title', 'ISP Tenants Directory')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">ISP Tenants &amp; Organizations</h1>
            <p class="text-xs text-slate-400 mt-1">Multi-tenant instances running autonomous billing, NOC monitoring, and network automation.</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('platform.tenants.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by ISP name, subdomain, or company email..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono text-xs">
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl text-xs transition">
                    Filter Tenants
                </button>
                <a href="{{ route('platform.tenants.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 font-mono uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Tenant ID</th>
                        <th class="py-3.5 px-4">ISP / Organization</th>
                        <th class="py-3.5 px-4">Subdomain / Domain</th>
                        <th class="py-3.5 px-4">SaaS Tier</th>
                        <th class="py-3.5 px-4">Subscription Status</th>
                        <th class="py-3.5 px-4">Tenant State</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($tenants as $t)
                    @php $sub = $t->activeSubscription; @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-cyan-400">
                            #{{ $t->id }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white flex items-center space-x-2">
                                <span>{{ $t->name }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $t->company_email }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-300">
                            {{ $t->subdomain }}.smartoh.co.ke
                        </td>
                        <td class="py-3.5 px-4 font-mono uppercase text-[11px]">
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 border border-slate-700">
                                {{ $sub->plan->name ?? $t->subscription_tier ?? 'Starter' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($sub)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono 
                                    @if($sub->status === 'ACTIVE_PAID') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                    @elseif(str_starts_with($sub->status, 'TRIAL')) bg-cyan-500/15 text-cyan-300 border border-cyan-500/30
                                    @elseif($sub->status === 'GRACE_PERIOD') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                    @else bg-rose-500/15 text-rose-300 border border-rose-500/30 @endif">
                                    {{ $sub->status }}
                                </span>
                                @if($sub->isTrial())
                                    <div class="text-[10px] font-mono text-cyan-400 mt-0.5">{{ $sub->daysLeftInTrial() }} days remaining</div>
                                @endif
                            @else
                                <span class="text-slate-500 font-mono text-[10px]">No Subscription</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($t->status === 'active')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                    ACTIVE
                                </span>
                            @elseif($t->status === 'pending')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                    PENDING
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-rose-500/15 text-rose-300 border border-rose-500/30">
                                    SUSPENDED
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('platform.tenants.show', $t) }}" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition" title="Inspect Tenant Details">
                                    Details
                                </a>
                                <a href="{{ route('platform.tenants.impersonate', $t) }}" class="px-2.5 py-1.5 bg-cyan-950/80 hover:bg-cyan-900 border border-cyan-500/40 text-cyan-300 rounded-xl text-xs font-semibold transition" title="Enter ISP Tenant Workspace as Super Admin">
                                    Enter &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 font-mono text-xs">
                            No tenants match the search filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
