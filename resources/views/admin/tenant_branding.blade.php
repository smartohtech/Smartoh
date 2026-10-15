@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    brandColor: '{{ $tenant->brand_color ?? '#1565C0' }}',
    accentColor: '{{ $tenant->accent_color ?? '#0ea5e9' }}',
    customDomain: '{{ $tenant->custom_domain ?? '' }}',
    themeMode: '{{ $tenant->theme_mode ?? 'dark' }}'
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">White-Label Engine</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">Custom Branding</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Organization &amp; Tenant Branding</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Customize your subscriber portal URL, custom CNAME domain, brand colors, and theme aesthetics.</p>
        </div>
    </div>

    <!-- Branding Form & Preview Grid (Matching Base44 TenantBrandingSettings.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Form -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Branding Parameters</h3>

            <form method="POST" action="{{ route('tenants.branding', $tenant ?? 1) }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Primary Brand Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="brand_color" x-model="brandColor" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="brand_color" x-model="brandColor" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Accent Highlight Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="accent_color" x-model="accentColor" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="accent_color" x-model="accentColor" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Theme Mode</label>
                    <select name="theme_mode" x-model="themeMode" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                        <option value="dark">Dark Theme (NetFlow Cyberpunk)</option>
                        <option value="light">Crisp Light Mode</option>
                        <option value="system">Auto Match System</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Custom Domain Name (CNAME)</label>
                    <input type="text" name="custom_domain" x-model="customDomain" placeholder="portal.my-isp.co.ke" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    <p class="text-[11px] text-slate-400 mt-1">Point your domain CNAME DNS record to <span class="text-blue-600 dark:text-blue-400 font-mono">cname.smartoh.net</span></p>
                </div>

                <div class="pt-3">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-md shadow-blue-600/20">
                        Save Organization Skin
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Live Client Portal Preview</h3>

            <div class="p-5 rounded-2xl border" :style="'border-color: ' + accentColor + '; background: ' + (themeMode === 'light' ? '#f8fafc' : '#020617')">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-xs shadow-md" :style="'background-color: ' + brandColor">
                            ISP
                        </div>
                        <span class="font-bold text-sm" :class="themeMode === 'light' ? 'text-slate-900' : 'text-white'">{{ $tenant->name ?? 'Your ISP Brand' }}</span>
                    </div>
                    <span class="text-xs font-mono font-bold" :style="'color: ' + accentColor">Subscriber Portal</span>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                    <div class="text-xs font-semibold" :class="themeMode === 'light' ? 'text-slate-800' : 'text-slate-200'">Active Service: Home Fiber 20M</div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="h-2 rounded-full" :style="'width: 65%; background-color: ' + brandColor"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-400">
                        <span>325 GB / 500 GB</span>
                        <span class="font-bold" :style="'color: ' + accentColor">Full Speed</span>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="button" class="w-full py-2 rounded-xl text-white font-semibold text-xs shadow-md" :style="'background-color: ' + brandColor">
                        Renew via M-Pesa Express
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
