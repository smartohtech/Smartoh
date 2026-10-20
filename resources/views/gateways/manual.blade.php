@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addManualModal: false, addApiModal: false, selectedManualProvider: 'bank_manual', selectedApiProvider: 'mpesa_daraja' }">

    <!-- Header & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Non-Custodial Payment Gateways</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Direct ISP Accounts</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Non-API &amp; Manual Payment Accounts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add your desired Bank Accounts, Pochi la Biashara, Non-API Paybill/Till, SACCO, or Cash Desk collection accounts.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Switch to API Gateways View -->
            <a href="{{ route('gateways.api') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition flex items-center space-x-1.5">
                <span>API Gateways ({{ $apiCount ?? 0 }})</span>
            </a>

            <!-- Add API Gateway Account Button -->
            <button type="button" @click="addApiModal = true" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add API Gateway Account</span>
            </button>

            <!-- Add Non-API Payment Account Button -->
            <button type="button" @click="addManualModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add Non-API Payment Account</span>
            </button>
        </div>
    </div>

    <!-- Gateway Mode Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 dark:border-slate-800 pb-2 text-xs font-semibold">
        <a href="{{ route('gateways.api') }}" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            API Payment Gateways ({{ $apiCount ?? 0 }})
        </a>
        <a href="{{ route('gateways.manual') }}" class="px-4 py-2 rounded-xl bg-emerald-600 text-white shadow-sm">
            Non-API / Bank / Pochi / Manual Accounts ({{ count($manualGateways) }})
        </a>
    </div>

    <!-- Manual / Non-API Gateway Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($manualGateways as $gw)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white text-xs">
                            {{ strtoupper(substr($gw->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $gw->name }}</h3>
                            <span class="text-[10px] text-slate-400 font-mono uppercase">{{ $gw->provider_label ?? 'DIRECT ACCOUNT' }}</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $gw->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800' }}">
                        {{ $gw->status }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-xs font-mono">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Account / Till / Phone:</span>
                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $gw->account_number }}</span>
                    </div>
                    @if(!empty($gw->account_holder_name))
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Beneficiary:</span>
                        <span class="text-slate-700 dark:text-slate-300 font-sans truncate max-w-[180px]">{{ $gw->account_holder_name }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Branch / Paybill:</span>
                        <span class="text-slate-700 dark:text-slate-300 font-sans truncate max-w-[180px]">{{ $gw->branch }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Reconciliation:</span>
                        <span class="text-blue-600 dark:text-blue-400 font-sans">{{ $gw->reconciliation_method ?? 'CSV Statement / Manual' }}</span>
                    </div>
                    <div class="pt-2 text-slate-500 dark:text-slate-400 font-sans text-[11px]">
                        {{ $gw->instructions }}
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <a href="{{ route('billing.payments') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    Import Statement / Reconcile &rarr;
                </a>

                @if(!empty($gw->is_persisted) && !empty($gw->id))
                <div class="flex items-center space-x-1.5">
                    <form method="POST" action="{{ route('gateways.toggle', $gw->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-medium transition {{ $gw->is_active ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400' }}">
                            {{ $gw->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('gateways.destroy', $gw->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition" title="Delete Account">
                            &times;
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: ADD DESIRED NON-API / MANUAL PAYMENT ACCOUNT                     -->
    <!-- ========================================================================= -->
    <div x-show="addManualModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div @click.away="addManualModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Add Desired Non-API Payment Account</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Add a Bank Account, Pochi la Biashara, Non-API Paybill/Till, SACCO, or Cash Desk account.</p>
                </div>
                <button type="button" @click="addManualModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('gateways.manual.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account Display Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Equity Bank Corporate / Pochi la Biashara" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account Type / Channel *</label>
                        <select name="provider" x-model="selectedManualProvider" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="bank_manual">Direct Bank Account (Equity, KCB, Co-op, NCBA, etc.)</option>
                            <option value="pochi_la_biashara">M-Pesa Pochi la Biashara (Phone Number)</option>
                            <option value="mpesa_paybill_manual">M-Pesa Paybill (Non-API / Statement Sync)</option>
                            <option value="mpesa_till_manual">M-Pesa Buy Goods Till (Non-API)</option>
                            <option value="airtel_money_manual">Airtel Money Paybill / Till (Non-API)</option>
                            <option value="tkash_manual">Telkom T-Kash Account</option>
                            <option value="sacco_account">SACCO / Microfinance Account</option>
                            <option value="cash_desk">Walk-in Office Cash Desk / Field Agent</option>
                            <option value="custom_manual">Other / Custom Non-API Payment Account...</option>
                        </select>
                    </div>
                </div>

                <div x-show="selectedManualProvider === 'custom_manual'" class="p-3 bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl">
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Custom Payment Channel / Institution Name *</label>
                    <input type="text" name="custom_provider_name" placeholder="e.g. Unaitas SACCO / Family Bank Paybill / WorldRemit" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account / Phone / Till / Paybill No. *</label>
                        <input type="text" name="account_number" required placeholder="e.g. 01802938472, 0712345678, or Till No." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account Holder / Beneficiary Name</label>
                        <input type="text" name="account_holder_name" placeholder="e.g. Alpha Net Fiber Ltd" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Bank Paybill (If Bank)</label>
                        <input type="text" name="bank_paybill" placeholder="e.g. 247247 or 522522" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Branch / Network Label</label>
                        <input type="text" name="branch" placeholder="e.g. Westlands Branch" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Reconciliation Mode</label>
                        <select name="reconciliation_mode" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="statement_import">CSV / Statement Import</option>
                            <option value="manual">Manual Staff Verification</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Customer Payment Instructions (Shown on Invoices &amp; Portal)</label>
                    <textarea name="instructions" rows="2" placeholder="e.g. Pay via M-Pesa Paybill 247247, Account: 018029#YourAccountNo, then submit reference or await CSV statement sync." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addManualModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-md shadow-emerald-600/20">
                        Save Non-API Payment Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: ADD DESIRED API PAYMENT GATEWAY ACCOUNT                          -->
    <!-- ========================================================================= -->
    <div x-show="addApiModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto">
        <div @click.away="addApiModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Add Desired API Payment Account</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Connect your own M-Pesa Paybill/Till, Bank API, Pesapal, PayPal, or any Custom API Gateway.</p>
                </div>
                <button type="button" @click="addApiModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">&times;</button>
            </div>

            <form method="POST" action="{{ route('gateways.api.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account Display Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Alpha Net M-Pesa Paybill 400200" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Account Holder / Business Name</label>
                        <input type="text" name="account_holder_name" placeholder="e.g. Alpha Net Fiber Ltd" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">API Provider / Platform *</label>
                        <select name="provider" x-model="selectedApiProvider" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="mpesa_daraja">Safaricom Daraja STK Push &amp; Paybill</option>
                            <option value="mpesa_c2b">Safaricom C2B Paybill / Buy Goods Till</option>
                            <option value="pesapal">Pesapal API v3.0 Merchant</option>
                            <option value="paypal">PayPal Commerce v2 REST API</option>
                            <option value="equity_jenga">Equity Bank Jenga API</option>
                            <option value="kcb_buni">KCB Buni Bank API</option>
                            <option value="coop_connect">Co-operative Bank Connect API</option>
                            <option value="airtel_money_api">Airtel Money Merchant API</option>
                            <option value="equitel_finserve">Equitel / Finserve API</option>
                            <option value="flutterwave">Flutterwave Merchant API</option>
                            <option value="paystack">Paystack Merchant API</option>
                            <option value="stripe">Stripe Payments API</option>
                            <option value="custom_api">Other / Custom API Payment Provider...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Environment *</label>
                        <select name="environment" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="production">Production Live</option>
                            <option value="sandbox">Sandbox / Testing</option>
                        </select>
                    </div>
                </div>

                <div x-show="selectedApiProvider === 'custom_api'" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 p-3 bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 rounded-xl">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Custom API Provider Name *</label>
                        <input type="text" name="custom_provider_name" placeholder="e.g. NCBA Loop API / Family Bank API" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">API Endpoint Base URL</label>
                        <input type="text" name="base_url" placeholder="https://api.yourbank.co.ke/v1" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Paybill / Till / Merchant Account No. *</label>
                        <input type="text" name="shortcode" required placeholder="e.g. 400200, 522522, or Merchant ID" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Passkey / Lipa Na M-Pesa Passkey (Optional)</label>
                        <input type="password" name="passkey" placeholder="Daraja STK Passkey or API Token" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Consumer Key / Client ID / API Key *</label>
                        <input type="text" name="consumer_key" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Consumer Secret / Client Secret *</label>
                        <input type="password" name="consumer_secret" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Webhook Signing Secret (Optional)</label>
                    <input type="text" name="webhook_secret" placeholder="HMAC Webhook Secret for callback verification" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addApiModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Save API Payment Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
