@extends('layouts.app')

@section('title', 'SaaS Platform Subscription & Tier Proration')

@section('content')
@php
    $currentPlan = $subscription?->plan ?? $plans->firstWhere('slug', $tenant->subscription_tier ?? 'starter') ?? $plans->first();
    $currentVer = $subscription?->planVersion ?? $currentPlan?->currentVersion;
    $currentLimits = $currentVer?->limits ?? [];
    $usage = $tenant->usage ?? new \App\Models\TenantUsage();

    $defaultTargetPlan = $plans->first(fn($p) => $currentPlan && $p->id !== $currentPlan->id && $p->monthly_price > $currentPlan->monthly_price)
        ?? $plans->first(fn($p) => $currentPlan && $p->id !== $currentPlan->id)
        ?? $currentPlan;

    $defaultPreview = ($defaultTargetPlan && isset($prorationPreviews[$defaultTargetPlan->id]))
        ? $prorationPreviews[$defaultTargetPlan->id]
        : [
            'current_plan_rate' => $currentPlan?->monthly_price ?? 5000,
            'target_plan_rate' => $defaultTargetPlan?->monthly_price ?? 14000,
            'total_cycle_days' => 30,
            'remaining_days' => 18,
            'billing_interval' => $subscription?->billing_interval ?? 'monthly',
        ];

    $plansJson = $plans->mapWithKeys(function ($p) {
        return [$p->id => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'monthly_price' => (float) $p->monthly_price,
            'annual_price' => (float) $p->annual_price,
            'sort_order' => (int) $p->sort_order,
            'badge' => $p->currentVersion?->features['tier_badge'] ?? 'ISP Tier',
            'sla' => $p->currentVersion?->features['sla_tier'] ?? 'Standard SLA',
        ]];
    });
@endphp

<div class="space-y-6" x-data="{
    plansMap: {{ Js::from($plansJson) }},
    currentPlanId: {{ $currentPlan?->id ?? 1 }},
    currentPlanName: '{{ addslashes($currentPlan?->name ?? 'Starter ISP') }}',
    currentMonthlyRate: {{ (float) ($currentPlan?->monthly_price ?? 5000) }},
    currentAnnualRate: {{ (float) ($currentPlan?->annual_price ?? 50000) }},
    currentSortOrder: {{ (int) ($currentPlan?->sort_order ?? 1) }},
    targetPlanId: {{ session('tier_proration_result.target_plan_id') ?? ($defaultTargetPlan?->id ?? 1) }},
    billingInterval: '{{ session('tier_proration_result.billing_interval') ?? ($defaultPreview['billing_interval'] ?? 'monthly') }}',
    totalCycleDays: {{ (int) (session('tier_proration_result.total_cycle_days') ?? ($defaultPreview['total_cycle_days'] ?? 30)) }},
    remainingDays: {{ (int) (session('tier_proration_result.remaining_days') ?? ($defaultPreview['remaining_days'] ?? 18)) }},
    effectiveMode: 'immediate',
    payNow: false,
    paymentMethod: 'mpesa',

    selectTierForProration(planId, interval = null) {
        this.targetPlanId = planId;
        if (interval) {
            this.billingInterval = interval;
            this.totalCycleDays = interval === 'annually' ? 365 : 30;
        }
        const el = document.getElementById('isp-tier-proration-calculator');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    get currentRate() {
        return this.billingInterval === 'annually' ? this.currentAnnualRate : this.currentMonthlyRate;
    },
    get targetPlan() {
        return this.plansMap[this.targetPlanId] || { name: 'Selected Tier', monthly_price: 14000, annual_price: 140000, sort_order: 2 };
    },
    get targetRate() {
        return this.billingInterval === 'annually' ? Number(this.targetPlan.annual_price) : Number(this.targetPlan.monthly_price);
    },
    get dailyCurrentRate() {
        return this.totalCycleDays > 0 ? (this.currentRate / this.totalCycleDays) : 0;
    },
    get dailyTargetRate() {
        return this.totalCycleDays > 0 ? (this.targetRate / this.totalCycleDays) : 0;
    },
    get unusedCredit() {
        return Math.round(this.dailyCurrentRate * this.remainingDays * 100) / 100;
    },
    get proratedNewCharge() {
        return Math.round(this.dailyTargetRate * this.remainingDays * 100) / 100;
    },
    get netDifference() {
        return Math.round((this.proratedNewCharge - this.unusedCredit) * 100) / 100;
    },
    get isUpgrade() {
        return this.targetRate > this.currentRate || (this.targetRate === this.currentRate && Number(this.targetPlan.sort_order) > this.currentSortOrder);
    },
    get isDowngrade() {
        return Number(this.targetPlanId) !== Number(this.currentPlanId) && !this.isUpgrade;
    },
    get amountToAdd() {
        return this.isUpgrade ? Math.max(0, this.netDifference) : 0;
    },
    get creditCarryover() {
        return this.isDowngrade ? Math.abs(Math.min(0, this.netDifference)) : 0;
    }
}">

    <!-- Header & Status Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center flex-wrap gap-2.5 mb-1">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">ISP Plan Preferences, Benefits &amp; Proration</h1>
                @if($subscription)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ in_array(strtolower($subscription->status), ['active_paid']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : '' }}
                        {{ in_array(strtolower($subscription->status), ['trial_active', 'trial_expiring']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400' : '' }}
                        {{ in_array(strtolower($subscription->status), ['trial_expired', 'past_due', 'grace_period', 'payment_pending']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400' : '' }}
                        {{ in_array(strtolower($subscription->status), ['suspended', 'cancelled']) ? 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' : '' }}
                    ">
                        {{ str_replace('_', ' ', $subscription->status) }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Organization: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $tenant->name }}</span> (Tenant ID: #{{ $tenant->id }})
                • Active Tier: <span class="font-bold text-blue-600 dark:text-blue-400">{{ $currentPlan?->name ?? 'Starter ISP' }}</span>
                @if(!empty($tenant->settings['saas_credit_balance']))
                    • <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">Proration Credit Balance: KES {{ number_format($tenant->settings['saas_credit_balance'], 2) }}</span>
                @endif
            </p>
        </div>

        @if($subscription && $subscription->isTrial())
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
                    Expires {{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y H:i') : 'Soon' }}
                </div>
            </div>
        @elseif($subscription)
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-4 rounded-xl shadow-md min-w-[240px]">
                <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Active ISP License Tier</div>
                <div class="text-xl font-extrabold">{{ $currentPlan?->name ?? 'Custom' }}</div>
                <div class="text-[11px] text-emerald-100 mt-1">
                    Rate: {{ $subscription->currency }} {{ number_format($subscription->amount) }} / {{ $subscription->billing_interval }}
                    • Renews {{ $subscription->current_period_end ? $subscription->current_period_end->format('M d, Y') : 'N/A' }}
                </div>
            </div>
        @endif
    </div>

    <!-- Multi-Tier ISP Plan Preferences & Benefit Levels -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <span class="text-[11px] font-mono uppercase tracking-wider font-bold text-blue-600 dark:text-blue-400">Tiered ISP Entitlements &amp; Capabilities</span>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Smartoh ISP Plan Tiers &amp; Benefit Levels</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Each tier unlocks progressively higher subscriber capacity, hardware automation, NOC AI intelligence, and SLA guarantees. Upgrade or downgrade anytime with automatic mid-cycle proration.
                </p>
            </div>
            <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold self-start">
                <button type="button" @click="billingInterval = 'monthly'; totalCycleDays = 30" class="px-3.5 py-1.5 rounded-lg transition" :class="billingInterval === 'monthly' ? 'bg-blue-600 text-white shadow' : 'text-slate-600 dark:text-slate-400'">
                    Monthly Cycle
                </button>
                <button type="button" @click="billingInterval = 'annually'; totalCycleDays = 365" class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5" :class="billingInterval === 'annually' ? 'bg-blue-600 text-white shadow' : 'text-slate-600 dark:text-slate-400'">
                    <span>Annual Cycle</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 font-mono">2 Mo Free</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                    $ver = $plan->currentVersion;
                    $pLimits = $ver?->limits ?? [];
                    $features = $ver?->features ?? [];
                    $includedBenefits = $plan->includedBenefits();
                    $lockedBenefits = $plan->lockedBenefits();
                    $preview = $prorationPreviews[$plan->id] ?? null;
                @endphp
                <div class="rounded-2xl p-6 flex flex-col justify-between relative transition-all {{ $isCurrent ? 'border-2 border-blue-600 dark:border-blue-500 ring-4 ring-blue-500/10 bg-blue-50/20 dark:bg-blue-950/15 shadow-lg' : 'border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-blue-400 dark:hover:border-slate-700 shadow-sm' }}">
                    @if($isCurrent)
                        <div class="absolute -top-3 left-6 px-3 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-full bg-blue-600 text-white shadow">
                            Current Active Tier
                        </div>
                    @elseif($plan->slug === 'pro')
                        <div class="absolute -top-3 left-6 px-3 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow">
                            {{ $features['tier_badge'] ?? 'Most Popular for Scaling ISPs' }}
                        </div>
                    @else
                        <div class="absolute -top-3 left-6 px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $features['tier_badge'] ?? ('Tier ' . $plan->sort_order) }}
                        </div>
                    @endif

                    <div class="space-y-4 pt-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                                <span class="text-[11px] font-mono text-blue-600 dark:text-cyan-400 font-semibold">
                                    {{ $features['sla_tier'] ?? 'Standard Support SLA' }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                Level {{ $features['tier_level'] ?? $plan->sort_order }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 min-h-[34px]">{{ $plan->description }}</p>

                        <!-- Pricing Box -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-show="billingInterval === 'monthly'">
                                    {{ $plan->currency }} {{ number_format($plan->monthly_price) }}
                                </span>
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-show="billingInterval === 'annually'" x-cloak>
                                    {{ $plan->currency }} {{ number_format($plan->annual_price) }}
                                </span>
                                <span class="text-xs text-slate-500 font-mono" x-text="billingInterval === 'monthly' ? '/ month' : '/ year'">/ month</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between">
                                <span>Daily rate: <strong class="font-mono">{{ $plan->currency }} {{ number_format($plan->monthly_price / 30, 2) }}/day</strong></span>
                                <span class="text-blue-600 dark:text-cyan-400 font-mono">{{ $plan->trial_days }}d Free Trial</span>
                            </div>
                        </div>

                        <!-- Resource Quota Grid -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                                <div class="text-xs font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($pLimits['max_subscribers'] ?? 300) }}</div>
                                <div class="text-[10px] text-slate-500">Subscribers</div>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                                <div class="text-xs font-extrabold font-mono text-slate-900 dark:text-white">{{ $pLimits['max_routers'] ?? 3 }}</div>
                                <div class="text-[10px] text-slate-500">NAS / OLTs</div>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800">
                                <div class="text-xs font-extrabold font-mono text-slate-900 dark:text-white">{{ $pLimits['max_sites'] ?? 1 }} POPs</div>
                                <div class="text-[10px] text-slate-500">{{ $pLimits['max_staff'] ?? 3 }} Staff</div>
                            </div>
                        </div>

                        <!-- Included Tier Benefits -->
                        <div class="space-y-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Included Tier Benefits ({{ count($includedBenefits) }})
                            </div>
                            <ul class="text-xs space-y-2 text-slate-700 dark:text-slate-300">
                                @foreach($includedBenefits as $benefit)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Locked / Higher-Tier Benefits -->
                        @if(!empty($lockedBenefits))
                            <div class="space-y-1.5 border-t border-dashed border-slate-200 dark:border-slate-800 pt-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Unlocked in Higher Tiers
                                </div>
                                <ul class="text-[11px] space-y-1.5 text-slate-400 dark:text-slate-500">
                                    @foreach($lockedBenefits as $locked)
                                        <li class="flex items-start gap-2">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span class="line-through">{{ $locked }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Proration Preview & Upgrade / Downgrade CTA -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        @if($isCurrent)
                            <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-center text-[11px] text-blue-700 dark:text-blue-300 font-semibold">
                                Active Plan ({{ $preview['remaining_days'] ?? 18 }} of {{ $preview['total_cycle_days'] ?? 30 }} cycle days left)
                            </div>
                            <button type="button" disabled class="w-full py-2.5 bg-blue-600/40 text-white text-xs font-bold rounded-xl cursor-default">
                                Current Active Tier
                            </button>
                        @elseif($preview && $preview['is_upgrade'])
                            <div class="p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400">
                                    <span>Unused {{ $currentPlan?->name }} Credit:</span>
                                    <span class="font-mono text-rose-500">- {{ $plan->currency }} {{ number_format($preview['unused_credit'], 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400">
                                    <span>New Tier ({{ $preview['remaining_days'] }}d prorated):</span>
                                    <span class="font-mono">+ {{ $plan->currency }} {{ number_format($preview['prorated_new_charge'], 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between font-bold text-emerald-700 dark:text-emerald-300 pt-1 border-t border-emerald-200/60 dark:border-emerald-800/60">
                                    <span>Amount to Add Now:</span>
                                    <span class="font-mono text-sm">{{ $plan->currency }} {{ number_format($preview['prorated_amount_to_add'], 2) }}</span>
                                </div>
                            </div>
                            <button type="button" @click="selectTierForProration({{ $plan->id }})" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                <span>Upgrade to {{ $plan->name }} (+{{ $plan->currency }} {{ number_format($preview['prorated_amount_to_add']) }})</span>
                            </button>
                        @else
                            <div class="p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400">
                                    <span>Amount to Add Now:</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-white">{{ $plan->currency }} 0.00</span>
                                </div>
                                <div class="flex items-center justify-between font-bold text-amber-700 dark:text-amber-300">
                                    <span>Prorated Credit Carryover:</span>
                                    <span class="font-mono">{{ $plan->currency }} {{ number_format($preview['credit_carryover'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                            <button type="button" @click="selectTierForProration({{ $plan->id }})" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                <span>Request Downgrade to {{ $plan->name }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Interactive Mid-Cycle ISP Tier Upgrade / Downgrade & Proration Calculator -->
    <div id="isp-tier-proration-calculator" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Mid-Cycle ISP Tier Upgrade / Downgrade &amp; Proration Calculator</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Calculate the exact prorated amount to add when upgrading to a higher tier during your existing billing cycle, or request a tier downgrade.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 self-start sm:self-auto">
                Daily Burn-Rate Proration Engine
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left 7 Cols: Upgrade / Downgrade Request Form -->
            <div class="lg:col-span-7 space-y-5">
                <form method="POST" action="{{ route('billing.subscription.change-tier') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Current Active ISP Tier</label>
                            <input type="text" disabled :value="currentPlanName + ' (KES ' + currentRate.toLocaleString() + '/' + (billingInterval === 'annually' ? 'yr' : 'mo') + ')'" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Target Destination ISP Tier *</label>
                            <select name="target_plan_id" x-model.number="targetPlanId" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-blue-500">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">
                                        {{ $plan->name }} — {{ $plan->currency }} {{ number_format($plan->monthly_price) }}/mo {{ $currentPlan && $currentPlan->id === $plan->id ? '(Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Billing Interval</label>
                            <select name="billing_interval" x-model="billingInterval" @change="totalCycleDays = (billingInterval === 'annually' ? 365 : 30); if (remainingDays > totalCycleDays) remainingDays = totalCycleDays;" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium">
                                <option value="monthly">Monthly (30 Days)</option>
                                <option value="annually">Annually (365 Days)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Total Days in Cycle</label>
                            <input type="number" name="total_cycle_days" x-model.number="totalCycleDays" min="7" max="365" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Remaining Days in Existing Tier *</label>
                            <input type="number" name="remaining_days" x-model.number="remainingDays" min="1" :max="totalCycleDays" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">When Should Change Take Effect?</label>
                            <select name="effective_mode" x-model="effectiveMode" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                <option value="immediate">Immediate (Prorate remaining cycle days now)</option>
                                <option value="end_of_cycle">At End of Current Billing Cycle</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Preferred Top-Up Payment Method</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                <option value="mpesa">M-Pesa Express STK Push</option>
                                <option value="bank_transfer">Bank Wire / EFT / RTGS</option>
                                <option value="card">Corporate Visa / Mastercard</option>
                                <option value="manual">Smartoh Direct / Account Credit</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="isUpgrade" class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/70">
                        <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-800 dark:text-slate-200">
                            <input type="checkbox" name="pay_now" value="1" x-model="payNow" class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="font-bold">Pay Prorated Amount Now &amp; Activate Higher Tier Immediately</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                    If checked, your prorated top-up of <strong class="font-mono text-blue-600 dark:text-blue-400" x-text="'KES ' + amountToAdd.toLocaleString()"></strong> will be processed immediately and your ISP quotas will be upgraded right away. Leave unchecked to generate a Prorated Upgrade Request &amp; Invoice first.
                                </span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Request Notes / Operational Reason (Optional)</label>
                        <input type="text" name="reason_notes" placeholder="e.g. Expanding GPON OLT coverage to new estate and need 1,500 subscriber capacity..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <button type="submit" :disabled="Number(targetPlanId) === Number(currentPlanId)" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="isUpgrade ? (payNow ? 'Pay KES ' + amountToAdd.toLocaleString() + ' & Upgrade Tier Now' : 'Submit Upgrade Request (+KES ' + amountToAdd.toLocaleString() + ' Prorated)') : (isDowngrade ? 'Submit Downgrade Request' : 'Already on Selected Tier')">Submit Tier Change Request</span>
                        </button>

                        <button type="submit" formaction="{{ route('billing.subscription.proration-preview') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition">
                            Verify Server Proration Calculation
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right 5 Cols: Live Proration Financial Breakdown Receipt -->
            <div class="lg:col-span-5">
                <div class="bg-slate-50 dark:bg-slate-950/80 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-wider font-bold text-blue-600 dark:text-cyan-400">Proration Formula Breakdown</span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Mid-Cycle Tier Adjustment</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                              :class="isUpgrade ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : (isDowngrade ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-slate-200 text-slate-700')"
                              x-text="isUpgrade ? 'Tier Upgrade' : (isDowngrade ? 'Tier Downgrade' : 'Same Tier')">
                            Tier Upgrade
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Existing Tier Rate:</span>
                            <span class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="'KES ' + currentRate.toLocaleString() + ' (KES ' + dailyCurrentRate.toFixed(2) + '/day)'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Target Tier (<span x-text="targetPlan.name"></span>):</span>
                            <span class="font-mono font-semibold text-blue-600 dark:text-blue-400" x-text="'KES ' + targetRate.toLocaleString() + ' (KES ' + dailyTargetRate.toFixed(2) + '/day)'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Unexpired Cycle Duration:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white" x-text="remainingDays + ' of ' + totalCycleDays + ' Days'"></span>
                        </div>

                        <hr class="border-dashed border-slate-200 dark:border-slate-800">

                        <div class="flex justify-between">
                            <span class="text-slate-500">1. Unused Credit (Existing Tier):</span>
                            <span class="font-mono font-semibold text-rose-500" x-text="'- KES ' + unusedCredit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">2. Target Tier Charge (<span x-text="remainingDays"></span> days):</span>
                            <span class="font-mono font-semibold text-slate-900 dark:text-white" x-text="'+ KES ' + proratedNewCharge.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>

                        <!-- Highlighted Net Amount to Add -->
                        <div class="p-4 rounded-xl border mt-2"
                             :class="isUpgrade ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-amber-500/10 border-amber-500/30'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="isUpgrade ? 'Amount to Add for Upgrade' : 'Unused Credit Carryover'">Amount to Add for Upgrade</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400" x-text="isUpgrade ? 'Prorated top-up for remaining ' + remainingDays + ' days' : 'Credited toward next billing renewal'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black font-mono"
                                         :class="isUpgrade ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                                         x-text="'KES ' + (isUpgrade ? amountToAdd : creditCarryover).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                        KES 5,400.00
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(session('tier_proration_result'))
                            @php $res = session('tier_proration_result'); @endphp
                            <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-xs space-y-1">
                                <div class="font-bold text-blue-900 dark:text-blue-300">Server-Verified Proration Result:</div>
                                <div class="flex justify-between text-blue-800 dark:text-blue-300">
                                    <span>Transition:</span>
                                    <span class="font-semibold">{{ $res['current_plan_name'] }} &rarr; {{ $res['target_plan_name'] }}</span>
                                </div>
                                <div class="flex justify-between text-blue-800 dark:text-blue-300">
                                    <span>Unused Credit Deducted:</span>
                                    <span class="font-mono">- {{ $res['currency'] }} {{ number_format($res['unused_credit'], 2) }}</span>
                                </div>
                                <div class="flex justify-between text-blue-800 dark:text-blue-300">
                                    <span>New Tier Prorated Cost ({{ $res['remaining_days'] }}d):</span>
                                    <span class="font-mono">+ {{ $res['currency'] }} {{ number_format($res['prorated_new_charge'], 2) }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-blue-950 dark:text-white pt-1 border-t border-blue-200 dark:border-blue-800">
                                    <span>{{ $res['is_upgrade'] ? 'Net Amount to Add:' : 'Credit Carryover:' }}</span>
                                    <span class="font-mono">{{ $res['currency'] }} {{ number_format($res['is_upgrade'] ? $res['prorated_amount_to_add'] : $res['credit_carryover'], 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted ISP Plan Upgrade / Downgrade Requests -->
    @if(isset($changeRequests) && $changeRequests->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>ISP Plan Upgrade &amp; Downgrade Requests</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase tracking-wider text-[10px] font-mono">
                        <th class="py-3 px-3">Ref #</th>
                        <th class="py-3 px-3">Type</th>
                        <th class="py-3 px-3">Tier Transition</th>
                        <th class="py-3 px-3">Cycle Left</th>
                        <th class="py-3 px-3">Unused Credit</th>
                        <th class="py-3 px-3">New Prorated</th>
                        <th class="py-3 px-3">Amount to Add / Credit</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($changeRequests as $req)
                        <tr>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ $req->reference_number }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $req->isUpgrade() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ $req->change_type }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-800 dark:text-slate-200">
                                {{ $req->currentPlan?->name ?? 'Previous' }} &rarr; <strong class="text-blue-600 dark:text-blue-400">{{ $req->targetPlan?->name ?? 'Target' }}</strong>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-600 dark:text-slate-400">
                                {{ $req->remaining_days }}/{{ $req->total_cycle_days }}d
                            </td>
                            <td class="py-3 px-3 font-mono text-rose-500">
                                -{{ $req->currency }} {{ number_format($req->unused_credit, 2) }}
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-700 dark:text-slate-300">
                                +{{ $req->currency }} {{ number_format($req->prorated_new_charge, 2) }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold">
                                @if($req->isUpgrade())
                                    <span class="text-emerald-600 dark:text-emerald-400">+{{ $req->currency }} {{ number_format($req->prorated_amount_to_add, 2) }} to add</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-400">{{ $req->currency }} {{ number_format($req->credit_carryover, 2) }} credit</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $req->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400' : '' }}
                                    {{ in_array($req->status, ['pending_review', 'pending_payment']) ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400' : '' }}
                                    {{ in_array($req->status, ['rejected', 'cancelled']) ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : '' }}
                                ">
                                    {{ str_replace('_', ' ', $req->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($req->isPending())
                                    <div class="inline-flex items-center gap-1.5">
                                        @if($req->isUpgrade() && $req->prorated_amount_to_add > 0)
                                            <form method="POST" action="{{ route('billing.subscription.change-requests.pay', $req) }}">
                                                @csrf
                                                <input type="hidden" name="provider" value="mpesa">
                                                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold transition">
                                                    Pay {{ $req->currency }} {{ number_format($req->prorated_amount_to_add) }} &amp; Activate
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('billing.subscription.change-requests.cancel', $req) }}">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-slate-100 hover:bg-rose-100 dark:bg-slate-800 dark:hover:bg-rose-950/50 text-slate-600 hover:text-rose-600 dark:text-slate-400 rounded-lg text-[11px] font-medium transition">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] font-mono text-slate-400">{{ $req->applied_at ? $req->applied_at->format('M d, H:i') : '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Active Plan Resource Consumption & Standard Renewal Checkout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Current Plan Overview -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <span>Current Plan Specifications</span>
            </h2>

            @if($subscription && $currentPlan)
                <div class="space-y-3">
                    <div class="flex justify-between items-baseline py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500">Plan Tier</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $currentPlan->name }}</span>
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

                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Resource Allocation</h3>
                    
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Active Clients</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->active_subscribers_count ?? 0 }} / {{ number_format($currentLimits['subscribers'] ?? 300) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $subLimit = isset($currentLimits['subscribers']) && is_numeric($currentLimits['subscribers']) ? max(1, (int)$currentLimits['subscribers']) : 300;
                                $subPct = min(100, round((($usage->active_subscribers_count ?? 0) / $subLimit) * 100));
                            @endphp
                            <div class="bg-blue-600 h-full rounded-full" style="width: {{ $subPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Managed Routers &amp; OLTs</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->managed_routers_count ?? 0 }} / {{ $currentLimits['devices'] ?? 3 }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $devLimit = isset($currentLimits['devices']) && is_numeric($currentLimits['devices']) ? max(1, (int)$currentLimits['devices']) : 3;
                                $devPct = min(100, round((($usage->managed_routers_count ?? 0) / $devLimit) * 100));
                            @endphp
                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $devPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Staff Accounts</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $usage->active_staff_count ?? 1 }} / {{ $currentLimits['staff'] ?? 3 }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            @php
                                $staffLimit = isset($currentLimits['staff']) && is_numeric($currentLimits['staff']) ? max(1, (int)$currentLimits['staff']) : 3;
                                $staffPct = min(100, round((($usage->active_staff_count ?? 1) / $staffLimit) * 100));
                            @endphp
                            <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $staffPct }}%"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Payment & Full Cycle Renewal Checkout -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2 flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Renew Current Subscription / Complete Full Cycle Payment</span>
            </h2>
            <p class="text-xs text-slate-500 mb-6">
                Pay your Smartoh platform fee instantly via M-Pesa STK push, Bank Transfer, or Card. Transactions are verified and automatically update your account to <span class="font-semibold text-emerald-600">ACTIVE_PAID</span> status.
            </p>

            @if($subscription)
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700/60 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="text-xs text-slate-500">Amount Due for Full Term Renewal</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ $subscription->currency }} {{ number_format($subscription->amount) }}
                        </div>
                        <div class="text-[11px] text-slate-400">Plan: {{ $currentPlan->name ?? 'Standard' }} ({{ ucfirst($subscription->billing_interval) }})</div>
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
            @endif
        </div>
    </div>

    <!-- Platform Invoice History -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Platform Billing &amp; Proration Invoices</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-3">Invoice #</th>
                        <th class="py-3 px-3">Description / Notes</th>
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
                                {{ $inv->notes ?? 'Platform Subscription Invoice' }}
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900 dark:text-white">
                                {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400' : '' }}
                                    {{ $inv->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400' : '' }}
                                    {{ in_array($inv->status, ['overdue', 'failed', 'void']) ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-400' : '' }}
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
