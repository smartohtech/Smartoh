@extends('layouts.platform')

@section('title', 'Tenant Overview: ' . $tenant->name)

@section('content')
<div x-data="{ suspendModal: false }" class="space-y-6">

    <!-- Header & Action Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <a href="{{ route('platform.tenants.index') }}" class="hover:text-white">&larr; Back to Tenants</a>
                <span>•</span>
                <span class="font-mono text-cyan-400 font-bold">Tenant #{{ $tenant->id }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight mt-1 flex items-center space-x-2">
                <span>{{ $tenant->name }}</span>
                @if($tenant->status === 'active')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">ACTIVE</span>
                @elseif($tenant->status === 'pending')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">PENDING</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-rose-500/15 text-rose-300 border border-rose-500/30">SUSPENDED</span>
                @endif
            </h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $tenant->subdomain }}.smartoh.co.ke</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ route('platform.tenants.impersonate', $tenant) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span>Enter Workspace</span>
            </a>

            @if($tenant->status === 'active')
                <button @click="suspendModal = true" class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold rounded-xl transition">
                    Suspend Tenant
                </button>
            @elseif($tenant->status === 'suspended')
                <form method="POST" action="{{ route('platform.tenants.reinstate', $tenant) }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl transition">
                        Reinstate Tenant
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        @php $sub = $tenant->activeSubscription; @endphp
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-lg space-y-1">
            <div class="text-[10px] font-mono text-slate-500 uppercase">Subscription Status</div>
            <div class="text-lg font-bold font-mono text-cyan-400">{{ $sub->status ?? 'None' }}</div>
            <div class="text-[11px] text-slate-400">{{ $sub->plan->name ?? 'N/A' }}</div>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-lg space-y-1">
            <div class="text-[10px] font-mono text-slate-500 uppercase">Trial Remaining</div>
            <div class="text-lg font-bold font-mono text-white">
                {{ $sub && $sub->isTrial() ? $sub->daysLeftInTrial() . ' Days' : 'N/A' }}
            </div>
            <div class="text-[11px] text-slate-400 font-mono">{{ $sub?->trial_ends_at?->format('M d, Y') ?? 'No trial' }}</div>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-lg space-y-1">
            <div class="text-[10px] font-mono text-slate-500 uppercase">Staff Accounts</div>
            <div class="text-lg font-bold font-mono text-white">{{ $tenant->users->count() }}</div>
            <div class="text-[11px] text-slate-400">Assigned Administrators</div>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-lg space-y-1">
            <div class="text-[10px] font-mono text-slate-500 uppercase">Platform Invoices</div>
            <div class="text-lg font-bold font-mono text-emerald-400">{{ $tenant->saasInvoices->count() }}</div>
            <div class="text-[11px] text-slate-400">Generated Bills</div>
        </div>
    </div>

    <!-- 2 Columns: Tenant Staff vs SaaS Subscriptions & Invoices -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Col 1: Staff Users -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3">ISP Staff Members</h2>

            <div class="divide-y divide-slate-800/80">
                @forelse($tenant->users as $u)
                <div class="py-2.5 flex items-center justify-between text-xs font-mono">
                    <div>
                        <div class="font-bold text-white">{{ $u->name }}</div>
                        <div class="text-slate-400 text-[11px]">{{ $u->email }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] uppercase font-bold">{{ $u->role }}</span>
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $u->is_approved ? 'Approved' : 'Pending' }}</div>
                    </div>
                </div>
                @empty
                <div class="py-4 text-center text-xs text-slate-500 font-mono">No staff assigned to this tenant.</div>
                @endforelse
            </div>
        </div>

        <!-- Col 2: Subscriptions & Invoices -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3">SaaS Subscription &amp; Invoices</h2>

            <div class="space-y-3">
                @forelse($tenant->saasInvoices->take(4) as $inv)
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs font-mono">
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
                <div class="py-4 text-center text-xs text-slate-500 font-mono">No platform invoices generated yet.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Audit Trail -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
        <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3">Tenant Audit History</h2>
        <div class="space-y-2">
            @forelse($auditLogs as $log)
            <div class="p-2.5 bg-slate-950/70 border border-slate-800/80 rounded-xl flex items-center justify-between text-xs font-mono">
                <div class="flex items-center space-x-2 truncate">
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-bold">{{ $log->action }}</span>
                    <span class="text-slate-300 truncate">{{ $log->details }}</span>
                </div>
                <div class="text-[10px] text-slate-500 whitespace-nowrap ml-4">
                    {{ $log->created_at->format('M d, Y H:i') }}
                </div>
            </div>
            @empty
            <div class="py-4 text-center text-xs text-slate-500 font-mono">No audit events recorded for this tenant.</div>
            @endforelse
        </div>
    </div>

    <!-- SUSPEND MODAL -->
    <div x-show="suspendModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="suspendModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white text-rose-400">Suspend Tenant Workspace</h3>
                <button @click="suspendModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Suspending will restrict ISP administrator operational logins while <strong>strictly preserving all customer records, invoices, routers, and tickets</strong>.
            </p>

            <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Suspension Reason *</label>
                    <textarea name="reason" rows="3" required placeholder="Specify non-payment, terms violation, or request..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="suspendModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30">
                        Confirm Suspension
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
