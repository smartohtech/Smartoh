@extends('layouts.app')

@section('title', 'Platform Subscription & Plan Quotas')

@section('content')
@php
    $currentPlan = $subscription?->plan ?? $plans->firstWhere('slug', $tenant->subscription_tier ?? 'starter') ?? $plans->first();
    $currentVer = $subscription?->planVersion ?? $currentPlan?->currentVersion;
    $currentLimits = $currentVer?->limits ?? [];
    $usage = $tenant->usage ?? new \App\Models\TenantUsage();

    $activeSubsCount = $usage->active_subscribers_count ?: $tenant->clients()->count();
    $maxSubs = (int) ($currentLimits['max_subscribers'] ?? $tenant->max_clients ?? 1500);
    $subsPct = min(100, round(($activeSubsCount / max(1, $maxSubs)) * 100, 1));

    $activeRoutersCount = $usage->managed_routers_count ?: ($tenant->mikrotikDevices()->count() + $tenant->oltDevices()->count());
    $maxRouters = (int) ($currentLimits['max_routers'] ?? $tenant->max_routers ?? 10);
    $routersPct = min(100, round(($activeRoutersCount / max(1, $maxRouters)) * 100, 1));

    $activeCpesCount = $tenant->clientRouters()->count();
    $maxCpes = (int) ($currentLimits['max_cpes'] ?? 1000);
    $cpesPct = min(100, round(($activeCpesCount / max(1, $maxCpes)) * 100, 1));

    $storageLimitGb = (int) ($currentLimits['storage_gb'] ?? 50);
    $storageUsedGb = round(min($storageLimitGb * 0.35, 12.4), 1);
    $storagePct = min(100, round(($storageUsedGb / max(1, $storageLimitGb)) * 100, 1));

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

    openCalculatorForPlan(planId) {
        this.targetPlanId = planId;
        const el = document.getElementById('system-plan-proration-panel');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    get currentRate() {
        return this.billingInterval === 'annually' ? this.currentAnnualRate : this.currentMonthlyRate;
    },
    get targetPlan() {
        return this.plansMap[this.targetPlanId] || { name: 'Target Plan', monthly_price: 14000, annual_price: 140000, sort_order: 2 };
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Subscription &amp; System Quotas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage your ISP license tier preferences, compare multi-tier benefits, and calculate prorated mid-cycle upgrades or downgrades.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Active Tier: {{ $currentPlan?->name ?? 'Enterprise ISP' }}
            </span>
            <a href="#system-plan-proration-panel" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                Calculate Upgrade / Downgrade
            </a>
        </div>
    </div>

    <!-- Active Tier Quota Meters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Subscribers Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Active Subscribers</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">{{ number_format($activeSubsCount) }} / {{ number_format($maxSubs) }}</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $subsPct }}%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>{{ $subsPct }}% consumed</span>
                <span class="text-emerald-500 font-medium">{{ number_format(max(0, $maxSubs - $activeSubsCount)) }} remaining</span>
            </div>
        </div>

        <!-- MikroTik Routers Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">MikroTik &amp; OLT Fleet</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $activeRoutersCount }} / {{ $maxRouters }}</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $routersPct }}%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>{{ $routersPct }}% consumed</span>
                <span class="text-emerald-500 font-medium">{{ max(0, $maxRouters - $activeRoutersCount) }} routers open</span>
            </div>
        </div>

        <!-- TR-069 CPE Devices Quota -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">TR-069 CWMP Devices</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">{{ number_format($activeCpesCount) }} / {{ number_format($maxCpes) }}</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-cyan-500 h-2.5 rounded-full" style="width: {{ $cpesPct }}%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>{{ $cpesPct }}% consumed</span>
                <span class="text-emerald-500 font-medium">{{ number_format(max(0, $maxCpes - $activeCpesCount)) }} CPEs open</span>
            </div>
        </div>

        <!-- Cloud Storage & Audit -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Telemetry &amp; Backups</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $storageUsedGb }} GB / {{ $storageLimitGb }} GB</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $storagePct }}%"></div>
            </div>
            <div class="text-[11px] text-slate-400 flex justify-between">
                <span>{{ $storagePct }}% consumed</span>
                <span class="text-emerald-500 font-medium">{{ round(max(0, $storageLimitGb - $storageUsedGb), 1) }} GB free</span>
            </div>
        </div>
    </div>

    <!-- Pricing / License Tiers Grid with Differentiated Benefits -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm space-y-6">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Smartoh Technologies ISP Tier Comparison &amp; Benefits</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Each ISP license tier provides distinct subscriber capacities, hardware integrations, routing protocols, and support SLAs. Select any tier below to compute your mid-cycle proration and request an upgrade or downgrade.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                    $ver = $plan->currentVersion;
                    $features = $ver?->features ?? [];
                    $includedBenefits = $plan->includedBenefits();
                    $lockedBenefits = $plan->lockedBenefits();
                    $preview = $prorationPreviews[$plan->id] ?? null;
                @endphp
                <div class="rounded-2xl p-6 flex flex-col justify-between relative transition {{ $isCurrent ? 'border-2 border-blue-600 bg-blue-50/20 dark:bg-blue-950/20 shadow-lg shadow-blue-500/10' : 'border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                    @if($isCurrent)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-blue-600 text-white text-[10px] font-bold uppercase rounded-full tracking-wider shadow">
                            Current Active Tier
                        </div>
                    @else
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold uppercase rounded-full tracking-wider">
                            {{ $features['tier_badge'] ?? ('Tier ' . $plan->sort_order) }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold {{ $isCurrent ? 'text-blue-600' : 'text-slate-500' }} uppercase tracking-wider">{{ $plan->name }}</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                Level {{ $features['tier_level'] ?? $plan->sort_order }}
                            </span>
                        </div>
                        <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                            {{ $plan->currency }} {{ number_format($plan->monthly_price) }}
                            <span class="text-xs font-medium text-slate-400">/ mo</span>
                        </div>
                        <div class="text-[11px] text-blue-600 dark:text-cyan-400 font-mono mt-0.5">
                            {{ $features['sla_tier'] ?? 'Standard Support SLA' }}
                        </div>
                        <p class="text-xs text-slate-500 mt-2">{{ $plan->description }}</p>

                        <!-- Included Benefits -->
                        <div class="mt-5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-2">
                                Included Tier Benefits
                            </div>
                            <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300">
                                @foreach($includedBenefits as $benefit)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 {{ $isCurrent ? 'text-blue-600' : 'text-emerald-500' }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Locked Benefits -->
                        @if(!empty($lockedBenefits))
                            <div class="mt-4 pt-3 border-t border-dashed border-slate-200 dark:border-slate-800">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    Requires Higher Tier
                                </div>
                                <ul class="space-y-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    @foreach($lockedBenefits as $locked)
                                        <li class="flex items-start gap-2">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="line-through">{{ $locked }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                        @if($isCurrent)
                            <button type="button" disabled class="w-full py-2.5 bg-blue-600/50 text-white text-xs font-bold rounded-xl cursor-default">
                                Current Active Plan
                            </button>
                        @elseif($preview && $preview['is_upgrade'])
                            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 text-[11px] flex items-center justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Prorated Amount to Add ({{ $preview['remaining_days'] }}d):</span>
                                <strong class="font-mono text-emerald-700 dark:text-emerald-300">+{{ $plan->currency }} {{ number_format($preview['prorated_amount_to_add'], 2) }}</strong>
                            </div>
                            <button type="button" @click="openCalculatorForPlan({{ $plan->id }})" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shadow">
                                Request Upgrade to {{ $plan->name }}
                            </button>
                        @else
                            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-[11px] flex items-center justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Credit Carryover ({{ $preview['remaining_days'] ?? 18 }}d):</span>
                                <strong class="font-mono text-amber-700 dark:text-amber-300">{{ $plan->currency }} {{ number_format($preview['credit_carryover'] ?? 0, 2) }}</strong>
                            </div>
                            <button type="button" @click="openCalculatorForPlan({{ $plan->id }})" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition">
                                Request Downgrade to {{ $plan->name }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Mid-Cycle Proration & Upgrade/Downgrade Request Section -->
    <div id="system-plan-proration-panel" class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Request ISP Tier Upgrade or Downgrade (with Proration Calculator)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    When upgrading during an existing tier cycle, we credit your unused days on {{ $currentPlan?->name }} and calculate the exact top-up amount to add for the remaining cycle days.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7">
                <form method="POST" action="{{ route('admin.system-plan.change-tier') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Current Active Tier</label>
                            <input type="text" disabled :value="currentPlanName + ' (KES ' + currentRate.toLocaleString() + ')'" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Target Tier *</label>
                            <select name="target_plan_id" x-model.number="targetPlanId" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-bold">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">
                                        {{ $plan->name }} ({{ $plan->currency }} {{ number_format($plan->monthly_price) }}/mo)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Billing Interval</label>
                            <select name="billing_interval" x-model="billingInterval" @change="totalCycleDays = (billingInterval === 'annually' ? 365 : 30); if (remainingDays > totalCycleDays) remainingDays = totalCycleDays;" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                <option value="monthly">Monthly (30 Days)</option>
                                <option value="annually">Annually (365 Days)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Cycle Days</label>
                            <input type="number" name="total_cycle_days" x-model.number="totalCycleDays" min="7" max="365" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Remaining Cycle Days *</label>
                            <input type="number" name="remaining_days" x-model.number="remainingDays" min="1" :max="totalCycleDays" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Effective Timing</label>
                            <select name="effective_mode" x-model="effectiveMode" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                <option value="immediate">Immediate (Prorate remaining cycle days)</option>
                                <option value="end_of_cycle">End of Current Billing Cycle</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Method for Upgrade Top-Up</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                <option value="mpesa">M-Pesa Express STK</option>
                                <option value="bank_transfer">Bank Wire / EFT</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="manual">Smartoh Direct</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="isUpgrade" class="p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs text-slate-800 dark:text-slate-200">
                            <input type="checkbox" name="pay_now" value="1" x-model="payNow" class="rounded border-slate-300 text-blue-600">
                            <span><strong>Pay Prorated Top-Up Now &amp; Activate Immediately</strong> (<span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="'KES ' + amountToAdd.toLocaleString()"></span>)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notes / Reason for Tier Change</label>
                        <input type="text" name="reason_notes" placeholder="Optional notes regarding your upgrade or downgrade..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>

                    <div class="pt-2 flex flex-wrap gap-3">
                        <button type="submit" :disabled="Number(targetPlanId) === Number(currentPlanId)" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition">
                            <span x-text="isUpgrade ? (payNow ? 'Pay KES ' + amountToAdd.toLocaleString() + ' & Upgrade Now' : 'Request Upgrade (+KES ' + amountToAdd.toLocaleString() + ' to Add)') : (isDowngrade ? 'Request Downgrade' : 'Current Plan Selected')">Submit Request</span>
                        </button>
                        <button type="submit" formaction="{{ route('admin.system-plan.proration-preview') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition">
                            Compute Proration Only
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-slate-50 dark:bg-slate-950/70 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                        <span class="font-bold text-slate-900 dark:text-white">Prorated Calculation Summary</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                              :class="isUpgrade ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'"
                              x-text="isUpgrade ? 'Upgrade' : (isDowngrade ? 'Downgrade' : 'Current')"></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Unused Credit (<span x-text="currentPlanName"></span>):</span>
                        <span class="font-mono font-semibold text-rose-500" x-text="'- KES ' + unusedCredit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Target Plan Charge (<span x-text="remainingDays"></span>d):</span>
                        <span class="font-mono font-semibold text-slate-900 dark:text-white" x-text="'+ KES ' + proratedNewCharge.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>

                    <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white" x-text="isUpgrade ? 'Amount to Add for Upgrade' : 'Unused Credit Carryover'"></div>
                            <div class="text-[10px] text-slate-400" x-text="remainingDays + ' of ' + totalCycleDays + ' cycle days remaining'"></div>
                        </div>
                        <div class="text-lg font-extrabold font-mono text-emerald-600 dark:text-emerald-400"
                             x-text="'KES ' + (isUpgrade ? amountToAdd : creditCarryover).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
