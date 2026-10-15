@extends('layouts.app')

@section('content')
<div x-data="{ addModal: false }" class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Subscribers &amp; CRM</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage PPPoE, Hotspot, and Dedicated Fiber customer lifecycles.</p>
        </div>
        <button @click="addModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Register New Subscriber</span>
        </button>
    </div>

    <!-- Filter Pills & Search -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
            @foreach([
                'all' => 'All (' . $statusCounts['all'] . ')',
                'active' => 'Active (' . $statusCounts['active'] . ')',
                'suspended' => 'Suspended (' . $statusCounts['suspended'] . ')',
                'expiring_soon' => 'Expiring (' . $statusCounts['expired'] . ')',
                'fup_throttled' => 'FUP Throttled (' . $statusCounts['fup_throttled'] . ')',
                'overdue' => 'Overdue (' . $statusCounts['overdue'] . ')',
            ] as $key => $label)
                @php $isSelected = request('status', 'all') === $key || ($key === 'all' && !request('status')); @endphp
                <a href="{{ route('clients.index', array_merge(request()->query(), ['status' => $key === 'all' ? null : $key])) }}" class="px-3 py-1.5 text-xs rounded-lg font-medium transition {{ $isSelected ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-bold border border-blue-200 dark:border-blue-800' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('clients.index') }}" class="w-full md:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, account..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </form>

    </div>

    <!-- Subscribers Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Service &amp; Plan</th>
                        <th class="p-4">PPPoE / IP</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Cycle / Expiry</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($clients as $client)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        
                        <!-- Client Info -->
                        <td class="p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition">
                                        {{ $client->first_name }} {{ $client->last_name }}
                                    </a>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $client->account_number }} • {{ $client->phone }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Plan & Bandwidth -->
                        <td class="p-4">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $client->billingPlan?->name ?? 'Custom Plan' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ $client->billingPlan ? round($client->billingPlan->download_speed_kbps / 1024) . 'M / ' . round($client->billingPlan->upload_speed_kbps / 1024) . 'M' : 'Unlimited' }}
                            </div>
                        </td>

                        <!-- PPPoE / IP -->
                        <td class="p-4 font-mono text-xs">
                            <div class="text-blue-600 dark:text-blue-400">{{ $client->pppoe_username ?? 'dhcp-client' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $client->ip_address ?? '10.10.x.x' }}</div>
                        </td>

                        <!-- Status Pill -->
                        <td class="p-4">
                            @php
                                $statusClasses = [
                                    'active' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    'suspended' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                    'expired' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                    'fup_throttled' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-800',
                                ][$client->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            @endphp
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider border {{ $statusClasses }}">
                                @if($client->status === 'active')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                @endif
                                <span>{{ str_replace('_', ' ', $client->status) }}</span>
                            </span>
                        </td>

                        <!-- Cycle / Expiry -->
                        <td class="p-4 font-mono text-xs">
                            @if($client->current_cycle_end)
                                <div class="text-slate-800 dark:text-slate-200">{{ $client->current_cycle_end->format('M d, Y') }}</div>
                                <div class="text-[11px] {{ $client->current_cycle_end->isPast() ? 'text-rose-500' : 'text-slate-400' }}">
                                    {{ $client->current_cycle_end->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('clients.show', $client) }}" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>

                                <form method="POST" action="{{ route('clients.toggle-status', $client) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg transition {{ $client->status === 'active' ? 'text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}" title="{{ $client->status === 'active' ? 'Disconnect / Suspend' : 'Reconnect' }}">
                                        @if($client->status === 'active')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            No subscribers found matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $clients->links() }}
        </div>
        @endif
    </div>

    <!-- Registration Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Register New Subscriber</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('clients.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                        <input type="text" name="first_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number (M-Pesa) *</label>
                        <input type="text" name="phone" placeholder="254712345678" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Billing Plan *</label>
                        <select name="billing_plan_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} (KES {{ number_format($plan->price) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="pppoe">PPPoE Fiber / Wireless</option>
                            <option value="hotspot">Captive Portal Hotspot</option>
                            <option value="static_ip">Dedicated Static IP</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">PPPoE Username</label>
                        <input type="text" name="pppoe_username" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">PPPoE Password</label>
                        <input type="password" name="pppoe_password" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Physical Installation Address</label>
                    <input type="text" name="address" placeholder="Estate / House No / Road" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Enroll Subscriber
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
