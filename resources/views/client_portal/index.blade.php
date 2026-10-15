@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showTicketModal: false,
    showMpesaModal: false,
    stkPushTriggered: false,
    mpesaAmount: {{ $client->billingPlan?->price ?: 2500 }}
}">

    <!-- Top Subscriber Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-cyan-950/40 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start sm:items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-cyan-500/20 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold text-xl shadow-lg shadow-cyan-500/10 shrink-0">
                    {{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                            Welcome back, {{ $client->first_name }} {{ $client->last_name }}
                        </h1>
                        @if($client->status === 'active')
                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Service Active</span>
                        </span>
                        @elseif($client->status === 'suspended')
                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs bg-rose-500/10 text-rose-400 border border-rose-500/30 font-medium">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            <span>Suspended (Due Balance)</span>
                        </span>
                        @else
                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs bg-amber-500/10 text-amber-400 border border-amber-500/30 font-medium">
                            <span>Pending Provisioning</span>
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-400 font-mono">
                        <div>Account ID: <span class="text-white">{{ $client->account_number }}</span></div>
                        <span>•</span>
                        <div>PPPoE User: <span class="text-cyan-400">{{ $client->pppoe_username ?? 'dhcp-client' }}</span></div>
                        <span>•</span>
                        <div>Plan: <span class="text-white font-medium">{{ $client->billingPlan?->name ?? 'Standard Home Fiber' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Quick Payment & Voucher Actions -->
            <div class="flex items-center space-x-3">
                <button @click="showMpesaModal = true" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-emerald-500/20 flex items-center space-x-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Pay with M-Pesa</span>
                </button>

                <button @click="showTicketModal = true" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-xl border border-slate-700 flex items-center space-x-2 transition">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <span>Support Ticket</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Grid: Billing Cycle & Bandwidth Quota -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Billing Cycle Progress Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-sm backdrop-blur flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Subscription Cycle</span>
                    <span class="text-[11px] font-mono text-cyan-400">{{ $daysRemaining }} days remaining</span>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <div class="text-2xl font-bold text-white">
                        KES {{ number_format($client->billingPlan?->price ?? 0) }}
                    </div>
                    <span class="text-xs text-slate-400">/ monthly renewal</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 mt-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $cycleProgressPct }}%"></div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between font-mono">
                <span>Cycle Ends: {{ $client->current_cycle_end ? $client->current_cycle_end->format('M d, Y') : 'Active Ongoing' }}</span>
                <span class="{{ $client->status === 'active' ? 'text-emerald-400' : 'text-rose-400' }}">Auto-renew ready</span>
            </div>
        </div>

        <!-- Bandwidth & FUP Quota Gauge -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-sm backdrop-blur flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Data Quota & Fair Usage</span>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        Full Speed Active
                    </span>
                </div>
                <div class="mt-4 flex items-baseline justify-between">
                    <div class="text-2xl font-bold text-white">
                        {{ $usageGb }} <span class="text-sm font-normal text-slate-400">/ {{ $quotaGb }} GB</span>
                    </div>
                    <span class="text-xs font-mono text-cyan-400">{{ $quotaProgressPct }}% Consumed</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 mt-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-cyan-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $quotaProgressPct }}%"></div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between font-mono">
                <span>Speed: {{ $client->billingPlan?->download_speed_kbps ? round($client->billingPlan->download_speed_kbps / 1024) . ' Mbps' : 'Unlimited' }}</span>
                <span class="text-slate-400">FUP Throttling: 5 Mbps</span>
            </div>
        </div>

        <!-- Digital Voucher Scratch Card Redemption -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-sm backdrop-blur flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Top-Up Voucher</span>
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                </div>
                <p class="text-xs text-slate-400 mt-2">Have an ISP scratch card or digital voucher code? Enter it below for instant activation.</p>

                <form method="POST" action="{{ route('client.redeem-voucher') }}" class="mt-3 flex gap-2">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="text" name="voucher_code" placeholder="e.g. VCH-ABCD-1234" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono text-xs focus:border-cyan-500 focus:outline-none uppercase">
                    <button type="submit" class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold rounded-xl transition shrink-0">
                        Redeem
                    </button>
                </form>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Instant router session unblock</span>
                <span class="text-emerald-400 font-mono">24/7 Automation</span>
            </div>
        </div>

    </div>

    <!-- Active Hardware & Optical Connection Specs -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-sm backdrop-blur">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-white">Provisioned Hardware & TR-069 Optical Telemetry</h3>
                <p class="text-xs text-slate-400">Premises ONT router & GPON fiber link status</p>
            </div>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                Fiber Link Healthy
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
            <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                <div class="text-slate-500 text-[11px]">Optical Rx Power</div>
                <div class="text-emerald-400 font-bold text-sm mt-1">-19.4 dBm</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Optimal range (-8 to -25)</div>
            </div>
            <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                <div class="text-slate-500 text-[11px]">Wi-Fi SSID</div>
                <div class="text-white font-bold text-sm mt-1">{{ $client->first_name }}_Fiber_5G</div>
                <div class="text-[10px] text-cyan-400 mt-0.5">WPA3-SAE Secured</div>
            </div>
            <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                <div class="text-slate-500 text-[11px]">Assigned IPv4</div>
                <div class="text-cyan-400 font-bold text-sm mt-1">{{ $client->ip_address ?? '10.100.4.152' }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">MikroTik Pool</div>
            </div>
            <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl">
                <div class="text-slate-500 text-[11px]">TR-069 ACS Status</div>
                <div class="text-purple-400 font-bold text-sm mt-1">Periodic Inform</div>
                <div class="text-[10px] text-slate-500 mt-0.5">CWMP v1.4 Active</div>
            </div>
        </div>
    </div>

    <!-- Invoices & Billing History -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">Billing Statements & Payment Receipts</h3>
                <p class="text-xs text-slate-400">Official tax invoices and Safaricom M-Pesa confirmation logs</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-mono border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Billing Date</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Balance Due</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($client->invoices as $inv)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 text-white font-medium">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $inv->issue_date ? $inv->issue_date->format('M d, Y') : '-' }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : '-' }}</td>
                        <td class="py-3 px-4 text-white">KES {{ number_format($inv->total_amount, 2) }}</td>
                        <td class="py-3 px-4 {{ $inv->balance_due > 0 ? 'text-rose-400' : 'text-slate-400' }}">KES {{ number_format($inv->balance_due, 2) }}</td>
                        <td class="py-3 px-4">
                            @if($inv->status === 'paid')
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                <span>PAID</span>
                            </span>
                            @elseif($inv->status === 'overdue')
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                <span>OVERDUE</span>
                            </span>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                <span>ISSUED</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button onclick="alert('Downloading PDF Invoice {{ $inv->invoice_number }}...')" class="px-2.5 py-1 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-700 transition">
                                PDF
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">No invoices on record.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Support Tickets & Tech Dispatch -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur">
        <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">My Support Tickets</h3>
                <p class="text-xs text-slate-400">Direct escalation to Network Operations & Field Technicians</p>
            </div>
            <button @click="showTicketModal = true" class="px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-lg text-xs font-medium transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Report Issue</span>
            </button>
        </div>

        <div class="p-4 space-y-3">
            @forelse($tickets as $t)
            <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="font-mono text-xs text-cyan-400 font-semibold">{{ $t->ticket_number }}</span>
                        <span class="text-xs font-medium text-white">{{ $t->subject }}</span>
                    </div>
                    @php
                        $badgeClasses = [
                            'open' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                            'in_progress' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                            'resolved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                        ][$t->status] ?? 'bg-slate-800 text-slate-400 border-slate-700';
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider border {{ $badgeClasses }}">
                        {{ str_replace('_', ' ', $t->status) }}
                    </span>
                </div>

                <div class="flex items-center space-x-4 text-[11px] text-slate-400 font-mono">
                    <span>Category: {{ str_replace('_', ' ', $t->category) }}</span>
                    <span>•</span>
                    <span>Priority: <span class="capitalize {{ $t->priority === 'urgent' ? 'text-rose-400' : 'text-slate-300' }}">{{ $t->priority }}</span></span>
                    <span>•</span>
                    <span>Opened: {{ $t->created_at->diffForHumans() }}</span>
                </div>

                <!-- Latest replies -->
                @if($t->replies && $t->replies->count() > 0)
                <div class="mt-3 pl-3 border-l-2 border-slate-700 space-y-1">
                    @foreach($t->replies as $reply)
                    <div class="text-xs {{ $reply->is_client_reply ? 'text-slate-300' : 'text-cyan-300 bg-cyan-950/20 p-2 rounded-lg border border-cyan-900/50' }}">
                        <span class="font-bold text-[10px] uppercase font-mono block {{ $reply->is_client_reply ? 'text-slate-400' : 'text-cyan-400' }}">
                            {{ $reply->is_client_reply ? 'You' : 'NOC Engineer' }}:
                        </span>
                        {{ $reply->message }}
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="py-8 text-center text-slate-500 text-xs">
                No support tickets found. Your fiber connection is running smoothly.
            </div>
            @endforelse
        </div>
    </div>

    <!-- M-Pesa STK Push Modal -->
    <div x-show="showMpesaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showMpesaModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                        M
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">Safaricom M-Pesa Express</h3>
                        <p class="text-[11px] text-slate-400">Instant STK Push to your mobile handset</p>
                    </div>
                </div>
                <button @click="showMpesaModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('client.pay-mpesa') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">M-Pesa Phone Number</label>
                    <input type="text" name="phone" value="{{ $client->phone }}" required class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white font-mono text-xs focus:border-emerald-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">A PIN prompt will appear on this handset immediately.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Renewal Amount (KES)</label>
                    <input type="number" name="amount" x-model="mpesaAmount" min="1" step="1" required class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white font-mono text-xs focus:border-emerald-500 focus:outline-none font-bold">
                </div>

                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-400 space-y-1">
                    <div class="font-semibold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Automated Instant Reconnection</span>
                    </div>
                    <div class="text-[11px] text-slate-300">
                        Once you enter your M-Pesa PIN, your PPPoE session will be renewed and bandwidth restored within 5 seconds.
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="showMpesaModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center space-x-1.5">
                        <span>Send STK Push Prompt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Trouble Ticket Modal -->
    <div x-show="showTicketModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showTicketModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">Report Connection Issue</h3>
                        <p class="text-[11px] text-slate-400">Open priority ticket with our technical NOC</p>
                    </div>
                </div>
                <button @click="showTicketModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('client.create-ticket') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Issue Subject *</label>
                    <input type="text" name="subject" required placeholder="e.g. Red LOS Light on Router / Slow Wi-Fi" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                            <option value="no_internet">No Internet / Fiber Break</option>
                            <option value="slow_speeds">Slow Speeds / High Latency</option>
                            <option value="billing">Billing & Payment Issue</option>
                            <option value="router_replacement">Router Hardware Problem</option>
                            <option value="relocation">Relocation Request</option>
                            <option value="other">Other Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Urgency Priority *</label>
                        <select name="priority" required class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                            <option value="low">Low (General Inquiry)</option>
                            <option value="medium" selected>Medium (Standard Service)</option>
                            <option value="high">High (Home Office Disrupted)</option>
                            <option value="urgent">Urgent (Complete Outage)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Detailed Description *</label>
                    <textarea name="message" rows="4" required placeholder="Please describe what you are experiencing, what lights are showing on your fiber router, etc." class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showTicketModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-medium text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition">
                        Submit Trouble Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
