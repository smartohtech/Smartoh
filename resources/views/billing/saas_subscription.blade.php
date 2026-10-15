@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Status Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SaaS Platform Subscription</h1>
                @if($subscription)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ in_array($subscription->status, ['active_paid']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : '' }}
                        {{ in_array($subscription->status, ['trial_active', 'trial_expiring']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400' : '' }}
                        {{ in_array($subscription->status, ['trial_expired', 'past_due', 'grace_period', 'payment_pending']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400' : '' }}
                        {{ in_array($subscription->status, ['suspended', 'cancelled']) ? 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' : '' }}
                    ">
                        {{ str_replace('_', ' ', $subscription->status) }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        No Active Subscription
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Organization: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $tenant->name }}</span> (Tenant ID: #{{ $tenant->id }})
            </p>
        </div>

        @if($subscription && in_array($subscription->status, ['trial_active', 'trial_expiring']))
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-4 rounded-xl shadow-md min-w-[240px]">
                <div class="text-xs uppercase tracking-wider text-blue-200 font-semibold mb-1">7-Day Free Trial</div>
                <div class="text-2xl font-extrabold">
                    @if($subscription->daysRemainingInTrial() > 0)
                        {{ $subscription->daysRemainingInTrial() }} Days Left
                    @else
                        {{ $subscription->hoursRemainingInTrial() }} Hours Left
                    @endif
                </div>
                <div class="text-[11px] text-blue-100 mt-1">
                    Expires {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y H:i T') : 'Soon' }}
                </div>
            </div>
        @elseif($subscription && $subscription->status === 'active_paid')
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-4 rounded-xl shadow-md min-w-[240px]">
                <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Active Paid Plan</div>
                <div class="text-2xl font-extrabold">{{ $subscription->plan->name ?? 'Custom' }}</div>
                <div class="text-[11px] text-emerald-100 mt-1">
                    Renews {{ $subscription->current_period_end ? $subscription->current_period_end->format('M d, Y') : 'N/A' }}
                </div>
            </div>
        @endif
    </div>

    <!-- Active Plan & Usage Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Current Plan Overview -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <span>Current Plan Specifications</span>
            </h2>

            @if($subscription && $subscription->plan)
                <div class="space-y-3">
                    <div class="flex justify-between items-baseline py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500">Plan Tier</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $subscription->plan->name }}</span>
                    </div>
                    <div class="flex justify-between items-baseline py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500">Subscription Rate</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                            {{ $subscription->currency }} {{ number_format($subscription->amount) }} / {{ $subscription->billing_interval }}
                        </span>
                    </div>
                    <div class="flex justify-between items-baseline py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500">Started Date</span>
                        <span class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ ($subscription->current_period_start ?? $subscription->trial_started_at ?? $subscription->created_at)?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-baseline py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500">Next Billing Renewal</span>
                        <span class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ $subscription->current_period_end ? $subscription->current_period_end->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                <!-- Limits & Entitlements -->
                @php
                    $limits = $subscription->planVersion->limits ?? $subscription->plan->currentVersion->limits ?? [];
                    $usage = $tenant->usage ?? new \App\Models\TenantUsage();
                @endphp
                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Resource Allocation</h3>
                    
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Active Clients</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->active_subscribers_count ?? 0 }} / {{ $limits['subscribers'] ?? '∞' }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $subLimit = isset($limits['subscribers']) && is_numeric($limits['subscribers']) ? (int)$limits['subscribers'] : 1;
                                $subPct = min(100, round((($usage->active_subscribers_count ?? 0) / $subLimit) * 100));
                            @endphp
                            <div class="bg-blue-600 h-full rounded-full" style="width: {{ $subPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Managed Routers &amp; OLTs</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->managed_routers_count ?? 0 }} / {{ $limits['devices'] ?? '∞' }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $devLimit = isset($limits['devices']) && is_numeric($limits['devices']) ? (int)$limits['devices'] : 1;
                                $devPct = min(100, round((($usage->managed_routers_count ?? 0) / $devLimit) * 100));
                            @endphp
                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $devPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Staff Accounts</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->active_staff_count ?? 1 }} / {{ $limits['staff'] ?? '∞' }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $staffLimit = isset($limits['staff']) && is_numeric($limits['staff']) ? (int)$limits['staff'] : 1;
                                $staffPct = min(100, round((($usage->active_staff_count ?? 1) / $staffLimit) * 100));
                            @endphp
                            <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $staffPct }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-500">Your organization does not currently have an active plan assigned.</p>
            @endif
        </div>

        <!-- Payment & Upgrade Checkout -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2 flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Renew Subscription / Complete Payment</span>
            </h2>
            <p class="text-xs text-slate-500 mb-6">
                Pay your Smartoh platform fee instantly via M-Pesa STK push, Bank Transfer, or Card. Transactions are verified and automatically update your account to <span class="font-semibold text-emerald-600">ACTIVE_PAID</span> status.
            </p>

            @if($subscription)
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700/60 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="text-xs text-slate-500">Amount Due for Next Term</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ $subscription->currency }} {{ number_format($subscription->amount) }}
                        </div>
                        <div class="text-[11px] text-slate-400">Plan: {{ $subscription->plan->name ?? 'Standard' }} ({{ ucfirst($subscription->billing_interval) }})</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                            Server-Side Verified
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('billing.subscription.pay') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Select Payment Method</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="relative flex flex-col items-center p-3 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-emerald-500 transition">
                                <input type="radio" name="provider" value="mpesa" class="peer sr-only" checked>
                                <span class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 mb-1"></span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">M-Pesa STK</span>
                                <span class="text-[10px] text-slate-400">Instant Push</span>
                            </label>
                            <label class="relative flex flex-col items-center p-3 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="provider" value="bank_transfer" class="peer sr-only">
                                <span class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 mb-1"></span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Bank Wire</span>
                                <span class="text-[10px] text-slate-400">EFT / RTGS</span>
                            </label>
                            <label class="relative flex flex-col items-center p-3 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-purple-500 transition">
                                <input type="radio" name="provider" value="card" class="peer sr-only">
                                <span class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-purple-600 peer-checked:bg-purple-600 mb-1"></span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Credit Card</span>
                                <span class="text-[10px] text-slate-400">Visa / Master</span>
                            </label>
                            <label class="relative flex flex-col items-center p-3 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-amber-500 transition">
                                <input type="radio" name="provider" value="manual" class="peer sr-only">
                                <span class="w-4 h-4 rounded-full border border-slate-300 peer-checked:border-amber-600 peer-checked:bg-amber-600 mb-1"></span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Smartoh Direct</span>
                                <span class="text-[10px] text-slate-400">Admin Reconcile</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Phone Number (for M-Pesa prompt)</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone ?? '254700000000' }}" placeholder="2547XXXXXXXX" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 font-mono">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Process Subscription Payment ({{ $subscription->currency }} {{ number_format($subscription->amount) }})</span>
                        </button>
                    </div>
                </form>
            @else
                <p class="text-xs text-slate-500">Please contact support or select a plan below to activate billing.</p>
            @endif
        </div>
    </div>

    <!-- Platform Plans Available for Upgrade -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Smartoh Platform Plan Tiers</h2>
            <p class="text-xs text-slate-500">Compare tiers and scale your ISP operations without disruption.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $subscription && $subscription->saas_plan_id === $plan->id;
                    $ver = $plan->currentVersion;
                    $pLimits = $ver->limits ?? [];
                @endphp
                <div class="border rounded-2xl p-5 relative transition {{ $isCurrent ? 'border-blue-600 dark:border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20 dark:bg-blue-950/10' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">
                    @if($isCurrent)
                        <span class="absolute top-3 right-3 px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-blue-600 text-white">
                            Current Plan
                        </span>
                    @endif
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px]">{{ $plan->description }}</p>

                    <div class="mt-4 mb-4">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $plan->currency }} {{ number_format($plan->monthly_price) }}</span>
                        <span class="text-xs text-slate-500">/ month</span>
                    </div>

                    <ul class="text-xs space-y-2 text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800 pt-4 mb-4">
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>{{ $pLimits['subscribers'] ?? 'Unlimited' }}</strong> Active Clients</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>{{ $pLimits['devices'] ?? 'Unlimited' }}</strong> MikroTik / OLT Devices</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Up to <strong>{{ $pLimits['staff'] ?? 'Unlimited' }}</strong> Staff Accounts</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>7-Day Free Trial Included</span>
                        </li>
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Platform Invoice History -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Platform Billing Invoices</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-3">Invoice #</th>
                        <th class="py-3 px-3">Period</th>
                        <th class="py-3 px-3">Amount</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Due Date</th>
                        <th class="py-3 px-3">Paid At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-400">
                                {{ $inv->period_start ? $inv->period_start->format('M d') : '' }} - {{ $inv->period_end ? $inv->period_end->format('M d, Y') : '' }}
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900 dark:text-white">
                                {{ $inv->currency }} {{ number_format($inv->total) }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400' : '' }}
                                    {{ $inv->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400' : '' }}
                                    {{ in_array($inv->status, ['overdue', 'failed']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-400' : '' }}
                                ">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : 'N/A' }}</td>
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ $inv->paid_at ? $inv->paid_at->format('M d, Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">
                                No SaaS platform invoices issued yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
