@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('tickets.index') }}" class="hover:text-white transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <span>Support Tickets</span>
                </a>
                <span>/</span>
                <span class="text-slate-300 font-mono">{{ $ticket->ticket_number }}</span>
            </div>
            <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-mono">{{ $ticket->ticket_number }}: {{ $ticket->subject }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-mono uppercase {{ $ticket->status === 'open' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : ($ticket->status === 'in_progress' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/30' : ($ticket->status === 'waiting_client' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/30' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30')) }}">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $ticket->priority === 'urgent' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : ($ticket->priority === 'high' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40' : 'bg-blue-500/20 text-blue-400 border border-blue-500/40') }}">
                    {{ $ticket->priority }} Priority
                </span>
            </div>
        </div>

        <!-- Quick Status Actions -->
        <div class="flex items-center space-x-2">
            @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
            <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                @csrf
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-emerald-600/20 flex items-center space-x-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>Mark Resolved</span>
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                @csrf
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs border border-slate-700 transition">
                    Re-open Ticket
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Main Grid: Conversation Thread & Subscriber Details Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Discussion & Reply Thread -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Conversation Thread List -->
            <div class="space-y-4">
                @foreach($ticket->replies as $reply)
                <div class="p-4 rounded-2xl border shadow-xl text-xs space-y-2 {{ $reply->is_client_reply ? 'bg-slate-900/90 border-slate-800' : 'bg-blue-950/30 border-blue-500/30' }}">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold {{ $reply->is_client_reply ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}">
                                {{ $reply->is_client_reply ? 'CLI' : substr($reply->user?->name ?? 'Staff', 0, 2) }}
                            </div>
                            <div>
                                <span class="font-bold text-white">
                                    {{ $reply->is_client_reply ? ($ticket->client?->name ?? 'Subscriber') : ($reply->user?->name ?? 'Smartoh Staff') }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono ml-1.5">
                                    ({{ $reply->is_client_reply ? 'Client Message' : str_replace('_', ' ', $reply->user?->role ?? 'Support') }})
                                </span>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-500">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                    </div>

                    <div class="text-slate-300 leading-relaxed whitespace-pre-line text-xs font-sans pt-1">
                        {{ $reply->message }}
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Post New Reply Box -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3">
                <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                    <span>Post Staff Reply / Dispatch Note</span>
                </h3>

                <form method="POST" action="{{ route('tickets.reply', $ticket) }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <textarea name="message" rows="4" placeholder="Enter message to subscriber or internal technical notes..." required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 font-sans"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                        <div class="flex items-center space-x-2">
                            <label class="text-[11px] text-slate-400">Update Status:</label>
                            <select name="status" class="px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs">
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="waiting_client" {{ $ticket->status === 'waiting_client' ? 'selected' : '' }}>Waiting Client</option>
                                <option value="resolved">Mark Resolved</option>
                                <option value="closed">Mark Closed</option>
                            </select>
                        </div>

                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition self-end sm:self-auto">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Col: Ticket SLA & Subscriber Profile Card -->
        <div class="space-y-4">
            
            <!-- Ticket Info Card -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3.5 text-xs">
                <div class="border-b border-slate-800 pb-2.5 flex items-center justify-between">
                    <h3 class="font-bold text-white">Ticket Specifications</h3>
                    <span class="text-[10px] font-mono text-cyan-400">SLA: {{ $ticket->sla_due_at ? $ticket->sla_due_at->diffForHumans() : 'None' }}</span>
                </div>

                <div class="space-y-2 font-mono text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Category:</span>
                        <span class="text-slate-200 capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Priority:</span>
                        <span class="font-bold uppercase {{ $ticket->priority === 'urgent' ? 'text-rose-400' : ($ticket->priority === 'high' ? 'text-amber-400' : 'text-blue-400') }}">{{ $ticket->priority }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Created:</span>
                        <span class="text-slate-300">{{ $ticket->created_at->format('M d, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">SLA Target:</span>
                        <span class="text-amber-400">{{ $ticket->sla_due_at ? $ticket->sla_due_at->format('M d, H:i') : 'N/A' }}</span>
                    </div>
                </div>

                <!-- Update Assignee Form -->
                <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="pt-3 border-t border-slate-800 space-y-2">
                    @csrf
                    <label class="block text-[11px] font-medium text-slate-400">Reassign Staff / Technician:</label>
                    <div class="flex items-center space-x-2">
                        <select name="assigned_user_id" class="flex-1 px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs">
                            <option value="">Unassigned</option>
                            @foreach($staffUsers as $s)
                            <option value="{{ $s->id }}" {{ $ticket->assigned_user_id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold">Assign</button>
                    </div>
                </form>
            </div>

            <!-- Subscriber Snapshot Card -->
            @if($ticket->client)
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3 text-xs">
                <div class="border-b border-slate-800 pb-2.5 flex items-center justify-between">
                    <h3 class="font-bold text-white">Subscriber Snapshot</h3>
                    <a href="{{ route('clients.show', $ticket->client) }}" class="text-[10px] text-blue-400 hover:text-blue-300 underline font-mono">View Client Profile &rarr;</a>
                </div>

                <div class="space-y-2 font-mono text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Name:</span>
                        <strong class="text-white font-sans">{{ $ticket->client->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Account:</span>
                        <span class="text-cyan-400">{{ $ticket->client->account_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Phone:</span>
                        <span class="text-slate-200">{{ $ticket->client->phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Status:</span>
                        <span class="uppercase font-bold {{ $ticket->client->status === 'active' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $ticket->client->status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Billing Plan:</span>
                        <span class="text-indigo-400 font-sans">{{ $ticket->client->billingPlan?->name ?? 'Standard' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Speed:</span>
                        <span class="text-slate-300">{{ $ticket->client->billingPlan?->formatted_speed ?? 'N/A' }}</span>
                    </div>
                    @if($ticket->client->pppoe_username)
                    <div class="flex justify-between">
                        <span class="text-slate-400">PPPoE User:</span>
                        <span class="text-slate-300">{{ $ticket->client->pppoe_username }}</span>
                    </div>
                    @endif
                    @if($ticket->client->ontDevice)
                    <div class="flex justify-between">
                        <span class="text-slate-400">Optical Rx:</span>
                        <span class="font-bold {{ $ticket->client->ontDevice->rx_power_dbm >= -24.0 ? 'text-emerald-400' : 'text-amber-400' }}">{{ $ticket->client->ontDevice->rx_power_dbm }} dBm</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
