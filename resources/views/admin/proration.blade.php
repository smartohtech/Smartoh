@extends('layouts.app')

@section('title', 'Mid-Cycle Proration Calculator')

@section('content')
<div class="space-y-6" x-data="{
    clientCurrentPlanPrice: {{ $sampleClient?->billingPlan?->price ?? 2500 }},
    selectedNewPlanPrice: {{ $plans->first()?->price ?? 4000 }},
    remainingDays: 14,
    totalCycleDays: 30,
    get unusedCredit() {
        return Math.round((this.clientCurrentPlanPrice / this.totalCycleDays) * this.remainingDays);
    },
    get newCharge() {
        return Math.round((this.selectedNewPlanPrice / this.totalCycleDays) * this.remainingDays);
    },
    get netPayable() {
        return this.newCharge - this.unusedCredit;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Mid-Cycle Proration Calculator</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Compute exact financial adjustments for mid-month bandwidth upgrades, downgrades, and plan changes.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Daily Burn Rate Proration Active
            </span>
        </div>
    </div>

    <!-- Proration Calculation Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Interactive Calculator Form (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Subscriber Plan Migration</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Select target subscriber and the destination billing profile.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.proration.calculate') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subscriber Account</label>
                        <select name="client_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500">
                            @if($sampleClient)
                                <option value="{{ $sampleClient->id }}" selected>
                                    {{ $sampleClient->account_number }} - {{ $sampleClient->name }} (Current: {{ $sampleClient->billingPlan->name ?? 'Standard Plan' }} - KES {{ number_format($sampleClient->billingPlan->price ?? 2500, 2) }}/mo)
                                </option>
                            @else
                                <option value="1">ACC-001 - John Doe (Home Fiber 20M - KES 2,500/mo)</option>
                            @endif
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select New Destination Plan</label>
                            <select name="new_plan_id" @change="selectedNewPlanPrice = $event.target.options[$event.target.selectedIndex].dataset.price" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500">
                                @forelse($plans as $plan)
                                    <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                                        {{ $plan->name }} ({{ $plan->formatted_speed }}) - KES {{ number_format($plan->price, 2) }}
                                    </option>
                                @empty
                                    <option value="1" data-price="4000">Ultra Fiber 50M - KES 4,000</option>
                                    <option value="2" data-price="6000">Gigabit Turbo 100M - KES 6,000</option>
                                @endforelse
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Days Remaining in Current Cycle</label>
                            <input type="number" x-model.number="remainingDays" min="1" max="31" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Compute &amp; Generate Proration Invoice
                        </button>
                    </div>
                </form>
            </div>

            <!-- Proration Logic Explanation -->
            <div class="bg-blue-50/60 dark:bg-blue-950/30 rounded-2xl p-5 border border-blue-200 dark:border-blue-900/60 text-xs text-slate-600 dark:text-slate-300 space-y-2">
                <div class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    How Smartoh Technologies Proration Operates
                </div>
                <p>1. <strong>Unused Credit:</strong> The unspent portion of the current month is credited back to the subscriber based on remaining cycle days.</p>
                <p>2. <strong>New Plan Charge:</strong> The new higher/lower bandwidth tier is charged strictly for the remaining days of the current billing month.</p>
                <p>3. <strong>Automatic MikroTik Rate Update:</strong> Upon confirmation, the subscriber's Simple Queue or PPPoE profile is instantly updated via RouterOS API without requiring disconnection.</p>
            </div>

        </div>

        <!-- Right Column: Live Proration Breakdown Receipt (5 cols) -->
        <div class="lg:col-span-5 space-y-6">

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Proration Summary</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">Preview</span>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Billing Cycle Span:</span>
                        <span class="font-semibold text-slate-800 dark:text-white" x-text="totalCycleDays + ' Days'">30 Days</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Days Remaining:</span>
                        <span class="font-bold text-blue-600" x-text="remainingDays + ' Days'">14 Days</span>
                    </div>

                    <hr class="border-dashed border-slate-200 dark:border-slate-800">

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Unused Credit (Current Plan):</span>
                        <span class="font-mono font-semibold text-rose-500" x-text="'- KES ' + unusedCredit.toLocaleString()">- KES 1,167</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Pro-rated Fee (New Plan):</span>
                        <span class="font-mono font-semibold text-slate-800 dark:text-white" x-text="'+ KES ' + newCharge.toLocaleString()">+ KES 1,867</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300" x-text="netPayable >= 0 ? 'Net Upgrade Due Now' : 'Credit Carried Forward'">Net Upgrade Due Now</span>
                                <span class="block text-[10px] text-slate-400">Payable via M-Pesa STK Push</span>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-extrabold font-mono text-emerald-600 dark:text-emerald-400" x-text="'KES ' + Math.abs(netPayable).toLocaleString()">KES 700</span>
                            </div>
                        </div>
                    </div>

                    @if(session('proration_result'))
                    <div class="mt-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs space-y-1">
                        <div class="font-bold text-emerald-800 dark:text-emerald-300">Backend Calculation Executed:</div>
                        <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
                            <span>Remaining Days:</span>
                            <span class="font-mono font-bold">{{ session('proration_result')['remaining_days'] }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
                            <span>Unused Credit:</span>
                            <span class="font-mono">KES {{ session('proration_result')['unused_credit'] }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
                            <span>New Charge:</span>
                            <span class="font-mono">KES {{ session('proration_result')['new_charge'] }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-emerald-900 dark:text-emerald-200 pt-1 border-t border-emerald-200 dark:border-emerald-700">
                            <span>Net Adjustment:</span>
                            <span class="font-mono">KES {{ session('proration_result')['net_adjustment'] }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
