@extends('layouts.platform')

@section('title', 'ISP Applications & Onboarding Approvals')

@section('content')
<div class="space-y-6">

    <!-- Header & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center space-x-2">
                <span>ISP Applications &amp; Onboarding Queue</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Review incoming registrations, perform KYC/technical checks, request info, or idempotently provision tenants.</p>
        </div>
    </div>

    <!-- Search & Filters Strip -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('platform.applications.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company, contact name, email, subdomain, or APP-#..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono text-xs">
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-cyan-500 text-xs">
                    <option value="">All Statuses</option>
                    <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Review</option>
                    <option value="info_requested" {{ request('status') === 'info_requested' ? 'selected' : '' }}>Info Requested</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved &amp; Active</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl text-xs transition">
                    Filter Queue
                </button>
                <a href="{{ route('platform.applications.index') }}" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 font-mono uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">App Ref</th>
                        <th class="py-3.5 px-4">ISP / Company</th>
                        <th class="py-3.5 px-4">Contact Person</th>
                        <th class="py-3.5 px-4">Network Specs</th>
                        <th class="py-3.5 px-4">Requested Plan</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($applications as $app)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-cyan-400">
                            {{ $app->application_number }}
                            <div class="text-[10px] text-slate-500 font-normal">{{ $app->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-white">{{ $app->company_name }}</div>
                            <div class="text-[11px] font-mono text-slate-400">{{ $app->subdomain }}.smartoh.co.ke</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-slate-200">{{ $app->contact_name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $app->email }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $app->phone }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-[11px]">
                            <span class="text-slate-300">{{ $app->network_type }}</span>
                            <div class="text-slate-500 text-[10px]">{{ number_format($app->current_subscribers) }} initial subscribers</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-200 border border-slate-700">
                                {{ $app->saasPlan->name ?? 'Custom Tier' }}
                            </span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ ucfirst($app->billing_interval) }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($app->isPending())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                    PENDING APPROVAL
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
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('platform.applications.show', $app) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-cyan-600 hover:text-white text-slate-200 rounded-xl text-xs font-semibold transition">
                                Inspect &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 font-mono text-xs">
                            No applications match the search or filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $applications->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
