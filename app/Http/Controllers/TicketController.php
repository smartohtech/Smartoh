<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Dedicated Support Tickets Helpdesk Desk
     */
    public function index(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;

        $query = SupportTicket::with(['client', 'assignedUser', 'replies'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Priority
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by Category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQ) use ($search) {
                      $clientQ->where('name', 'like', "%{$search}%")
                              ->orWhere('account_number', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(12)->withQueryString();

        // Summary Counts for Helpdesk metrics
        $baseQuery = SupportTicket::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
        $counts = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'waiting_client' => (clone $baseQuery)->where('status', 'waiting_client')->count(),
            'resolved' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            'urgent' => (clone $baseQuery)->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'sla_breached' => (clone $baseQuery)->whereNotIn('status', ['resolved', 'closed'])
                ->where('sla_due_at', '<', now())
                ->count(),
        ];

        // Available clients and staff for creating tickets
        $clients = Client::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->orderBy('name')
            ->get(['id', 'name', 'account_number', 'phone']);

        $staffUsers = User::when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('role', ['isp_admin', 'technician', 'billing_officer', 'super_admin'])
            ->get();

        return view('tickets.index', compact('tickets', 'counts', 'clients', 'staffUsers'));
    }

    /**
     * Store new support ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:no_internet,slow_speeds,billing,relocation,router_replacement,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_user_id' => 'nullable|exists:users,id',
            'sla_hours' => 'nullable|integer|min:1|max:168',
            'message' => 'required|string',
        ]);

        $slaHours = (int)($request->input('sla_hours') ?: ($validated['priority'] === 'urgent' ? 4 : ($validated['priority'] === 'high' ? 8 : 24)));

        $ticket = SupportTicket::create([
            'tenant_id' => app()->bound('current_tenant_id') ? app('current_tenant_id') : 1,
            'client_id' => $validated['client_id'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'ticket_number' => 'TCK-' . strtoupper(Str::random(6)),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'sla_due_at' => now()->addHours($slaHours),
        ]);

        // Add initial ticket description as first reply
        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_client_reply' => false,
            'message' => $validated['message'],
        ]);

        AuditLog::create([
            'tenant_id' => $ticket->tenant_id,
            'user_id' => auth()->id(),
            'event' => 'ticket.created',
            'auditable_type' => SupportTicket::class,
            'auditable_id' => $ticket->id,
            'new_values' => ['ticket_number' => $ticket->ticket_number, 'subject' => $ticket->subject],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Support Ticket {$ticket->ticket_number} created successfully.");
    }

    /**
     * Show ticket details and discussion thread
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'client.billingPlan',
            'client.site',
            'client.ontDevice',
            'client.clientRouter',
            'assignedUser',
            'replies.user',
        ]);

        $staffUsers = User::when($ticket->tenant_id, fn($q) => $q->where('tenant_id', $ticket->tenant_id))
            ->whereIn('role', ['isp_admin', 'technician', 'billing_officer', 'super_admin'])
            ->get();

        return view('tickets.show', compact('ticket', 'staffUsers'));
    }

    /**
     * Post a reply to a support ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'status' => 'nullable|in:open,in_progress,waiting_client,resolved,closed',
        ]);

        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_client_reply' => false,
            'message' => $validated['message'],
        ]);

        // Update status if passed or default to in_progress if currently open
        $newStatus = $request->input('status');
        if ($newStatus) {
            $ticket->update(['status' => $newStatus]);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply posted successfully.');
    }

    /**
     * Update ticket status or assignee
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:open,in_progress,waiting_client,resolved,closed',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $updates = array_filter($validated, fn($val) => !is_null($val));
        if (!empty($updates)) {
            $ticket->update($updates);
        }

        return back()->with('success', "Ticket {$ticket->ticket_number} updated.");
    }
}
