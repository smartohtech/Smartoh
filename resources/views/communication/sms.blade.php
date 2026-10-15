@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Communication Gateways</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">SMS Alphanumeric Active</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">SMS Gateway Providers</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Africa's Talking, Twilio, and Advanta SMS aggregators for automated OTPs and payment confirmations.</p>
        </div>
    </div>

    <!-- Gateways Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($gateways as $gw)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $gw->name }}</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        {{ $gw->environment }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-xs font-mono">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Sender ID:</span>
                        <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $gw->sender_id }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Username:</span>
                        <span class="text-slate-800 dark:text-slate-200">{{ $gw->username }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Account Balance:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $gw->balance }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">Last Verified: {{ $gw->last_tested }}</span>
                <form method="POST" action="{{ route('sms.test') }}">
                    @csrf
                    <input type="hidden" name="test_phone" value="+254712345678">
                    <button type="submit" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium transition">
                        Test SMS
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
