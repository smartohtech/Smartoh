<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Services\Billing\BillingEngine;
use App\Services\Payments\MpesaDarajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    public function index()
    {
        // For logged in client or demo preview client
        $client = null;
        if (auth()->check() && auth()->user()->client_id) {
            $client = Client::find(auth()->user()->client_id);
        }

        if (!$client) {
            $client = Client::with(['billingPlan', 'invoices', 'payments', 'supportTickets.replies'])->first();
        }

        if (!$client) {
            return redirect()->route('dashboard')->with('error', 'No client record available.');
        }

        $latestInvoice = Invoice::where('client_id', $client->id)->latest('issue_date')->first();
        $tickets = SupportTicket::where('client_id', $client->id)->with('replies')->latest()->get();

        // Calculate cycle progress
        $daysRemaining = $client->current_cycle_end ? max(0, now()->diffInDays($client->current_cycle_end, false)) : 0;
        $totalCycleDays = ($client->current_cycle_start && $client->current_cycle_end) ? max(1, $client->current_cycle_start->diffInDays($client->current_cycle_end)) : 30;
        $cycleProgressPct = min(100, round((($totalCycleDays - $daysRemaining) / $totalCycleDays) * 100));

        // Data usage in GB
        $usageGb = round($client->data_usage_bytes / (1024 * 1024 * 1024), 2);
        $quotaGb = $client->billingPlan?->fup_limit_gb ?: 500;
        $quotaProgressPct = min(100, round(($usageGb / $quotaGb) * 100));

        return view('client_portal.index', compact(
            'client',
            'latestInvoice',
            'tickets',
            'daysRemaining',
            'cycleProgressPct',
            'usageGb',
            'quotaGb',
            'quotaProgressPct'
        ));
    }

    public function payMpesa(Request $request, MpesaDarajaService $mpesa)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $client = Client::findOrFail($request->client_id);
        $res = $mpesa->initiateStkPush($client, (float) $request->amount);

        // Immediate simulation for local demo
        $mockCallback = [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => $res['checkout_request_id'],
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => $request->amount],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'QGH' . rand(1000000, 9999999)],
                            ['Name' => 'PhoneNumber', 'Value' => $client->phone],
                        ]
                    ]
                ]
            ]
        ];
        $mpesa->processCallback($mockCallback);

        return back()->with('success', "Payment of KES {$request->amount} confirmed via M-Pesa! Subscription renewed.");
    }

    public function redeemVoucher(Request $request, BillingEngine $engine)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'voucher_code' => 'required|string',
        ]);

        $client = Client::findOrFail($request->client_id);
        $res = $engine->redeemVoucher($client, $request->voucher_code);

        return back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    public function createTicket(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'category' => 'required|in:no_internet,slow_speeds,billing,relocation,router_replacement,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $ticket = SupportTicket::create([
            'client_id' => $client->id,
            'ticket_number' => 'TCK-' . strtoupper(Str::random(6)),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'sla_due_at' => now()->addHours(6),
        ]);

        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'is_client_reply' => true,
            'message' => $validated['message'],
        ]);

        return back()->with('success', "Support ticket {$ticket->ticket_number} submitted. A technician has been notified.");
    }
}
