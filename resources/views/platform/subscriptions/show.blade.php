@extends('layouts.platform')

@section('title', 'Subscription #' . $subscription->id . ' - ' . ($subscription->tenant->name ?? 'ISP'))

@section('content')
<div x-data="{ extendModal: false, statusModal: false }" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('platform.subscriptions.index') }}" class="hover:text-white">&larr; Back to Subscriptions</a>
                <span>•</span>
                <span class="font-mono text-cyan-400 font-bold">Subscription #{{ $subscription->id }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight mt-1">
                {{ $subscription->tenant->name ?? 'Tenant ISP' }} • {{ $subscription->plan->name ?? 'Custom Tier' }}
            </h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">
                Current State: <strong class="text-cyan-400">{{ $subscription->status }}</strong> • Interval: {{ ucfirst($subscription->billing_interval) }}
            </p>
        </div>

        <div class="flex items-center space-x-2.5 flex-wrap gap-y-2">
            @if($subscription->isTrial())
            <button @click="extendModal = true" class="px-3.5 py-2 bg-cyan-950/80 hover:bg-cyan-900 border border-cyan-500/40 text-cyan-300 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Extend Free Trial</span>
            </button>
            @endif

            <button @click="statusModal = true" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs rounded-xl transition">
                Transition State
            </button>

            <form method="POST" action="{{ route('platform.subscriptions.invoice', $subscription) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span>Generate Invoice</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 font-mono text-xs">
        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-lg space-y-1">
            <span class="text-[10px] text-slate-500 uppercase block">Billing Rate</span>
            <span class="text-xl font-extrabold text-white block">{{ $subscription->currency }} {{ number_format($subscription->amount) }}</span>
            <span class="text-[10px] text-slate-400 block">per {{ $subscription->billing_interval }}</span>
        </div>

        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-lg space-y-1">
            <span class="text-[10px] text-slate-500 uppercase block">Trial Started</span>
            <span class="text-lg font-bold text-slate-200 block">{{ $subscription->trial_started_at?->format('M d, Y') ?? 'N/A' }}</span>
            <span class="text-[10px] text-slate-400 block">{{ $subscription->trial_started_at?->diffForHumans() ?? '' }}</span>
        </div>

        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-lg space-y-1">
            <span class="text-[10px] text-slate-500 uppercase block">Trial Expiry Target</span>
            <span class="text-lg font-bold text-cyan-400 block">{{ $subscription->trial_ends_at?->format('M d, Y') ?? 'N/A' }}</span>
            <span class="text-[10px] text-cyan-300 block font-bold">{{ $subscription->daysLeftInTrial() }} days remaining</span>
        </div>

        <div class="p-4 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-lg space-y-1">
            <span class="text-[10px] text-slate-500 uppercase block">Paid Contract End</span>
            <span class="text-lg font-bold text-emerald-400 block">{{ $subscription->current_period_end?->format('M d, Y') ?? 'N/A' }}</span>
            <span class="text-[10px] text-slate-400 block">{{ $subscription->status === 'ACTIVE_PAID' ? 'Renews automatically' : 'Requires Payment' }}</span>
        </div>
    </div>

    <!-- 2 Columns: Trial Lifecycle Event Stream vs Generated Platform Invoices -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Col 1: Trial Event Stream -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span>Trial Lifecycle Event Timeline</span>
            </h2>

            <div class="space-y-3 font-mono text-xs">
                @forelse($subscription->trialEvents as $event)
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded bg-cyan-950 border border-cyan-800 text-cyan-300 text-[10px] font-bold uppercase">
                            {{ $event->event_type }}
                        </span>
                        <span class="text-[10px] text-slate-500">{{ $event->recorded_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <p class="text-slate-300 text-[11px] font-sans">{{ $event->notes }}</p>
                </div>
                @empty
                <div class="py-6 text-center text-slate-500">No trial events recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Col 2: Platform Invoices -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Platform Invoices &amp; Invoiced Bills</span>
            </h2>

            <div class="space-y-3 font-mono text-xs">
                @forelse($subscription->invoices as $inv)
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-white">{{ $inv->invoice_number }}</div>
                        <div class="text-[10px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-white">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</div>
                        <span class="px-1.5 py-0.2 rounded text-[10px] uppercase {{ $inv->isPaid() ? 'text-emerald-400 bg-emerald-500/10' : 'text-amber-400 bg-amber-500/10' }}">
                            {{ $inv->status }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-500">No invoices issued for this subscription.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODAL 1: Extend Trial -->
    <div x-show="extendModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="extendModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Extend Free Trial Duration</h3>
                <button @click="extendModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('platform.subscriptions.extend-trial', $subscription) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Additional Days to Add *</label>
                    <input type="number" name="extra_days" min="1" max="30" value="7" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Reason for Extension *</label>
                    <textarea name="reason" rows="2" required placeholder="e.g. ISP requested fiber trenching delay buffer..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="extendModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Confirm Extension
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Manual Status Transition -->
    <div x-show="statusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="statusModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Manually Transition Subscription State</h3>
                <button @click="statusModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('platform.subscriptions.update-status', $subscription) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Select New State *</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                        <option value="TRIAL_ACTIVE" {{ $subscription->status === 'TRIAL_ACTIVE' ? 'selected' : '' }}>TRIAL_ACTIVE</option>
                        <option value="TRIAL_EXPIRING" {{ $subscription->status === 'TRIAL_EXPIRING' ? 'selected' : '' }}>TRIAL_EXPIRING</option>
                        <option value="TRIAL_EXPIRED" {{ $subscription->status === 'TRIAL_EXPIRED' ? 'selected' : '' }}>TRIAL_EXPIRED</option>
                        <option value="ACTIVE_PAID" {{ $subscription->status === 'ACTIVE_PAID' ? 'selected' : '' }}>ACTIVE_PAID</option>
                        <option value="GRACE_PERIOD" {{ $subscription->status === 'GRACE_PERIOD' ? 'selected' : '' }}>GRACE_PERIOD</option>
                        <option value="PAST_DUE" {{ $subscription->status === 'PAST_DUE' ? 'selected' : '' }}>PAST_DUE</option>
                        <option value="PAYMENT_PENDING" {{ $subscription->status === 'PAYMENT_PENDING' ? 'selected' : '' }}>PAYMENT_PENDING</option>
                        <option value="SUSPENDED" {{ $subscription->status === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                        <option value="CANCELLED" {{ $subscription->status === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Administrative Notes (Audit Trail)</label>
                    <textarea name="notes" rows="2" placeholder="State reason for manual state change..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="statusModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Apply State
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
