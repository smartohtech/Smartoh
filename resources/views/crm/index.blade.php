@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addLeadModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">CRM Pipeline</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">Sales Pipeline</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Subscriber Acquisition &amp; CRM Leads</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage prospect inquiries, building feasibility site surveys, quotations, and subscriber conversions.</p>
        </div>

        <button @click="addLeadModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Lead</span>
        </button>
    </div>

    <!-- Leads Table (Matching Base44 CRM.js) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Active Sales Pipeline</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $leads->count() }} Prospective Opportunities</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-mono tracking-wider">
                        <th class="p-4">Lead Name &amp; Company</th>
                        <th class="p-4">Contact Details</th>
                        <th class="p-4">Lead Source</th>
                        <th class="p-4">Pipeline Stage</th>
                        <th class="p-4">Estimated Value</th>
                        <th class="p-4">Sales Rep</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <div class="font-semibold text-slate-900 dark:text-white">{{ $lead->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $lead->company ?? 'Residential Customer' }}</div>
                        </td>
                        <td class="p-4 font-mono">
                            <div class="text-slate-800 dark:text-slate-200">{{ $lead->phone }}</div>
                            <div class="text-[11px] text-slate-400">{{ $lead->email }}</div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $lead->source }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                {{ $lead->stage }}
                            </span>
                        </td>
                        <td class="p-4 font-bold font-mono text-emerald-600 dark:text-emerald-400">
                            KES {{ number_format($lead->value) }}/mo
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-300">
                            {{ $lead->assigned_to }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('clients.index') }}" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                Convert to Client
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">No leads currently in pipeline. Click "Add Lead" to register inquiries.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Lead Modal -->
    <div x-show="addLeadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addLeadModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Add New CRM Prospect</h3>
            <form method="POST" action="{{ route('crm.lead.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Contact Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Company / Building</label>
                        <input type="text" name="company" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                        <input type="text" name="phone" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Source *</label>
                        <select name="source" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="Walk-in">Walk-in</option>
                            <option value="Call">Phone Call</option>
                            <option value="Referral">Referral</option>
                            <option value="Website">Website</option>
                            <option value="Social">Social Media</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Stage *</label>
                        <select name="stage" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="New">New</option>
                            <option value="Contacted">Contacted</option>
                            <option value="Site Survey">Site Survey</option>
                            <option value="Proposal">Proposal</option>
                            <option value="Won">Won</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Est. Value (KES) *</label>
                        <input type="number" name="value" value="3500" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Notes &amp; Requirements</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="addLeadModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl">Save Lead</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
