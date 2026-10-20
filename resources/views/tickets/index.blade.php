@extends('layouts.app')

@section('content')
<div x-data="{ createModal: false, selectedCategory: '{{ request('category', 'all') }}', selectedPriority: '{{ request('priority', 'all') }}', selectedStatus: '{{ request('status', 'all') }}' }" class="space-y-6">

    <!-- Header & Action -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Customer Support Tickets &amp; Helpdesk</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/40 uppercase tracking-wider font-mono">
                    SLA Operations
                </span>
            </div>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-1">Subscriber troubleshooting, technical support escalations, technician dispatch, and SLA commitments.</p>
        </div>
        <button @click="createModal = true" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Create Support Ticket</span>
        </button>
    </div>

    <!-- Helpdesk Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Open Tickets</div>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1">{{ $counts['open'] }}</div>
                <div class="text-[10px] font-medium text-slate-600 dark:text-slate-300 mt-0.5">Awaiting first response</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">In Progress</div>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1">{{ $counts['in_progress'] }}</div>
                <div class="text-[10px] font-medium text-slate-600 dark:text-slate-300 mt-0.5">Assigned to team</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">SLA Breach / Urgent</div>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 font-mono mt-1">{{ $counts['urgent'] + $counts['sla_breached'] }}</div>
                <div class="text-[10px] font-medium text-slate-600 dark:text-slate-300 mt-0.5">{{ $counts['sla_breached'] }} breached deadline</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30 flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Resolved &amp; Closed</div>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1">{{ $counts['resolved'] }}</div>
                <div class="text-[10px] font-medium text-slate-600 dark:text-slate-300 mt-0.5">Total resolved issues</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </span>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1 overflow-x-auto pb-1 md:pb-0 text-xs font-bold">
            <a href="{{ route('tickets.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" class="px-3 py-1.5 rounded-xl transition {{ (!request()->has('status') || request('status') === 'all') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                All ({{ $counts['total'] }})
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('status', 'page'), ['status' => 'open'])) }}" class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'open' ? 'bg-amber-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Open ({{ $counts['open'] }})
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('status', 'page'), ['status' => 'in_progress'])) }}" class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'in_progress' ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                In Progress ({{ $counts['in_progress'] }})
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('status', 'page'), ['status' => 'waiting_client'])) }}" class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'waiting_client' ? 'bg-purple-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Waiting Client ({{ $counts['waiting_client'] }})
            </a>
            <a href="{{ route('tickets.index', array_merge(request()->except('status', 'page'), ['status' => 'resolved'])) }}" class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Resolved ({{ $counts['resolved'] }})
            </a>
        </div>

        <!-- Search & Filter Controls -->
        <form method="GET" action="{{ route('tickets.index') }}" class="flex items-center space-x-2 flex-1 max-w-lg">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <select name="priority" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:border-blue-500">
                <option value="all">Priority: All</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>

            <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:border-blue-500">
                <option value="all">Category: All</option>
                <option value="no_internet" {{ request('category') === 'no_internet' ? 'selected' : '' }}>No Internet</option>
                <option value="slow_speeds" {{ request('category') === 'slow_speeds' ? 'selected' : '' }}>Slow Speeds</option>
                <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                <option value="relocation" {{ request('category') === 'relocation' ? 'selected' : '' }}>Relocation</option>
                <option value="router_replacement" {{ request('category') === 'router_replacement' ? 'selected' : '' }}>Hardware / Router</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
            </select>

            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ticket # or subscriber..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            <button type="submit" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition">Filter</button>
        </form>

    </div>

    <!-- Tickets Table / List -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Tickets Queue</h3>
            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 font-mono">{{ $tickets->total() }} Tickets Found</span>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-slate-800/60">
            @forelse($tickets as $t)
            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                
                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex items-center space-x-2.5 flex-wrap gap-y-1">
                        <!-- Priority Badge -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $t->priority === 'urgent' ? 'bg-rose-50 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/40' : ($t->priority === 'high' ? 'bg-amber-50 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/40' : ($t->priority === 'medium' ? 'bg-blue-50 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300')) }}">
                            {{ $t->priority }}
                        </span>

                        <!-- Ticket Number & Subject -->
                        <a href="{{ route('tickets.show', $t) }}" class="font-bold text-slate-900 dark:text-white font-mono text-sm hover:text-blue-600 dark:hover:text-blue-400 transition truncate">
                            {{ $t->ticket_number }}: {{ $t->subject }}
                        </a>

                        <!-- Status Badge -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $t->status === 'open' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' : ($t->status === 'in_progress' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30' : ($t->status === 'waiting_client' ? 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-500/30' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30')) }}">
                            {{ str_replace('_', ' ', $t->status) }}
                        </span>

                        <!-- Category Tag -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                            {{ str_replace('_', ' ', $t->category) }}
                        </span>
                    </div>

                    <!-- Meta details -->
                    <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300 flex flex-wrap items-center gap-3 pt-1">
                        <span>Subscriber: <strong class="text-slate-900 dark:text-white">{{ $t->client?->name ?? 'Unknown Client' }}</strong> ({{ $t->client?->account_number }})</span>
                        <span>•</span>
                        <span>Phone: <span class="text-slate-800 dark:text-slate-200 font-mono font-semibold">{{ $t->client?->phone }}</span></span>
                        <span>•</span>
                        <span>Assignee: <strong class="text-cyan-700 dark:text-cyan-400">{{ $t->assignedUser?->name ?? 'Unassigned' }}</strong></span>
                        <span>•</span>
                        <span>Replies: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $t->replies->count() }}</strong></span>
                        <span>•</span>
                        <span>Created: {{ $t->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <!-- SLA & Actions Column -->
                <div class="flex items-center space-x-3 shrink-0 self-start md:self-auto">
                    @if($t->sla_due_at)
                    <div class="text-right font-mono text-[11px]">
                        @if($t->status !== 'resolved' && $t->status !== 'closed' && $t->sla_due_at->isPast())
                        <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center space-x-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                            <span>SLA Breached ({{ $t->sla_due_at->diffForHumans() }})</span>
                        </span>
                        @else
                        <span class="text-slate-600 dark:text-slate-300 font-semibold">
                            Due: {{ $t->sla_due_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                    @endif

                    <a href="{{ route('tickets.show', $t) }}" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-600/20 whitespace-nowrap">
                        View Thread &rarr;
                    </a>
                </div>

            </div>
            @empty
            <div class="text-center py-10 text-slate-600 dark:text-slate-400 text-xs font-medium space-y-2">
                <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <div>No support tickets matching current filter criteria. All subscriber queries resolved!</div>
            </div>
            @endforelse
        </div>

        @if($tickets->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>

    <!-- Create Support Ticket Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="createModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                    </span>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Create Support Ticket</h3>
                </div>
                <button @click="createModal = false" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Select Subscriber / Client *</label>
                    <select name="client_id" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium">
                        <option value="">-- Choose Subscriber --</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->account_number }} - {{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Subject Headline *</label>
                    <input type="text" name="subject" placeholder="e.g. Fiber link LOS red light on ONT" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Issue Category *</label>
                        <select name="category" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium">
                            <option value="no_internet">No Internet (Circuit Outage)</option>
                            <option value="slow_speeds">Slow Speeds / High Latency</option>
                            <option value="billing">Billing &amp; Payment Inquiry</option>
                            <option value="relocation">Relocation / Re-installation</option>
                            <option value="router_replacement">Hardware / Router Fault</option>
                            <option value="other">General Inquiries</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Priority Level *</label>
                        <select name="priority" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium">
                            <option value="medium">Medium (Standard)</option>
                            <option value="high">High (Business Client)</option>
                            <option value="urgent">Urgent (Total Outage)</option>
                            <option value="low">Low (Cosmetic/Query)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Assign Staff / Technician</label>
                        <select name="assigned_user_id" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium">
                            <option value="">Unassigned</option>
                            @foreach($staffUsers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ str_replace('_', ' ', $s->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">SLA Resolution Target</label>
                        <select name="sla_hours" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono font-semibold">
                            <option value="4">4 Hours (Urgent)</option>
                            <option value="8">8 Hours (High)</option>
                            <option value="24" selected>24 Hours (Standard)</option>
                            <option value="48">48 Hours (Low)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Detailed Description / Problem Notes *</label>
                    <textarea name="message" rows="3" placeholder="Provide full context from subscriber phone call or ticket complaint..." required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30">
                        Log Support Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
