<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Services\Billing\BillingEngine;
use App\Services\Payments\MpesaDarajaService;
use App\Services\Payments\PaymentOrchestratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    public function index()
    {
        $client = null;
        if (auth()->check() && auth()->user()->client_id) {
            $client = Client::find(auth()->user()->client_id);
        }

        if (!$client) {
            $client = Client::with(['billingPlan', 'invoices', 'payments', 'supportTickets.replies', 'latestActivationJob'])->first();
        }

        if (!$client) {
            $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::create(['name' => 'Demo ISP', 'status' => 'active']);
            $client = Client::create([
                'tenant_id'           => $tenant->id,
                'name'                => 'Demo Subscriber',
                'email'               => 'subscriber@smartoh.net',
                'phone'               => '254700112233',
                'account_number'      => 'ACC-1001',
                'status'              => 'active',
                'connection_status'   => 'online',
                'current_cycle_start' => now()->startOfMonth(),
                'current_cycle_end'   => now()->endOfMonth(),
                'data_usage_bytes'    => 125 * 1024 * 1024 * 1024,
            ]);
        }

        $latestInvoice = Invoice::where('client_id', $client->id)->latest('issue_date')->first();
        $latestPayment = Payment::where('client_id', $client->id)->latest('id')->first();
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
            'latestPayment',
            'tickets',
            'daysRemaining',
            'cycleProgressPct',
            'usageGb',
            'quotaGb',
            'quotaProgressPct'
        ));
    }

    /**
     * Real-time status polling endpoint for Customer Portal.
     * Accurately distinguishes:
     * - Payment pending verification
     * - Payment received — activation retrying
     * - Payment verified — activation applied
     * - Connected (only when router/RADIUS connectivity is confirmed)
     */
    public function liveStatus(Request $request)
    {
        $clientId = $request->query('client_id') ?? auth()->user()?->client_id;
        $client = $clientId ? Client::find($clientId) : Client::first();

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Subscriber not found'], 404);
        }

        $latestPayment = Payment::where('client_id', $client->id)->latest('id')->first();

        return response()->json([
            'success'             => true,
            'account_number'      => $client->account_number,
            'subscriber_status'   => $client->status,
            'connection_status'   => $client->connection_status,
            'is_connected'        => $client->connection_status === 'online',
            'wallet_balance'      => (float) $client->wallet_balance,
            'outstanding_balance' => $client->outstanding_balance,
            'latest_payment'      => $latestPayment ? [
                'reference'             => $latestPayment->external_transaction_id ?? $latestPayment->transaction_reference,
                'amount'                => (float) $latestPayment->amount,
                'verification_status'   => $latestPayment->verification_status,
                'reconciliation_status' => $latestPayment->reconciliation_status,
                'activation_status'     => $latestPayment->activation_status,
                'human_status'          => $latestPayment->human_lifecycle_status,
            ] : null,
        ]);
    }

    public function payMpesa(Request $request, MpesaDarajaService $mpesa)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount'    => 'required|numeric|min:1',
        ]);

        $client = Client::findOrFail($request->client_id);
        $res = $mpesa->initiateStkPush($client, (float) $request->amount);

        // If sandbox instant callback is requested (default in local portal), process callback through orchestrator
        if (!$request->has('await_webhook') || !$request->boolean('await_webhook')) {
            $mockCallback = [
                'Body' => [
                    'stkCallback' => [
                        'CheckoutRequestID' => $res['checkout_request_id'],
                        'ResultCode'        => 0,
                        'ResultDesc'        => 'The service request is processed successfully.',
                        'CallbackMetadata'  => [
                            'Item' => [
                                ['Name' => 'Amount', 'Value' => (float) $request->amount],
                                ['Name' => 'MpesaReceiptNumber', 'Value' => 'QGH' . rand(1000000, 9999999)],
                                ['Name' => 'PhoneNumber', 'Value' => $client->phone],
                            ]
                        ]
                    ]
                ]
            ];
            $cbResult = $mpesa->processCallback($mockCallback);
            $statusMsg = $cbResult['human_status'] ?? 'Payment verified';
            return back()->with('success', "Payment of KES {$request->amount} processed! Status: {$statusMsg}");
        }

        return back()->with('info', "Payment pending verification. Please complete the M-Pesa prompt on {$client->phone}.");
    }

    /**
     * Customer-submitted manual reference / screenshot claim.
     * NEVER automatically activates service; marks as unverified_manual and queues exception for ISP review.
     */
    public function submitManualClaim(Request $request, PaymentOrchestratorService $orchestrator)
    {
        $validated = $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'submitted_reference' => 'required|string|max:100',
            'amount'              => 'required|numeric|min:1',
            'payment_method'      => 'nullable|string|max:50',
            'notes'               => 'nullable|string|max:500',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $payment = $orchestrator->submitUnverifiedManualClaim(
            $client,
            $validated['submitted_reference'],
            (float) $validated['amount'],
            $validated['payment_method'] ?? 'bank_transfer',
            $validated['notes'] ?? null
        );

        return back()->with(
            'warning',
            "Reference '{$payment->external_transaction_id}' submitted for verification. Unverified manual references do not automatically activate service until confirmed by bank/M-Pesa statement reconciliation."
        );
    }

    public function redeemVoucher(Request $request, BillingEngine $engine)
    {
        $request->validate([
            'client_id'    => 'required|exists:clients,id',
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
            'subject'   => 'required|string|max:255',
            'category'  => 'required|in:no_internet,slow_speeds,billing,relocation,router_replacement,other',
            'priority'  => 'required|in:low,medium,high,urgent',
            'message'   => 'required|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $ticket = SupportTicket::create([
            'client_id'     => $client->id,
            'ticket_number' => 'TCK-' . strtoupper(Str::random(6)),
            'subject'       => $validated['subject'],
            'category'      => $validated['category'],
            'priority'      => $validated['priority'],
            'status'        => 'open',
            'sla_due_at'    => now()->addHours(6),
        ]);

        TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'is_client_reply'   => true,
            'message'           => $validated['message'],
        ]);

        return back()->with('success', "Support ticket {$ticket->ticket_number} submitted. A technician has been notified.");
    }
}
