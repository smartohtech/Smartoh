@extends('layouts.app')

@section('content')
<div x-data="{
    stkModal: false,
    importModal: false,
    liveStreamConnected: true,
    lastUpdated: '{{ now()->format('H:i:s') }}',
    initLivePoll() {
        setInterval(async () => {
            try {
                const res = await fetch('{{ route('billing.payments.live-feed') }}', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    this.lastUpdated = new Date().toLocaleTimeString();
                    this.liveStreamConnected = true;
                }
            } catch (e) {
                this.liveStreamConnected = false;
            }
        }, 8000);
    }
}" x-init="initLivePoll()" class="space-y-6">

    <!-- Header & Non-Custodial Banner -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono flex items-center space-x-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Real-Time Non-Custodial Pipeline</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                    Direct ISP Merchant Settlement (Zero Smartoh Holding Period)
                </span>
                <span class="text-[11px] font-mono text-slate-400">
                    Live Feed Sync: <strong class="text-white" x-text="lastUpdated"></strong>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Payment Collections, M-Pesa Gateway & Reconciliation</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Subscriber payments settle directly into your authorized M-Pesa Paybill, Till, Pesapal, PayPal, or Bank accounts. Verified callbacks trigger immediate network activation.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('billing.payments.export') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 flex items-center space-x-1.5 transition">
                <span>Export CSV Report</span>
            </a>
            <button @click="importModal = true" class="px-3.5 py-2 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 text-xs font-semibold rounded-xl flex items-center space-x-1.5 transition">
                <span>Import Bank / Pochi Statement</span>
            </button>
            <button @click="stkModal = true" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-600/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                <span>Trigger M-Pesa STK Push</span>
            </button>
        </div>
    </div>

    <!-- 4-Card Financial & Settlement Telemetry (Collected vs Reconciled vs Settled vs Held/Pending) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg">
            <div class="text-[11px] font-mono uppercase text-slate-400">Money Collected (Verified)</div>
            <div class="text-xl font-bold text-white font-mono mt-1">KES {{ number_format($totalVolume, 2) }}</div>
            <div class="text-[11px] text-emerald-400 mt-1">Confirmed by provider events</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg">
            <div class="text-[11px] font-mono uppercase text-slate-400">Money Reconciled to Invoices</div>
            <div class="text-xl font-bold text-cyan-400 font-mono mt-1">KES {{ number_format($reconciledAmount ?? $totalVolume, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Allocated to subscriber bills & wallets</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg">
            <div class="text-[11px] font-mono uppercase text-slate-400">Settled in ISP Merchant Account</div>
            <div class="text-xl font-bold text-emerald-400 font-mono mt-1">KES {{ number_format($settledAmount ?? $totalVolume, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Direct M-Pesa Paybill/Till & Bank</div>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg">
            <div class="text-[11px] font-mono uppercase text-slate-400">Pending Provider Settlement (T+1)</div>
            <div class="text-xl font-bold text-amber-400 font-mono mt-1">KES {{ number_format($pendingSettlementAmount ?? 0, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Callback received; awaiting provider payout</div>
        </div>
    </div>

    <!-- Active Gateways Health Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($gateways as $gw)
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-white">{{ $gw->name }}</div>
                <div class="text-[11px] text-cyan-400 font-mono mt-0.5">
                    {{ strtoupper(str_replace('_', ' ', $gw->provider)) }}
                    @if($gw->shortcode) • Shortcode: {{ $gw->shortcode }} @endif
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                    Settlement: {{ $gw->settlement_mode === 'provider_t_plus_1' ? 'Provider T+1 Schedule' : 'Direct ISP Merchant Account' }}
                </div>
            </div>
            <div class="text-right space-y-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono block">
                    {{ $gw->is_sandbox ? 'SANDBOX ACTIVE' : 'LIVE PRODUCTION' }}
                </span>
                <span class="text-[10px] font-mono text-emerald-400 block">Health: {{ strtoupper($gw->health_status ?? 'HEALTHY') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('billing.payments') }}" class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 flex flex-wrap items-center gap-3 text-xs">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search receipt #, reference, subscriber, phone, account..." class="flex-1 min-w-[220px] px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
        <select name="reconciliation_status" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-300">
            <option value="">All Reconciliation States</option>
            <option value="matched" @selected(request('reconciliation_status') === 'matched')>Matched</option>
            <option value="partially_allocated" @selected(request('reconciliation_status') === 'partially_allocated')>Partially Allocated</option>
            <option value="overpaid_wallet_credited" @selected(request('reconciliation_status') === 'overpaid_wallet_credited')>Overpaid (Wallet Credited)</option>
            <option value="exception" @selected(request('reconciliation_status') === 'exception')>Exception Queue</option>
            <option value="reversed" @selected(request('reconciliation_status') === 'reversed')>Reversed</option>
        </select>
        <select name="activation_status" class="px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-300">
            <option value="">All Activation States</option>
            <option value="connected_verified" @selected(request('activation_status') === 'connected_verified')>Connected (Verified)</option>
            <option value="applied" @selected(request('activation_status') === 'applied')>Activation Applied</option>
            <option value="retrying" @selected(request('activation_status') === 'retrying')>Activation Retrying</option>
            <option value="failed" @selected(request('activation_status') === 'failed')>Activation Failed</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-xl">Filter</button>
        <a href="{{ route('billing.payments') }}" class="px-3 py-2 bg-slate-800 text-slate-400 hover:text-white rounded-xl">Reset</a>
    </form>

    <!-- Payments Ledger -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">Live Subscriber Payment & Activation Ledger</h3>
                <p class="text-[11px] text-slate-400">Separate from Smartoh SaaS platform billing (<a href="{{ route('billing.subscription') }}" class="text-cyan-400 underline">View ISP SaaS Subscription</a>)</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Total Volume: KES {{ number_format($totalVolume, 2) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="p-3.5">Receipt / Ref #</th>
                        <th class="p-3.5">Subscriber & Invoice</th>
                        <th class="p-3.5">Method & Channel</th>
                        <th class="p-3.5">Amount</th>
                        <th class="p-3.5">Reconciliation</th>
                        <th class="p-3.5">Settlement Status</th>
                        <th class="p-3.5">Network Activation</th>
                        <th class="p-3.5">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-3.5">
                            <div class="font-bold text-cyan-400">{{ $p->external_transaction_id ?? $p->transaction_reference }}</div>
                            <div class="text-[10px] text-slate-500">{{ $p->transaction_reference }}</div>
                        </td>
                        <td class="p-3.5 font-sans">
                            <div class="font-medium text-white">{{ $p->client?->name ?? $p->payer_name ?? 'Unmatched Payer' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                Acc: {{ $p->client?->account_number ?? $p->account_reference ?? 'UNMATCHED' }}
                                @if($p->invoice) • Inv: {{ $p->invoice->invoice_number }} @endif
                            </div>
                        </td>
                        <td class="p-3.5">
                            <div class="uppercase text-slate-200">{{ str_replace('_', ' ', $p->payment_method) }}</div>
                            <div class="text-[10px] text-slate-500">{{ str_replace('_', ' ', $p->source_channel ?? 'api_webhook') }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-emerald-400">+KES {{ number_format($p->amount, 2) }}</td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->reconciliation_status === 'exception' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' }}">
                                {{ str_replace('_', ' ', $p->reconciliation_status ?? 'matched') }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            @if(in_array($p->settlement_status, ['direct_to_isp_merchant', 'settled_in_isp_account']))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">DIRECT ISP SETTLED</span>
                            @elseif($p->settlement_status === 'pending_provider_settlement')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">PROVIDER T+1 PENDING</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">{{ strtoupper(str_replace('_', ' ', $p->settlement_status ?? 'pending')) }}</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <div class="text-[11px] font-sans font-semibold {{ $p->activation_status === 'connected_verified' ? 'text-emerald-400' : ($p->activation_status === 'retrying' ? 'text-amber-400' : 'text-slate-300') }}">
                                {{ $p->human_lifecycle_status }}
                            </div>
                            @if($p->status === 'completed')
                                <span class="text-[9px] text-emerald-500 uppercase">COMPLETED</span>
                            @else
                                <span class="text-[9px] text-amber-400 uppercase">{{ strtoupper($p->status) }}</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-slate-400 text-[11px]">{{ ($p->paid_at ?? $p->created_at)->format('d M Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="p-8 text-center text-slate-500 font-sans">No payment records logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- Exception Queue & Activation Retry Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Reconciliation Exceptions Queue -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Reconciliation Exception Queue</h3>
                    <p class="text-xs text-slate-400">Wrong account references, unmatched deposits, and unverified manual claims</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono bg-rose-500/10 text-rose-400 border border-rose-500/30">
                    {{ isset($openExceptions) ? $openExceptions->count() : 0 }} Open
                </span>
            </div>
            <div class="space-y-3">
                @forelse($openExceptions ?? [] as $exc)
                <div class="p-3.5 bg-slate-950/70 border border-slate-800 rounded-xl space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-amber-400">Ref: {{ $exc->external_transaction_id }} (Submitted: {{ $exc->submitted_reference }})</span>
                        <span class="font-mono font-bold text-emerald-400">KES {{ number_format($exc->amount, 2) }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        Reason: <strong class="text-rose-400 uppercase">{{ str_replace('_', ' ', $exc->exception_reason) }}</strong> • Payer: {{ $exc->payer_name ?? $exc->phone_number }}
                    </div>
                    <form method="POST" action="{{ route('billing.payments.exceptions.resolve', $exc) }}" class="flex items-center gap-2 pt-1">
                        @csrf
                        <select name="client_id" required class="flex-1 px-2.5 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs">
                            <option value="">Assign to Subscriber...</option>
                            @foreach(App\Models\Client::all() as $c)
                            <option value="{{ $c->id }}">{{ $c->account_number }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-xs">
                            Verify & Activate
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-slate-500 py-4 text-center">No open reconciliation exceptions. All verified payments are matched.</div>
                @endforelse
            </div>
        </div>

        <!-- Network Activation Retry Queue -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Network Activation Retry Queue</h3>
                    <p class="text-xs text-slate-400">Exponential backoff retries when a router or RADIUS NAS is temporarily unreachable</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono bg-amber-500/10 text-amber-400 border border-amber-500/30">
                    {{ isset($retryingJobs) ? $retryingJobs->count() : 0 }} Queued
                </span>
            </div>
            <div class="space-y-3">
                @forelse($retryingJobs ?? [] as $job)
                <div class="p-3.5 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white">{{ $job->client?->name }} ({{ $job->client?->account_number }})</div>
                        <div class="text-[11px] text-amber-400 font-mono">
                            Status: {{ strtoupper($job->status) }} • Attempts: {{ $job->attempts }}/{{ $job->max_attempts }}
                        </div>
                        @if($job->last_error)
                        <div class="text-[10px] text-rose-400 mt-0.5">{{ $job->last_error }}</div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('billing.payments.activations.retry', $job) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold rounded-lg text-xs">
                            Retry Now
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-slate-500 py-4 text-center">All verified payments have been activated on the network.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Trigger M-Pesa STK Modal -->
    <div x-show="stkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="stkModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">M</div>
                    <h3 class="text-base font-bold text-white">Trigger M-Pesa STK Push</h3>
                </div>
                <button @click="stkModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.payments.stk-push') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Select Subscriber *</label>
                    <select name="client_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach(App\Models\Client::all() as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }}) - {{ $client->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Amount to Bill (KES) *</label>
                    <input type="number" step="0.01" name="amount" value="4000.00" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center space-x-2">
                    <input type="checkbox" name="simulate_instant_payment" value="1" checked id="sim_check" class="rounded bg-slate-800 border-slate-700 text-emerald-500">
                    <label for="sim_check" class="text-slate-300 cursor-pointer">
                        Simulate sandbox callback confirmation & immediate network activation
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="stkModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">
                        Dispatch STK Prompt
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Statement Modal (CSV/XLSX for non-API Bank & Pochi accounts) -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="importModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Controlled Statement CSV Import (Bank / Pochi)</h3>
                <button @click="importModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <form method="POST" action="{{ route('billing.payments.import-statement') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Account / Provider Type *</label>
                    <select name="provider" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="bank_manual">Direct Bank Statement (Equity / KCB / Co-op)</option>
                        <option value="pochi_statement">Verified M-Pesa / Pochi la Biashara Statement</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Paste CSV Rows (Receipt, AccountRef, Amount, PayerName)</label>
                    <textarea name="csv_content" rows="4" placeholder="BNK99102,CLI-1001,2500,John Mwangi" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono"></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="importModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl">
                        Import & Reconcile
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
