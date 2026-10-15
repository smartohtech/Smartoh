@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Payment Gateways</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Daraja STK Ready</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">API Payment Gateways</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated STK push collections, webhook callback listeners, and encrypted merchant credentials.</p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="addModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Add API Gateway</span>
            </button>
        </div>
    </div>

    <!-- Gateway Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($gateways as $gw)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white text-xs {{ str_contains(strtolower($gw->provider), 'mpesa') ? 'bg-emerald-600' : 'bg-blue-600' }}">
                            {{ substr($gw->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $gw->name }}</h3>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $gw->provider }}</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $gw->environment === 'production' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800' }}">
                        {{ $gw->environment }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-xs font-mono">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Shortcode / Till:</span>
                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $gw->shortcode }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Endpoint:</span>
                        <span class="text-blue-600 dark:text-blue-400 truncate max-w-[180px]">{{ $gw->base_url }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Last Verified:</span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ $gw->last_tested }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Status: <strong class="text-emerald-500 uppercase">{{ $gw->status }}</strong></span>
                <form method="POST" action="{{ route('gateways.test') }}">
                    @csrf
                    <input type="hidden" name="name" value="{{ $gw->name }}">
                    <button type="submit" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium transition">
                        Test Connection
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Add API Payment Gateway</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('gateways.api.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Gateway Name *</label>
                    <input type="text" name="name" required placeholder="e.g. M-Pesa Daraja STK Push" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Provider *</label>
                        <select name="provider" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="mpesa_daraja">Safaricom Daraja STK Push</option>
                            <option value="mpesa_c2b">Safaricom C2B Paybill</option>
                            <option value="stripe">Stripe Payments</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Environment *</label>
                        <select name="environment" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="sandbox">Sandbox / Demo</option>
                            <option value="production">Production Live</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Paybill / Till Shortcode *</label>
                    <input type="text" name="shortcode" required placeholder="e.g. 174379 or 522522" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Consumer Key *</label>
                    <input type="text" name="consumer_key" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Consumer Secret *</label>
                    <input type="password" name="consumer_secret" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Enroll Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
