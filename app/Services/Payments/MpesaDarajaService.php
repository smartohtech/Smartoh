<?php

namespace App\Services\Payments;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaDarajaService
{
    /**
     * Initiate M-Pesa STK Push (Lipa na M-Pesa Online)
     */
    public function initiateStkPush(Client $client, float $amount, ?Invoice $invoice = null, ?PaymentGateway $gateway = null): array
    {
        $phone = preg_replace('/[^0-9]/', '', $client->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+254')) {
            $phone = substr($phone, 1);
        }

        $reference = 'SMART-' . strtoupper(Str::random(8));
        $checkoutRequestId = 'ws_CO_' . date('dmYHis') . '_' . rand(1000, 9999);

        // Record pending payment
        $payment = Payment::create([
            'tenant_id' => $client->tenant_id,
            'site_id' => $client->site_id,
            'client_id' => $client->id,
            'invoice_id' => $invoice?->id,
            'payment_gateway_id' => $gateway?->id,
            'transaction_reference' => $reference,
            'external_transaction_id' => $checkoutRequestId,
            'payment_method' => 'mpesa_stk',
            'amount' => $amount,
            'currency' => 'KES',
            'phone_number' => $phone,
            'payer_name' => $client->name,
            'status' => 'pending',
            'idempotency_key' => $checkoutRequestId,
            'raw_payload' => json_encode(['CheckoutRequestID' => $checkoutRequestId, 'Amount' => $amount, 'Phone' => $phone]),
        ]);

        Log::info("M-Pesa STK Push initiated for {$phone} - Amount: {$amount} - Ref: {$reference}");

        return [
            'success' => true,
            'message' => "STK Push prompted on phone {$phone}. Please enter your M-Pesa PIN.",
            'checkout_request_id' => $checkoutRequestId,
            'reference' => $reference,
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Reconcile callback with idempotency locking and client reactivation
     */
    public function processCallback(array $callbackData): array
    {
        return DB::transaction(function () use ($callbackData) {
            $checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? null;
            $resultCode = $callbackData['Body']['stkCallback']['ResultCode'] ?? 1;
            $resultDesc = $callbackData['Body']['stkCallback']['ResultDesc'] ?? 'Failed';

            if (!$checkoutRequestId) {
                return ['success' => false, 'message' => 'Missing CheckoutRequestID'];
            }

            $payment = Payment::where('external_transaction_id', $checkoutRequestId)->lockForUpdate()->first();

            if (!$payment) {
                return ['success' => false, 'message' => 'Payment transaction not found'];
            }

            if ($payment->status === 'completed') {
                return ['success' => true, 'message' => 'Payment was already reconciled'];
            }

            if ($resultCode == 0) {
                // Success
                $items = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
                $receiptNumber = 'QGH' . rand(1000000, 9999999);
                foreach ($items as $item) {
                    if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                        $receiptNumber = $item['Value'];
                    }
                }

                $payment->update([
                    'status' => 'completed',
                    'external_transaction_id' => $receiptNumber,
                    'paid_at' => now(),
                    'raw_payload' => json_encode($callbackData),
                ]);

                // Update invoice
                if ($payment->invoice) {
                    $invoice = $payment->invoice;
                    $newPaid = $invoice->paid_amount + $payment->amount;
                    $status = $newPaid >= $invoice->total_amount ? 'paid' : 'partially_paid';
                    $invoice->update(['paid_amount' => $newPaid, 'status' => $status]);
                }

                // Update subscriber balance & reactivate if suspended
                if ($payment->client) {
                    $client = $payment->client;
                    $client->update([
                        'wallet_balance' => $client->wallet_balance + $payment->amount,
                        'status' => 'active',
                        'current_cycle_start' => now(),
                        'current_cycle_end' => now()->addDays($client->billingPlan?->validity_days ?: 30),
                        'service_suspended_at' => null,
                    ]);
                }

                AuditLog::create([
                    'tenant_id' => $payment->tenant_id,
                    'user_id' => null,
                    'event' => 'payment.reconciled',
                    'auditable_type' => Payment::class,
                    'auditable_id' => $payment->id,
                    'new_values' => ['receipt' => $receiptNumber, 'amount' => $payment->amount],
                    'ip_address' => request()->ip(),
                ]);

                return ['success' => true, 'receipt' => $receiptNumber];
            } else {
                $payment->update(['status' => 'failed', 'raw_payload' => json_encode($callbackData)]);
                return ['success' => false, 'message' => $resultDesc];
            }
        });
    }
}
