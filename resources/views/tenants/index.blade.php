@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showNewTenantModal: false, 
    showBrandingModal: false,
    selectedTenant: null,
    brandingForm: {
        id: '',
        name: '',
        brand_color: '#0284c7',
        accent_color: '#0ea5e9',
        theme_mode: 'dark',
        custom_domain: ''
    },
    openBranding(t) {
        this.selectedTenant = t;
        this.brandingForm.id = t.id;
        this.brandingForm.name = t.name;
        this.brandingForm.brand_color = t.brand_color || '#0284c7';
        this.brandingForm.accent_color = t.accent_color || '#0ea5e9';
        this.brandingForm.theme_mode = t.theme_mode || 'dark';
        this.brandingForm.custom_domain = t.custom_domain || '';
        this.showBrandingModal = true;
    }
}">

    <!-- Page Header & Action Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono uppercase tracking-wider bg-purple-500/10 text-purple-400 border border-purple-500/30">
                    Platform Master Console
                </span>
                <span class="text-xs text-slate-500">Decoupled Multi-Tenant Architecture</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight mt-1 flex items-center gap-2">
                <span>Multi-Tenant ISP Workspaces & White-Labeling</span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Provision isolated ISP tenant instances, manage subscription quotas, and customize tenant branding skins.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button @click="showNewTenantModal = true" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-medium text-xs rounded-xl shadow-lg shadow-cyan-500/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Onboard New Tenant ISP</span>
            </button>
        </div>
    </div>

    <!-- Global Platform Metrics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 shadow-sm backdrop-blur relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total ISP Tenants</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-white mt-2">{{ $tenants->count() }}</div>
            <div class="flex items-center space-x-2 mt-1 text-[11px] text-slate-400">
                <span class="text-emerald-400 font-semibold">{{ $tenants->where('status', 'active')->count() }} Active</span>
                <span>•</span>
                <span>{{ $tenants->where('status', '!=', 'active')->count() }} Suspended</span>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 shadow-sm backdrop-blur relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Managed Subscribers</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-white mt-2">{{ $tenants->sum('clients_count') }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                Aggregated active PPPoE/IPoE sessions
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 shadow-sm backdrop-blur relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Active Core Routers</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-white mt-2">{{ $tenants->sum('mikrotik_devices_count') }}</div>
            <div class="text-[11px] text-emerald-400 mt-1 flex items-center space-x-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>RouterOS v7 Telemetry Live</span>
            </div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-4 shadow-sm backdrop-blur relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Wholesale IXP Pools</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-white mt-2">10.0 Gbps</div>
            <div class="text-[11px] text-amber-400 mt-1">Multi-Tenant Upstream Fabric</div>
        </div>
    </div>

    <!-- Tenants Directory Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden backdrop-blur">
        <div class="p-4 border-b border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-white">Registered ISP Workspaces</h3>
                <p class="text-xs text-slate-400">Isolated database scopes with custom DNS subdomains & branding</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="text-slate-400">DNS Root:</span>
                <span class="font-mono text-cyan-400 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">*.smartoh.net</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-mono border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">ISP Tenant</th>
                        <th class="py-3 px-4">Subdomain & Domain</th>
                        <th class="py-3 px-4">Tier & Quota</th>
                        <th class="py-3 px-4">Routers</th>
                        <th class="py-3 px-4">Subscribers</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Theme & Brand</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($tenants as $t)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-md text-xs" style="background-color: {{ $t->brand_color ?? '#0284c7' }};">
                                    {{ substr($t->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-white">{{ $t->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $t->company_email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-mono">
                            <div class="text-cyan-400 font-medium">{{ $t->subdomain }}.smartoh.net</div>
                            @if($t->custom_domain)
                            <div class="text-[10px] text-emerald-400 flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                <span>{{ $t->custom_domain }}</span>
                            </div>
                            @else
                            <div class="text-[10px] text-slate-500">No CNAME custom domain</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $tierClasses = [
                                    'starter' => 'bg-slate-800 text-slate-300 border-slate-700',
                                    'pro' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                                    'enterprise' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                ][$t->subscription_tier] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase tracking-wider border {{ $tierClasses }}">
                                {{ $t->subscription_tier }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono">
                            <span class="text-white font-medium">{{ $t->mikrotik_devices_count }}</span>
                            <span class="text-slate-500">/ {{ $t->max_routers }} max</span>
                        </td>
                        <td class="py-3 px-4 font-mono">
                            <span class="text-white font-medium">{{ $t->clients_count }}</span>
                            <span class="text-slate-500">/ {{ $t->max_clients }} max</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($t->status === 'active')
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Active</span>
                            </span>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] bg-rose-500/10 text-rose-400 border border-rose-500/30 font-medium">
                                <span>Suspended</span>
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-1.5">
                                <span class="w-4 h-4 rounded-full border border-slate-700 shadow" style="background-color: {{ $t->brand_color ?? '#0284c7' }};" title="Primary: {{ $t->brand_color }}"></span>
                                <span class="w-4 h-4 rounded-full border border-slate-700 shadow" style="background-color: {{ $t->accent_color ?? '#0ea5e9' }};" title="Accent: {{ $t->accent_color }}"></span>
                                <span class="text-[10px] text-slate-400 capitalize ml-1">{{ $t->theme_mode ?? 'dark' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button @click="openBranding({{ $t }})" class="px-2.5 py-1 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 transition flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                                    <span>Branding</span>
                                </button>

                                <form method="POST" action="{{ route('switch-tenant') }}">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                                    <button type="submit" class="px-2.5 py-1 text-xs bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-lg transition flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                        <span>Switch In</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            No tenants onboarded yet. Click "Onboard New Tenant ISP" to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Onboard New Tenant ISP Modal -->
    <div x-show="showNewTenantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showNewTenantModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Onboard New ISP Tenant Instance</h3>
                </div>
                <button @click="showNewTenantModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('tenants.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">ISP Business Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Apex HighSpeed Networks" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Subdomain Slug *</label>
                        <div class="flex items-center">
                            <input type="text" name="subdomain" required placeholder="apex" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-l-xl text-white text-xs font-mono focus:border-cyan-500 focus:outline-none">
                            <span class="px-3 py-2 bg-slate-800 border border-l-0 border-slate-700 text-slate-400 text-xs rounded-r-xl font-mono">.smartoh.net</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Custom CNAME Domain (Optional)</label>
                        <input type="text" name="custom_domain" placeholder="e.g. billing.apexnet.co.ke" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Subscription Tier *</label>
                        <select name="subscription_tier" required class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                            <option value="starter">Starter (Up to 5 Routers, 300 Clients)</option>
                            <option value="pro" selected>Pro (Up to 20 Routers, 2,000 Clients)</option>
                            <option value="enterprise">Enterprise (Up to 50 Routers, 10,000 Clients)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Company Contact Email *</label>
                        <input type="email" name="company_email" required placeholder="admin@apexnet.co.ke" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Company Phone / Support *</label>
                        <input type="text" name="company_phone" required placeholder="+254 712 345 678" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Brand Theme Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="brand_color" value="#0284c7" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="brand_color" value="#0284c7" class="w-full px-3 py-1.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Brand Accent Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" name="accent_color" value="#0ea5e9" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" name="accent_color" value="#0ea5e9" class="w-full px-3 py-1.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800">
                    <h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-2 text-cyan-400">Initial ISP Super Administrator</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Admin Full Name *</label>
                            <input type="text" name="admin_name" required placeholder="e.g. Alex Njoroge" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Initial Password *</label>
                            <input type="password" name="admin_password" required minlength="8" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="showNewTenantModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-medium text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition">
                        Provision Workspace
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- White-Label Branding Customizer Modal -->
    <div x-show="showBrandingModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showBrandingModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">White-Label Branding Customizer</h3>
                        <p class="text-[11px] text-slate-400" x-text="'Configuring tenant: ' + brandingForm.name"></p>
                    </div>
                </div>
                <button @click="showBrandingModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form :action="'/tenants/' + brandingForm.id + '/branding'" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Primary Brand Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="brandingForm.brand_color" name="brand_color" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" x-model="brandingForm.brand_color" name="brand_color" class="w-full px-3 py-1.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Accent Highlight Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="brandingForm.accent_color" name="accent_color" class="w-10 h-8 rounded bg-transparent border-0 cursor-pointer">
                            <input type="text" x-model="brandingForm.accent_color" name="accent_color" class="w-full px-3 py-1.5 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Theme Mode</label>
                    <select name="theme_mode" x-model="brandingForm.theme_mode" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs focus:border-cyan-500 focus:outline-none">
                        <option value="dark">Dark Cyberpunk (Recommended)</option>
                        <option value="light">Crisp Light Mode</option>
                        <option value="system">Auto Match Client System</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Custom Domain Name (CNAME)</label>
                    <input type="text" name="custom_domain" x-model="brandingForm.custom_domain" placeholder="portal.your-isp.com" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white text-xs font-mono focus:border-cyan-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">Point your domain CNAME DNS record to <span class="text-cyan-400 font-mono">cname.smartoh.net</span></p>
                </div>

                <!-- Live Preview Badge -->
                <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/60">
                    <div class="text-[11px] font-mono text-slate-400 uppercase tracking-wider mb-2">Live UI Preview</div>
                    <div class="flex items-center justify-between p-3 rounded-xl border" :style="'border-color: ' + brandingForm.accent_color + '; background: linear-gradient(135deg, rgba(15,23,42,0.9), rgba(15,23,42,0.6));'">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-xs shadow-md" :style="'background-color: ' + brandingForm.brand_color">
                                ISP
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white" x-text="brandingForm.name || 'Tenant ISP Name'"></div>
                                <div class="text-[10px] text-slate-400">Subscriber Portal Active</div>
                            </div>
                        </div>
                        <button type="button" class="px-3 py-1 rounded-lg text-xs font-medium text-white shadow-sm" :style="'background-color: ' + brandingForm.accent_color">
                            Active Action
                        </button>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-800">
                    <button type="button" @click="showBrandingModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-medium text-xs rounded-xl shadow-lg shadow-purple-600/20 transition">
                        Save White-Label Brand
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
