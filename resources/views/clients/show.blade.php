@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Subscriber Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-xl shadow-cyan-500/20">
                {{ substr($client->name, 0, 2) }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $client->name }}</h1>
                    @if($client->status === 'active')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono">ACTIVE</span>
                    @elseif($client->status === 'suspended')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 font-mono">SUSPENDED</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 font-mono">{{ strtoupper($client->status) }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 font-mono mt-1">
                    <span class="text-cyan-400 font-bold">{{ $client->account_number }}</span>
                    <span>•</span>
                    <span>Phone: {{ $client->phone }}</span>
                    <span>•</span>
                    <span>Site: {{ $client->site?->name ?? 'Default Site' }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('clients.toggle-status', $client) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold rounded-xl border transition {{ $client->status === 'active' ? 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border-rose-500/30' : 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border-emerald-500/30' }}">
                    {{ $client->status === 'active' ? 'Suspend Service' : 'Reactivate Service' }}
                </button>
            </form>
            
            <form method="POST" action="{{ route('clients.generate-bill', $client) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl shadow-lg shadow-cyan-600/20 transition">
                    Generate Monthly Invoice
                </button>
            </form>
        </div>
    </div>

    <!-- Subscriber Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Plan & Usage -->
        <div class="space-y-6">
            
            <!-- Plan Card -->
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
                    <span>Active Subscription Plan</span>
                    <span class="text-cyan-400 font-mono">1:{{ $client->billingPlan?->contention_ratio ?? 1 }} Contention</span>
                </div>
                <div class="text-lg font-bold text-white">{{ $client->billingPlan?->name ?? 'No Plan Assigned' }}</div>
                <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl space-y-2 text-xs font-mono">
                    <div class="flex justify-between"><span class="text-slate-400">Provisioned Speed:</span><span class="text-cyan-400 font-bold">{{ $client->billingPlan?->formatted_speed ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Monthly Price:</span><span class="text-emerald-400 font-bold">KES {{ number_format($client->billingPlan?->price ?? 0, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">FUP Quota:</span><span class="text-slate-200">{{ $client->billingPlan?->fup_limit_gb ?? 500 }} GB</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Burst Limit:</span><span class="text-purple-400">{{ $client->billingPlan?->burst_limit ?? 'N/A' }}</span></div>
                </div>
            </div>

            <!-- PPPoE & Router Authentication Card -->
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Router & PPPoE Credentials</div>
                <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl space-y-2 text-xs font-mono">
                    <div class="flex justify-between"><span class="text-slate-400">Service:</span><span class="text-white uppercase">{{ $client->service_type }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">PPPoE User:</span><span class="text-cyan-400 font-bold">{{ $client->pppoe_username }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">PPPoE Password:</span><span class="text-amber-400 font-bold">{{ $client->pppoe_password }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Framed IP:</span><span class="text-slate-200">{{ $client->assigned_ip ?? 'Dynamic DHCP Pool' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Client MAC:</span><span class="text-slate-400">{{ $client->mac_address ?? 'Auto-bind on connect' }}</span></div>
                </div>
            </div>

            <!-- Optical Power (ONT) -->
            @if($client->ontDevice)
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
                    <span>GPON Optical Link (ONT)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono">
                        {{ strtoupper($client->ontDevice->status) }}
                    </span>
                </div>
                <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl space-y-2 text-xs font-mono">
                    <div class="flex justify-between"><span class="text-slate-400">ONT Serial:</span><span class="text-white">{{ $client->ontDevice->serial_number }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">OLT Uplink:</span><span class="text-cyan-400">{{ $client->ontDevice->olt?->name }} (Port {{ $client->ontDevice->pon_port }})</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">RX Power:</span><span class="text-emerald-400 font-bold">{{ $client->ontDevice->rx_power_dbm }} dBm</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">TX Power:</span><span class="text-slate-200">{{ $client->ontDevice->tx_power_dbm }} dBm</span></div>
                </div>
            </div>
            @endif

        </div>

        <!-- Column 2 & 3: Invoices, Payments & Tickets -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Invoices Table -->
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Invoices & Billing History</h3>
                    <span class="text-xs text-slate-400 font-mono">{{ count($client->invoices) }} Records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase">
                                <th class="pb-2">Invoice #</th>
                                <th class="pb-2">Issue Date</th>
                                <th class="pb-2">Due Date</th>
                                <th class="pb-2">Total (KES)</th>
                                <th class="pb-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            @forelse($client->invoices as $inv)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2.5 font-bold text-cyan-400">{{ $inv->invoice_number }}</td>
                                <td class="py-2.5 text-slate-400">{{ $inv->issue_date->format('d M Y') }}</td>
                                <td class="py-2.5 text-slate-400">{{ $inv->due_date->format('d M Y') }}</td>
                                <td class="py-2.5 text-white font-bold">{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $inv->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                                        {{ strtoupper($inv->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">No invoices generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Payment Transactions (M-Pesa / Bank)</h3>
                    <span class="text-xs text-slate-400 font-mono">{{ count($client->payments) }} Records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase">
                                <th class="pb-2">Receipt #</th>
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Method</th>
                                <th class="pb-2">Amount (KES)</th>
                                <th class="pb-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-mono">
                            @forelse($client->payments as $p)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2.5 font-bold text-white">{{ $p->external_transaction_id ?? $p->transaction_reference }}</td>
                                <td class="py-2.5 text-slate-400">{{ $p->created_at->format('d M Y H:i') }}</td>
                                <td class="py-2.5 text-cyan-400 uppercase">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                                <td class="py-2.5 text-emerald-400 font-bold">+{{ number_format($p->amount, 2) }}</td>
                                <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400">SUCCESS</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">No payments recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Support Tickets -->
            <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">Support & SLA Tickets</h3>
                    <span class="text-xs text-slate-400 font-mono">{{ count($client->supportTickets) }} Tickets</span>
                </div>
                <div class="space-y-3">
                    @forelse($client->supportTickets as $t)
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-cyan-400 font-mono">{{ $t->ticket_number }} • {{ $t->subject }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/10 text-amber-400 border border-amber-500/30 font-mono">{{ $t->status }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">Category: <span class="text-white">{{ str_replace('_', ' ', $t->category) }}</span> • Priority: <span class="text-white capitalize">{{ $t->priority }}</span></div>
                    </div>
                    @empty
                    <div class="py-4 text-center text-xs text-slate-500">Zero active support tickets.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
