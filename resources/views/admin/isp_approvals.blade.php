@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider font-mono">Platform Governance</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 font-mono">Super Admin Only</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">ISP Tenant Onboarding Approvals</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review, verify, and activate pending multi-tenant ISP workspace registrations.</p>
        </div>
    </div>

    <!-- Approvals Table (Matching Base44 ISPApprovals.js) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Tenant Registrations</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $pendingTenants->count() }} Pending Review</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">Tenant Name</th>
                        <th class="p-4">Subdomain</th>
                        <th class="p-4">Contact Info</th>
                        <th class="p-4">Requested Tier</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($pendingTenants as $t)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-slate-900 dark:text-white">{{ $t->name }}</td>
                        <td class="p-4 font-mono text-blue-600 dark:text-blue-400">{{ $t->subdomain }}.smartoh.net</td>
                        <td class="p-4 font-mono text-slate-600 dark:text-slate-300">{{ $t->company_email }} • {{ $t->company_phone }}</td>
                        <td class="p-4 uppercase font-mono text-slate-700 dark:text-slate-300">{{ $t->subscription_tier }}</td>
                        <td class="p-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase font-mono">Pending</span></td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('admin.isp.approve', $t) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    Approve &amp; Activate
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">No pending ISP tenant registrations waiting for approval.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
