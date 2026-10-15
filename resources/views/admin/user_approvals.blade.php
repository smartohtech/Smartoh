@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider font-mono">Platform Governance</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">RBAC Approvals</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Staff &amp; User Role Approvals</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage technician authorizations, billing officer clearances, and ISP administrator delegations.</p>
        </div>
    </div>

    <!-- Users Table (Matching Base44 UserApprovals.js) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Staff Directory &amp; Clearances</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">User Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Assigned Role</th>
                        <th class="p-4">ISP Tenant</th>
                        <th class="p-4">Clearance Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold text-slate-900 dark:text-white">{{ $u->name }}</td>
                        <td class="p-4 font-mono text-slate-600 dark:text-slate-300">{{ $u->email }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-700 dark:text-slate-300">{{ $u->tenant?->name ?? 'Platform Global' }}</td>
                        <td class="p-4">
                            @if($u->is_approved)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase font-mono">Approved</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase font-mono">Pending Clearance</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if(!$u->is_approved)
                            <form method="POST" action="{{ route('admin.user.approve', $u) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    Approve Staff
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs font-mono">Cleared</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
