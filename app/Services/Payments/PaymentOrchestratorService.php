<?php

namespace App\Services\Payments;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentGateway;
use App\Models\PaymentReconciliationException;
use App\Models\PaymentWebhookEvent;
use App\Models\StatementImport;
use App\Models\User;
use App\Services\Accounting\FinanceService;
use App\Services\Network\NetworkActivationOrchestrator;
use App\Services\Payments\Adapters\BankStatementAndManualAdapter;
use App\Services\Payments\Adapters\MpesaDarajaAdapter;
use App\Services\Payments\Adapters\PaypalAdapter;
use App\Services\Payments\Adapters\PesapalAdapter;
use App\Services\Payments\Contracts\PaymentProviderAdapterInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentOrchestratorService
{
    /** @var array<string, PaymentProviderAdapterInterface> */
    protected array $adapters = [];

    public function __construct(
        protected NetworkActivationOrchestrator $activationOrchestrator,
        protected FinanceService $financeService
    ) {
        $this->registerAdapter(new MpesaDarajaAdapter());
        $this->registerAdapter(new PesapalAdapter());
        $this->registerAdapter(new PaypalAdapter());
        $this->registerAdapter(new BankStatementAndManualAdapter());
    }

    public function registerAdapter(PaymentProviderAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->getProviderIdentifier()] = $adapter;
    }

    public function getAdapter(string $provider): PaymentProviderAdapterInterface
    {
        $normalized = strtolower($provider);
        if (str_contains($normalized, 'mpesa') || str_contains($normalized, 'daraja') || str_contains($normalized, 'pochi')) {
            return $this->adapters['mpesa_daraja'];
        }
        if (str_contains($normalized, 'pesapal')) {
            return $this->adapters['pesapal'];
        }
        if (str_contains($normalized, 'paypal')) {
            return $this->adapters['paypal'];
        }
        return $this->adapters['bank_manual'];
    }

    public function getAllProviderCapabilities(): array
    {
        return array_map(fn($a) => $a->getProviderCapabilitiesAndLimitations(), $this->adapters);
    }

    /**
     * Receive and process a real-time provider webhook or callback for a specific tenant or shortcode.
     * - Authenticates signature
     * - Enforces idempotency / replay protection
     * - Resolves tenant, subscriber, and invoices
     * - Allocates payment (handles partial, multi-invoice, overpayment)
     * - Immediately triggers network activation without artificial waiting period
     */
    public function handleIncomingWebhook(string $provider, Request $request, ?int $explicitTenantId = null): array
    {
        $adapter = $this->getAdapter($provider);
        $payload = $request->all();

        // 1. Resolve tenant gateway by explicit tenantId, shortcode, or pending checkout_request_id
        $gateway = $this->resolveTenantGateway($provider, $payload, $explicitTenantId);
        $tenantId = $explicitTenantId ?? $gateway?->tenant_id;

        // If still no tenantId, check if there is a pending payment matching CheckoutRequestID or OrderTrackingId
        $parsed = $adapter->parseWebhookPayload($payload, $gateway);
        if (!$tenantId && !empty($parsed['checkout_request_id'])) {
            $pendingPayment = Payment::withoutGlobalScopes()
                ->where('external_transaction_id', $parsed['checkout_request_id'])
                ->orWhere('idempotency_key', $parsed['checkout_request_id'])
                ->first();
            if ($pendingPayment) {
                $tenantId = $pendingPayment->tenant_id;
                if (!$gateway && $pendingPayment->payment_gateway_id) {
                    $gateway = PaymentGateway::withoutGlobalScopes()->find($pendingPayment->payment_gateway_id);
                }
            }
        }

        // If still no tenantId, try resolving by account_reference (e.g. C2B BillRefNumber matching unique account_number or invoice_number)
        if (!$tenantId && !empty($parsed['account_reference'])) {
            $ref = trim((string) $parsed['account_reference']);
            $matchedClient = Client::withoutGlobalScopes()->where('account_number', $ref)->first();
            if ($matchedClient) {
                $tenantId = $matchedClient->tenant_id;
            } else {
                $matchedInv = Invoice::withoutGlobalScopes()->where('invoice_number', $ref)->first();
                if ($matchedInv) {
                    $tenantId = $matchedInv->tenant_id;
                }
            }
        }

        // 2. Authenticate and verify webhook signature
        $signatureValid = $adapter->verifyWebhookSignature($request, $gateway);
        $eventId = $parsed['event_id'] ?? ('EVT-' . Str::random(12));
        $idempotencyKey = "{$provider}:{$tenantId}:{$eventId}";

        if (!$signatureValid) {
            PaymentWebhookEvent::create([
                'tenant_id'          => $tenantId,
                'payment_gateway_id' => $gateway?->id,
                'provider'           => $provider,
                'event_id'           => $eventId,
                'idempotency_key'    => 'REJECTED-' . Str::random(10),
                'event_type'         => $parsed['status'] ?? 'invalid_signature',
                'signature_valid'    => false,
                'source_ip'          => $request->ip(),
                'payload'            => $payload,
                'status'             => 'failed',
                'error_message'      => 'Webhook signature validation failed',
            ]);

            return [
                'success' => false,
                'status'  => 401,
                'message' => 'Webhook signature verification failed. Untrusted event rejected.',
            ];
        }

        if (!$tenantId) {
            return [
                'success' => false,
                'status'  => 422,
                'message' => 'Unable to map webhook event to an authorized ISP tenant.',
            ];
        }

        // Update gateway last webhook timestamp
        if ($gateway) {
            $gateway->update([
                'last_webhook_received_at' => Carbon::now(),
                'health_status'            => 'healthy',
            ]);
        }

        // 3. Process inside atomic DB transaction with idempotency lock
        return DB::transaction(function () use ($provider, $parsed, $payload, $tenantId, $gateway, $eventId, $idempotencyKey, $request) {
            // Replay / Duplicate Callback Check
            $existingEvent = PaymentWebhookEvent::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
            if ($existingEvent && $existingEvent->status === 'processed') {
                $existingPayment = Payment::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where(function ($q) use ($parsed, $idempotencyKey) {
                        $q->where('external_transaction_id', $parsed['external_transaction_id'])
                          ->orWhere('idempotency_key', $idempotencyKey);
                    })->first();

                return [
                    'success'      => true,
                    'status'       => 200,
                    'is_duplicate' => true,
                    'message'      => 'Duplicate callback ignored idempotently.',
                    'payment_id'   => $existingPayment?->id,
                ];
            }

            // Also check if payment with same external_transaction_id is already completed for this tenant
            if (!empty($parsed['external_transaction_id']) && ($parsed['status'] ?? '') === 'verified') {
                $alreadyCompleted = Payment::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('external_transaction_id', $parsed['external_transaction_id'])
                    ->where('verification_status', 'verified')
                    ->lockForUpdate()
                    ->first();

                if ($alreadyCompleted) {
                    return [
                        'success'      => true,
                        'status'       => 200,
                        'is_duplicate' => true,
                        'message'      => 'Transaction already verified and reconciled.',
                        'payment_id'   => $alreadyCompleted->id,
                    ];
                }
            }

            $webhookEvent = $existingEvent ?? PaymentWebhookEvent::create([
                'tenant_id'          => $tenantId,
                'payment_gateway_id' => $gateway?->id,
                'provider'           => $provider,
                'event_id'           => $eventId,
                'idempotency_key'    => $idempotencyKey,
                'event_type'         => $parsed['status'] ?? 'payment',
                'signature_valid'    => true,
                'source_ip'          => $request->ip(),
                'payload'            => $payload,
                'status'             => 'received',
            ]);

            // Handle Reversal / Chargeback / Refund
            if (($parsed['status'] ?? '') === 'reversed') {
                $revResult = $this->processReversalByExternalId(
                    $tenantId,
                    (string) $parsed['external_transaction_id'],
                    (string) ($parsed['result_desc'] ?? 'Provider reversal callback')
                );
                $webhookEvent->update(['status' => 'processed', 'processed_at' => Carbon::now()]);
                return array_merge($revResult, ['status' => 200]);
            }

            // Handle Failed payment callback
            if (($parsed['status'] ?? '') === 'failed') {
                $pending = $this->findPendingPayment($tenantId, $parsed);
                if ($pending) {
                    $pending->update([
                        'status'              => 'failed',
                        'verification_status' => 'rejected',
                        'raw_payload'         => json_encode($payload),
                    ]);
                }
                $webhookEvent->update(['status' => 'processed', 'processed_at' => Carbon::now()]);
                return [
                    'success'    => false,
                    'status'     => 200,
                    'message'    => $parsed['result_desc'] ?? 'Payment failed at provider.',
                    'payment_id' => $pending?->id,
                ];
            }

            // Handle Verified Payment
            $payment = $this->recordAndReconcileVerifiedPayment($tenantId, $parsed, $payload, $gateway, $idempotencyKey);
            $webhookEvent->update(['status' => 'processed', 'processed_at' => Carbon::now()]);

            return [
                'success'       => true,
                'status'        => 200,
                'is_duplicate'  => false,
                'message'       => 'Payment verified, reconciled, and network activation triggered.',
                'payment_id'    => $payment->id,
                'client_id'     => $payment->client_id,
                'reconciled'    => $payment->reconciliation_status,
                'activation'    => $payment->activation_status,
                'human_status'  => $payment->human_lifecycle_status,
            ];
        });
    }

    /**
     * Record a verified payment, match subscriber & invoices, allocate funds, and immediately trigger network activation.
     */
    public function recordAndReconcileVerifiedPayment(
        int $tenantId,
        array $parsed,
        array $rawPayload = [],
        ?PaymentGateway $gateway = null,
        ?string $idempotencyKey = null
    ): Payment {
        $pending = $this->findPendingPayment($tenantId, $parsed);
        $client = $pending?->client_id ? Client::withoutGlobalScopes()->where('tenant_id', $tenantId)->find($pending->client_id) : null;
        $targetInvoice = $pending?->invoice_id ? Invoice::withoutGlobalScopes()->where('tenant_id', $tenantId)->find($pending->invoice_id) : null;

        // Resolve client or invoice by account_reference or phone_number if not already linked
        $accRef = trim((string) ($parsed['account_reference'] ?? ''));
        if (!$client && $accRef !== '') {
            $targetInvoice = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('invoice_number', $accRef)
                ->first();

            if ($targetInvoice) {
                $client = Client::withoutGlobalScopes()->where('tenant_id', $tenantId)->find($targetInvoice->client_id);
            } else {
                $client = Client::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where(function ($q) use ($accRef) {
                        $q->where('account_number', $accRef)
                          ->orWhere('pppoe_username', $accRef)
                          ->orWhere('phone', $accRef);
                    })->first();
            }
        }

        if (!$client && !empty($parsed['phone_number'])) {
            $normPhone = $this->normalizePhone((string) $parsed['phone_number']);
            $client = Client::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where(function ($q) use ($normPhone, $parsed) {
                    $q->where('phone', $parsed['phone_number'])
                      ->orWhere('phone', $normPhone)
                      ->orWhere('phone', '0' . substr($normPhone, -9));
                })->first();
        }

        $amount = (float) ($parsed['amount'] ?? ($pending?->amount ?? 0));
        $extId = (string) ($parsed['external_transaction_id'] ?? ('TX-' . strtoupper(Str::random(8))));
        $settlementStatus = $parsed['settlement_status'] ?? 'direct_to_isp_merchant';

        if ($pending) {
            $pending->update([
                'client_id'               => $client?->id ?? $pending->client_id,
                'invoice_id'              => $targetInvoice?->id ?? $pending->invoice_id,
                'external_transaction_id' => $extId,
                'amount'                  => $amount > 0 ? $amount : $pending->amount,
                'phone_number'            => $parsed['phone_number'] ?: $pending->phone_number,
                'payer_name'              => $parsed['payer_name'] ?: ($client?->name ?? $pending->payer_name),
                'account_reference'       => $accRef ?: $pending->account_reference,
                'status'                  => 'completed',
                'verification_status'     => 'verified',
                'settlement_status'       => $settlementStatus,
                'source_channel'          => $parsed['source_channel'] ?? 'api_webhook',
                'paid_at'                 => Carbon::now(),
                'verified_at'             => Carbon::now(),
                'settled_at'              => in_array($settlementStatus, ['direct_to_isp_merchant', 'settled_in_isp_account'], true) ? Carbon::now() : null,
                'raw_payload'             => json_encode($rawPayload),
            ]);
            $payment = $pending->fresh();
        } else {
            $payment = Payment::withoutGlobalScopes()->create([
                'tenant_id'               => $tenantId,
                'site_id'                 => $client?->site_id,
                'client_id'               => $client?->id,
                'invoice_id'              => $targetInvoice?->id,
                'payment_gateway_id'      => $gateway?->id,
                'transaction_reference'   => 'PAY-' . strtoupper(Str::random(8)),
                'external_transaction_id' => $extId,
                'payment_method'          => $parsed['payment_method'] ?? 'mpesa_c2b',
                'source_channel'          => $parsed['source_channel'] ?? 'api_webhook',
                'amount'                  => $amount,
                'allocated_amount'        => 0,
                'unallocated_amount'      => $amount,
                'currency'                => $parsed['currency'] ?? 'KES',
                'phone_number'            => $parsed['phone_number'] ?? null,
                'account_reference'       => $accRef ?: ($client?->account_number ?? null),
                'payer_name'              => $parsed['payer_name'] ?: ($client?->name ?? 'Subscriber'),
                'status'                  => 'completed',
                'verification_status'     => 'verified',
                'reconciliation_status'   => 'unreconciled',
                'settlement_status'       => $settlementStatus,
                'activation_status'       => 'not_started',
                'idempotency_key'         => $idempotencyKey ?? ($extId . '-' . $tenantId),
                'raw_payload'             => json_encode($rawPayload),
                'paid_at'                 => Carbon::now(),
                'verified_at'             => Carbon::now(),
                'settled_at'              => in_array($settlementStatus, ['direct_to_isp_merchant', 'settled_in_isp_account'], true) ? Carbon::now() : null,
            ]);
        }

        // If subscriber could not be identified (wrong reference / unknown phone), queue in exception table
        if (!$client) {
            $payment->update([
                'reconciliation_status' => 'exception',
                'activation_status'     => 'not_started',
            ]);

            PaymentReconciliationException::withoutGlobalScopes()->create([
                'tenant_id'               => $tenantId,
                'payment_id'              => $payment->id,
                'external_transaction_id' => $payment->external_transaction_id,
                'submitted_reference'     => $accRef ?: 'MISSING',
                'phone_number'            => $payment->phone_number,
                'payer_name'              => $payment->payer_name,
                'amount'                  => $payment->amount,
                'exception_reason'        => 'unknown_account_reference',
                'status'                  => 'open',
            ]);

            AuditLog::create([
                'tenant_id'      => $tenantId,
                'event'          => 'payment.exception_queued',
                'auditable_type' => Payment::class,
                'auditable_id'   => $payment->id,
                'new_values'     => ['reference' => $accRef, 'amount' => $payment->amount, 'reason' => 'unknown_account_reference'],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);

            return $payment->fresh();
        }

        // Allocate payment to subscriber invoices and calculate service eligibility
        $this->allocatePaymentAndActivateSubscriber($payment, $client, $targetInvoice);

        return $payment->fresh();
    }

    /**
     * Allocate a verified payment across unpaid/partially-paid invoices (oldest first or target invoice first),
     * credit any overpayment to subscriber wallet, evaluate service eligibility, and trigger immediate activation.
     */
    public function allocatePaymentAndActivateSubscriber(Payment $payment, Client $client, ?Invoice $targetInvoice = null): void
    {
        $remainingToAllocate = (float) $payment->amount;
        $totalAllocatedToInvoices = 0.0;

        // Fetch unpaid/partially_paid/overdue invoices for this client
        $invoices = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->orderByRaw($targetInvoice ? "CASE WHEN id = {$targetInvoice->id} THEN 0 ELSE 1 END" : 'issue_date ASC')
            ->orderBy('issue_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($invoices as $inv) {
            if ($remainingToAllocate <= 0) {
                break;
            }
            $balanceDue = max(0, (float) $inv->total_amount - (float) $inv->paid_amount);
            if ($balanceDue <= 0) {
                continue;
            }

            $allocAmount = min($remainingToAllocate, $balanceDue);
            $newPaid = round((float) $inv->paid_amount + $allocAmount, 2);
            $newStatus = $newPaid >= (float) $inv->total_amount ? 'paid' : 'partially_paid';

            $inv->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
            ]);

            PaymentAllocation::withoutGlobalScopes()->create([
                'tenant_id'       => $client->tenant_id,
                'payment_id'      => $payment->id,
                'invoice_id'      => $inv->id,
                'client_id'       => $client->id,
                'amount'          => $allocAmount,
                'allocation_type' => 'invoice_payment',
                'notes'           => "Allocated KES {$allocAmount} to Invoice {$inv->invoice_number} ({$newStatus})",
            ]);

            if (!$payment->invoice_id) {
                $payment->invoice_id = $inv->id;
            }

            $remainingToAllocate = round($remainingToAllocate - $allocAmount, 2);
            $totalAllocatedToInvoices = round($totalAllocatedToInvoices + $allocAmount, 2);
        }

        // If there were no open invoices or there is an overpayment remainder, credit subscriber wallet
        $reconciliationStatus = 'matched';
        if ($invoices->isEmpty()) {
            // Direct plan renewal / wallet topup
            $client->wallet_balance = round((float) $client->wallet_balance + $remainingToAllocate, 2);
            PaymentAllocation::withoutGlobalScopes()->create([
                'tenant_id'       => $client->tenant_id,
                'payment_id'      => $payment->id,
                'invoice_id'      => null,
                'client_id'       => $client->id,
                'amount'          => $remainingToAllocate,
                'allocation_type' => 'wallet_overpayment',
                'notes'           => 'Credited to subscriber wallet / direct plan renewal',
            ]);
            $totalAllocatedToInvoices = (float) $payment->amount;
            $remainingToAllocate = 0.0;
        } elseif ($remainingToAllocate > 0) {
            // Overpayment credited to wallet
            $client->wallet_balance = round((float) $client->wallet_balance + $remainingToAllocate, 2);
            PaymentAllocation::withoutGlobalScopes()->create([
                'tenant_id'       => $client->tenant_id,
                'payment_id'      => $payment->id,
                'invoice_id'      => null,
                'client_id'       => $client->id,
                'amount'          => $remainingToAllocate,
                'allocation_type' => 'wallet_overpayment',
                'notes'           => "Overpayment surplus of KES {$remainingToAllocate} credited to subscriber wallet",
            ]);
            $reconciliationStatus = 'overpaid_wallet_credited';
        }

        // Check if any invoice is still partially paid
        $stillUnpaidInvoices = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->get();

        $outstandingBalance = $stillUnpaidInvoices->sum(fn($i) => max(0, (float) $i->total_amount - (float) $i->paid_amount));

        if ($outstandingBalance > 0 && $stillUnpaidInvoices->where('status', 'partially_paid')->isNotEmpty()) {
            $reconciliationStatus = 'partially_allocated';
        }

        $payment->update([
            'client_id'             => $client->id,
            'invoice_id'            => $payment->invoice_id,
            'allocated_amount'      => (float) $payment->amount - $remainingToAllocate,
            'unallocated_amount'    => $remainingToAllocate,
            'reconciliation_status' => $reconciliationStatus,
            'reconciled_at'         => Carbon::now(),
        ]);

        // Record in General Ledger
        $this->financeService->recordPayment($payment);

        // Determine if subscriber is eligible for activation:
        // Eligible if all due invoices are paid, OR if no invoices exist and payment covers plan price (or wallet balance covers plan price)
        $planPrice = (float) ($client->billingPlan?->price ?? 0);
        $isEligibleForActivation = ($outstandingBalance <= 0.01) && (
            $invoices->isNotEmpty() || (float) $payment->amount >= $planPrice || (float) $client->wallet_balance >= $planPrice
        );

        if ($isEligibleForActivation) {
            $validityDays = $client->billingPlan?->validity_days ?: 30;
            $baseStart = ($client->current_cycle_end && $client->current_cycle_end->isFuture())
                ? $client->current_cycle_end
                : Carbon::now();

            $client->update([
                'wallet_balance'      => $client->wallet_balance,
                'current_cycle_start' => Carbon::now(),
                'current_cycle_end'   => $baseStart->copy()->addDays($validityDays),
            ]);

            // Immediately enqueue and execute network activation
            $this->activationOrchestrator->enqueueAndExecuteActivation($client->fresh(), $payment, 'activate');
        } else {
            // Partial payment recorded, waiting for balance completion
            $client->save();
            $payment->update(['activation_status' => 'awaiting_full_balance']);
        }

        AuditLog::create([
            'tenant_id'      => $client->tenant_id,
            'event'          => 'payment.reconciled',
            'auditable_type' => Payment::class,
            'auditable_id'   => $payment->id,
            'new_values'     => [
                'receipt'               => $payment->external_transaction_id,
                'amount'                => $payment->amount,
                'reconciliation_status' => $reconciliationStatus,
                'outstanding_balance'   => $outstandingBalance,
                'eligible_to_activate'  => $isEligibleForActivation,
            ],
            'ip_address'     => request()?->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Controlled reversal / chargeback handling:
     * - Reverses invoice allocations
     * - Recalculates subscriber eligibility
     * - Restricts only that specific subscriber if no longer eligible (never indiscriminate network-wide disconnection)
     */
    public function processReversalByExternalId(int $tenantId, string $externalTransactionId, string $reason = 'Provider reversal'): array
    {
        $payment = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($externalTransactionId) {
                $q->where('external_transaction_id', $externalTransactionId)
                  ->orWhere('transaction_reference', $externalTransactionId);
            })
            ->lockForUpdate()
            ->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Original transaction not found for reversal.'];
        }

        if ($payment->status === 'reversed') {
            return ['success' => true, 'message' => 'Transaction already reversed.', 'payment_id' => $payment->id];
        }

        return $this->reversePayment($payment, $reason);
    }

    public function reversePayment(Payment $payment, string $reason = 'Reversed by provider'): array
    {
        return DB::transaction(function () use ($payment, $reason) {
            $allocations = PaymentAllocation::withoutGlobalScopes()
                ->where('payment_id', $payment->id)
                ->get();

            foreach ($allocations as $alloc) {
                if ($alloc->allocation_type === 'invoice_payment' && $alloc->invoice_id) {
                    $inv = Invoice::withoutGlobalScopes()->find($alloc->invoice_id);
                    if ($inv) {
                        $newPaid = max(0, round((float) $inv->paid_amount - (float) $alloc->amount, 2));
                        $inv->update([
                            'paid_amount' => $newPaid,
                            'status'      => $newPaid <= 0 ? 'unpaid' : 'partially_paid',
                        ]);
                    }
                } elseif ($alloc->allocation_type === 'wallet_overpayment' && $alloc->client_id) {
                    $cli = Client::withoutGlobalScopes()->find($alloc->client_id);
                    if ($cli) {
                        $cli->update([
                            'wallet_balance' => max(0, round((float) $cli->wallet_balance - (float) $alloc->amount, 2)),
                        ]);
                    }
                }
            }

            $payment->update([
                'status'                => 'reversed',
                'verification_status'   => 'reversed',
                'reconciliation_status' => 'reversed',
                'settlement_status'     => 'reversed',
                'activation_status'     => 'not_started',
                'reversed_at'           => Carbon::now(),
                'reversal_reason'       => $reason,
            ]);

            $client = $payment->client_id ? Client::withoutGlobalScopes()->find($payment->client_id) : null;
            $restricted = false;

            if ($client) {
                // Reassess subscriber eligibility: if they now have unpaid balance or insufficient wallet, restrict ONLY this subscriber
                $outstanding = $client->outstanding_balance;
                if ($outstanding > 0 || (float) $client->wallet_balance <= 0) {
                    $this->activationOrchestrator->enqueueAndExecuteActivation($client, $payment, 'restrict');
                    $restricted = true;
                }
            }

            AuditLog::create([
                'tenant_id'      => $payment->tenant_id,
                'user_id'        => auth()->id(),
                'event'          => 'payment.reversed',
                'auditable_type' => Payment::class,
                'auditable_id'   => $payment->id,
                'new_values'     => [
                    'reason'                => $reason,
                    'subscriber_restricted' => $restricted,
                    'client_id'             => $client?->id,
                ],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);

            return [
                'success'               => true,
                'payment_id'            => $payment->id,
                'subscriber_restricted' => $restricted,
                'message'               => "Payment {$payment->transaction_reference} reversed and subscriber eligibility reassessed.",
            ];
        });
    }

    /**
     * Record a customer-submitted manual payment claim (e.g., M-Pesa SMS text / bank ref / screenshot ref).
     * Strictly marks as 'unverified_manual' and queues an exception for staff/statement verification.
     * NEVER automatically authorizes network access.
     */
    public function submitUnverifiedManualClaim(
        Client $client,
        string $submittedReference,
        float $claimedAmount,
        string $paymentMethod = 'bank_transfer',
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($client, $submittedReference, $claimedAmount, $paymentMethod, $notes) {
            $cleanRef = strtoupper(trim($submittedReference));

            $payment = Payment::withoutGlobalScopes()->create([
                'tenant_id'               => $client->tenant_id,
                'site_id'                 => $client->site_id,
                'client_id'               => $client->id,
                'transaction_reference'   => 'CLAIM-' . strtoupper(Str::random(8)),
                'external_transaction_id' => $cleanRef,
                'payment_method'          => $paymentMethod,
                'source_channel'          => 'customer_claim_unverified',
                'amount'                  => $claimedAmount,
                'allocated_amount'        => 0,
                'unallocated_amount'      => $claimedAmount,
                'currency'                => 'KES',
                'phone_number'            => $client->phone,
                'account_reference'       => $client->account_number,
                'payer_name'              => $client->name,
                'status'                  => 'pending',
                'verification_status'     => 'unverified_manual',
                'reconciliation_status'   => 'exception',
                'settlement_status'       => 'pending_settlement',
                'activation_status'       => 'not_started',
                'raw_payload'             => json_encode(['submitted_reference' => $cleanRef, 'notes' => $notes]),
            ]);

            PaymentReconciliationException::withoutGlobalScopes()->create([
                'tenant_id'               => $client->tenant_id,
                'payment_id'              => $payment->id,
                'external_transaction_id' => $cleanRef,
                'submitted_reference'     => $cleanRef,
                'phone_number'            => $client->phone,
                'payer_name'              => $client->name,
                'amount'                  => $claimedAmount,
                'exception_reason'        => 'unverified_manual_claim',
                'status'                  => 'open',
            ]);

            AuditLog::create([
                'tenant_id'      => $client->tenant_id,
                'user_id'        => auth()->id(),
                'event'          => 'payment.unverified_claim_submitted',
                'auditable_type' => Payment::class,
                'auditable_id'   => $payment->id,
                'new_values'     => ['submitted_reference' => $cleanRef, 'amount' => $claimedAmount],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);

            return $payment;
        });
    }

    /**
     * Import a verified Bank or M-Pesa/Pochi Statement CSV content for non-API accounts.
     * Distinctly marks transactions with source_channel = 'statement_import'.
     */
    public function importStatementCsv(int $tenantId, string $csvContent, string $filename, string $provider = 'bank_manual', ?User $user = null): StatementImport
    {
        $lines = array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $csvContent))));
        $totalRows = 0;
        $matchedCount = 0;
        $exceptionCount = 0;
        $duplicateCount = 0;

        foreach ($lines as $idx => $line) {
            $cols = str_getcsv($line);
            if ($idx === 0 && isset($cols[0]) && preg_match('/(ref|receipt|trans|date)/i', $cols[0])) {
                continue; // skip header row
            }
            if (count($cols) < 3) {
                continue;
            }

            $totalRows++;
            // Expected columns: [0 => external_ref, 1 => account_reference, 2 => amount, 3 => payer_name/phone]
            $extRef = trim((string) ($cols[0] ?? ''));
            $accRef = trim((string) ($cols[1] ?? ''));
            $amount = (float) preg_replace('/[^0-9.]/', '', (string) ($cols[2] ?? '0'));
            $payer  = trim((string) ($cols[3] ?? 'Statement Depositor'));

            if ($extRef === '' || $amount <= 0) {
                continue;
            }

            $exists = Payment::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('external_transaction_id', $extRef)
                ->where('verification_status', 'verified')
                ->exists();

            if ($exists) {
                $duplicateCount++;
                continue;
            }

            $payment = $this->recordAndReconcileVerifiedPayment($tenantId, [
                'external_transaction_id' => $extRef,
                'account_reference'       => $accRef,
                'amount'                  => $amount,
                'currency'                => 'KES',
                'payer_name'              => $payer,
                'phone_number'            => null,
                'status'                  => 'verified',
                'settlement_status'       => 'settled_in_isp_account',
                'payment_method'          => $provider === 'pochi_statement' ? 'pochi_la_biashara' : 'bank_transfer',
                'source_channel'          => 'statement_import',
            ], ['csv_row' => $cols]);

            if ($payment->reconciliation_status === 'exception') {
                $exceptionCount++;
            } else {
                $matchedCount++;
            }
        }

        $import = StatementImport::withoutGlobalScopes()->create([
            'tenant_id'           => $tenantId,
            'uploaded_by_user_id' => $user?->id,
            'filename'            => $filename,
            'provider'            => $provider,
            'total_rows'          => $totalRows,
            'matched_count'       => $matchedCount,
            'exception_count'     => $exceptionCount,
            'duplicate_count'     => $duplicateCount,
            'status'              => 'completed',
        ]);

        AuditLog::create([
            'tenant_id'      => $tenantId,
            'user_id'        => $user?->id,
            'event'          => 'payment.statement_imported',
            'auditable_type' => StatementImport::class,
            'auditable_id'   => $import->id,
            'new_values'     => [
                'filename'   => $filename,
                'matched'    => $matchedCount,
                'exceptions' => $exceptionCount,
                'duplicates' => $duplicateCount,
            ],
            'ip_address'     => request()?->ip() ?? '127.0.0.1',
        ]);

        return $import;
    }

    /**
     * Resolve an open reconciliation exception (e.g. wrong account reference or verified manual claim)
     * by assigning it to a valid subscriber in the same tenant.
     */
    public function resolveException(
        PaymentReconciliationException $exception,
        Client $client,
        User $staffUser,
        ?string $notes = null
    ): Payment {
        if ((int) $exception->tenant_id !== (int) $client->tenant_id) {
            abort(403, 'Cross-tenant reconciliation violation.');
        }

        return DB::transaction(function () use ($exception, $client, $staffUser, $notes) {
            $payment = Payment::withoutGlobalScopes()->findOrFail($exception->payment_id);

            $payment->update([
                'client_id'           => $client->id,
                'status'              => 'completed',
                'verification_status' => 'verified',
                'verified_at'         => $payment->verified_at ?? Carbon::now(),
                'paid_at'             => $payment->paid_at ?? Carbon::now(),
            ]);

            $this->allocatePaymentAndActivateSubscriber($payment, $client);

            $exception->update([
                'status'              => 'resolved',
                'resolved_client_id'  => $client->id,
                'resolved_invoice_id' => $payment->fresh()->invoice_id,
                'resolved_by_user_id' => $staffUser->id,
                'resolved_at'         => Carbon::now(),
                'resolution_notes'    => $notes ?? "Manually reconciled to subscriber {$client->account_number}",
            ]);

            AuditLog::create([
                'tenant_id'      => $client->tenant_id,
                'user_id'        => $staffUser->id,
                'event'          => 'payment.exception_resolved',
                'auditable_type' => PaymentReconciliationException::class,
                'auditable_id'   => $exception->id,
                'new_values'     => [
                    'payment_id'     => $payment->id,
                    'account_number' => $client->account_number,
                    'notes'          => $notes,
                ],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);

            return $payment->fresh();
        });
    }

    protected function resolveTenantGateway(string $provider, array $payload, ?int $explicitTenantId = null): ?PaymentGateway
    {
        $shortcode = $payload['BusinessShortCode']
            ?? ($payload['Body']['stkCallback']['BusinessShortCode'] ?? null)
            ?? ($payload['shortcode'] ?? null);

        $query = PaymentGateway::withoutGlobalScopes()->where('is_active', true);

        if ($explicitTenantId) {
            $query->where('tenant_id', $explicitTenantId);
        }

        if ($shortcode) {
            $byShortcode = (clone $query)->where('shortcode', (string) $shortcode)->first();
            if ($byShortcode) {
                return $byShortcode;
            }
        }

        if ($explicitTenantId) {
            return $query->first();
        }

        return null;
    }

    protected function findPendingPayment(int $tenantId, array $parsed): ?Payment
    {
        $checkoutId = $parsed['checkout_request_id'] ?? null;
        $internalRef = $parsed['internal_reference'] ?? null;

        if (!$checkoutId && !$internalRef) {
            return null;
        }

        return Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($checkoutId, $internalRef) {
                if ($checkoutId) {
                    $q->where('external_transaction_id', $checkoutId)
                      ->orWhere('idempotency_key', $checkoutId);
                }
                if ($internalRef) {
                    $q->orWhere('transaction_reference', $internalRef);
                }
            })
            ->lockForUpdate()
            ->first();
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '0')) {
            return '254' . substr($digits, 1);
        }
        return $digits;
    }
}
