@extends('layouts.platform')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div x-data="{ payModal: false }" class="space-y-6 max-w-4xl mx-auto">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('platform.invoices.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Back to Invoices</a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight mt-1">Invoice {{ $invoice->invoice_number }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Billed to <strong class="text-white">{{ $invoice->tenant->name ?? 'ISP' }}</strong> on {{ $invoice->created_at->format('M d, Y') }}</p>
        </div>

        <div class="flex items-center space-x-2.5">
            @if(!$invoice->isPaid())
            <button @click="payModal = true" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Record &amp; Verify Payment</span>
            </button>
            @else
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Payment Confirmed (Paid)</span>
            </span>
            @endif
        </div>
    </div>

    <!-- Printable Invoice Sheet Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
        
        <!-- Sheet Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-800 pb-8">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/smartoh_logo.jpeg') }}" alt="Smartoh Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-slate-700">
                    <span class="text-base font-extrabold text-white">Smartoh Technologies</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed font-mono">
                    Autonomous ISP Billing &amp; SaaS Core<br>
                    Nairobi, Kenya • billing@smartoh.co.ke
                </p>
            </div>

            <div class="sm:text-right space-y-1 font-mono text-xs">
                <div class="text-xl font-extrabold text-white">{{ $invoice->invoice_number }}</div>
                <div class="text-slate-400">Issue Date: <span class="text-white">{{ $invoice->created_at->format('M d, Y') }}</span></div>
                <div class="text-slate-400">Due Date: <span class="text-amber-400 font-bold">{{ $invoice->due_date->format('M d, Y') }}</span></div>
                @if($invoice->paid_at)
                <div class="text-emerald-400 font-bold">Paid At: {{ $invoice->paid_at->format('M d, Y H:i') }}</div>
                @endif
            </div>
        </div>

        <!-- Bill To -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Billed Client (ISP):</span>
                <div class="font-bold text-white text-sm mt-1">{{ $invoice->tenant->name ?? 'ISP Organization' }}</div>
                <div class="text-slate-400 font-mono mt-0.5">{{ $invoice->tenant->company_email ?? 'N/A' }}</div>
                <div class="text-slate-400 font-mono">{{ $invoice->tenant->subdomain ?? 'isp' }}.smartoh.co.ke</div>
            </div>

            <div>
                <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Billing Coverage Period:</span>
                <div class="font-mono text-slate-200 mt-1">
                    {{ $invoice->billing_period_start->format('M d, Y') }} to {{ $invoice->billing_period_end->format('M d, Y') }}
                </div>
                <div class="text-slate-500 font-mono text-[11px] mt-0.5">
                    Tier: {{ $invoice->subscription->plan->name ?? 'Standard Platform' }}
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="border border-slate-800 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 font-mono uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Item &amp; Description</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Unit Price</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-mono">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3.5 px-4 font-bold text-white">{{ $item->description }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-300">{{ $item->quantity }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-300">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-white">{{ $invoice->currency }} {{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Calculation -->
        <div class="flex justify-end text-xs font-mono">
            <div class="w-full sm:w-64 space-y-2 border-t border-slate-800 pt-3">
                <div class="flex items-center justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>Applicable Tax:</span>
                    <span>{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-base font-bold text-white pt-2 border-t border-slate-800">
                    <span>Total Due:</span>
                    <span class="text-emerald-400">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payments Logged on this Invoice -->
        @if($invoice->payments->isNotEmpty())
        <div class="border-t border-slate-800 pt-6 space-y-3 font-mono text-xs">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Payments Logged on this Invoice:</div>
            @foreach($invoice->payments as $pay)
            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="font-bold text-white">Ref: {{ $pay->payment_reference }}</span>
                    <span class="text-slate-400 text-[10px] block">Provider: {{ strtoupper($pay->provider) }} • Paid at: {{ $pay->paid_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="font-bold text-emerald-400">
                    {{ $pay->currency }} {{ number_format($pay->amount, 2) }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- RECORD PAYMENT MODAL -->
    <div x-show="payModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="payModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Record Verified Payment</h3>
                <button @click="payModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-300">
                Recording this payment will mark the invoice as <strong>PAID</strong> and automatically upgrade the ISP tenant's subscription to <strong>ACTIVE_PAID</strong>.
            </p>

            <form method="POST" action="{{ route('platform.invoices.record-payment', $invoice) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Amount Paid ({{ $invoice->currency }}) *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $invoice->total }}" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Payment Reference / Transaction ID *</label>
                    <input type="text" name="payment_reference" placeholder="e.g. QKD1837492 or BANK-TRF-09" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Payment Gateway / Channel *</label>
                    <select name="provider" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="mpesa">M-Pesa Paybill (Daraja)</option>
                        <option value="bank_transfer">Bank Wire Transfer</option>
                        <option value="stripe">Stripe / Card</option>
                        <option value="manual">Manual / Check</option>
                        <option value="cash">Cash Settlement</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Reconciliation Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes for audit logs..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="payModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">
                        Confirm &amp; Activate Paid
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
