@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Automation</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">Automated Nudges</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Automated Payment Reminders</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated SMS &amp; email nudges for unpaid invoices and expiring PPPoE subscriptions.</p>
        </div>
    </div>

    <!-- Clients Due for Reminder (Matching Base44 Reminders.js) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Subscribers Due for Reminder</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Expiring within 3 days or currently overdue</p>
            </div>
            <span class="px-2.5 py-0.5 rounded text-xs font-bold font-mono bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                {{ $dueClients->count() }} Clients Due
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Phone Number</th>
                        <th class="p-4">Billing Plan</th>
                        <th class="p-4">Renewal Price</th>
                        <th class="p-4">Expiry Date</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($dueClients as $c)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-semibold text-slate-900 dark:text-white">{{ $c->first_name }} {{ $c->last_name }}</td>
                        <td class="p-4 font-mono text-slate-700 dark:text-slate-300">{{ $c->phone }}</td>
                        <td class="p-4">{{ $c->billingPlan?->name ?? 'Standard Plan' }}</td>
                        <td class="p-4 font-bold font-mono text-slate-900 dark:text-white">KES {{ number_format($c->billingPlan?->price ?? 0) }}</td>
                        <td class="p-4 font-mono text-amber-600 dark:text-amber-400">
                            {{ $c->current_cycle_end ? $c->current_cycle_end->format('M d, Y') : 'Overdue' }}
                        </td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('reminders.send') }}" class="inline">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ $c->id }}">
                                <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    Send Nudge Now
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No clients due for a reminder right now. All accounts are paid up.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Reminder Log -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Recent Automated Nudges</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Automated multi-channel delivery audit logs</p>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            @foreach($reminderHistory as $rem)
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                <div>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $rem->client_name }} ({{ $rem->phone }})</div>
                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $rem->channel }} • {{ $rem->type }}</div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        {{ $rem->status }}
                    </span>
                    <div class="text-[10px] text-slate-400 mt-1 font-mono">{{ $rem->sent_at }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
