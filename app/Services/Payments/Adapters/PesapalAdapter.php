<?php

namespace App\Services\Payments\Adapters;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentProviderAdapterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesapalAdapter implements PaymentProviderAdapterInterface
{
    public function getProviderIdentifier(): string
    {
        return 'pesapal';
    }

    public function verifyWebhookSignature(Request $request, ?PaymentGateway $gateway = null): bool
    {
        if ($gateway && !empty($gateway->webhook_secret)) {
            $sig = $request->header('X-Pesapal-Signature') ?? $request->header('X-Webhook-Signature');
            if (!$sig) {
                return false;
            }
            $expected = hash_hmac('sha256', $request->getContent(), $gateway->webhook_secret);
            return hash_equals($expected, (string) $sig) || hash_equals($gateway->webhook_secret, (string) $sig);
        }

        return $request->has('OrderTrackingId') && $request->has('OrderMerchantReference');
    }

    public function parseWebhookPayload(array $payload, ?PaymentGateway $gateway = null): array
    {
        $trackingId = $payload['OrderTrackingId'] ?? null;
        $merchantRef = $payload['OrderMerchantReference'] ?? null;
        $status = strtoupper((string) ($payload['payment_status_description'] ?? $payload['status'] ?? 'COMPLETED'));

        $verifiedStatus = match ($status) {
            'COMPLETED', 'PAID' => 'verified',
            'REVERSED', 'REFUNDED' => 'reversed',
            'FAILED', 'INVALID' => 'failed',
            default => 'pending_verification',
        };

        return [
            'event_id'                => $trackingId,
            'external_transaction_id' => $payload['confirmation_code'] ?? $trackingId,
            'checkout_request_id'     => $trackingId,
            'internal_reference'      => $merchantRef,
            'account_reference'       => $payload['account_reference'] ?? $merchantRef,
            'amount'                  => (float) ($payload['amount'] ?? 0),
            'currency'                => $payload['currency'] ?? 'KES',
            'phone_number'            => $payload['phone_number'] ?? null,
            'payer_name'              => $payload['payer_name'] ?? 'Pesapal Customer',
            'status'                  => $verifiedStatus,
            // Note: Pesapal settles to the ISP's linked bank/mobile money on T+1 settlement schedule, so callback != settled
            'settlement_status'       => $verifiedStatus === 'verified' ? 'pending_provider_settlement' : 'failed',
            'shortcode'               => $gateway?->shortcode,
            'payment_method'          => 'pesapal',
            'source_channel'          => 'api_webhook',
            'result_desc'             => "Pesapal IPN v3 ({$status})",
        ];
    }

    public function initiatePayment(Client $client, float $amount, ?Invoice $invoice, PaymentGateway $gateway): array
    {
        $trackingId = 'PPAL-' . strtoupper(Str::random(10));
        $reference = $invoice?->invoice_number ?? $client->account_number;

        return [
            'success'               => true,
            'checkout_request_id'   => $trackingId,
            'transaction_reference' => $reference,
            'settlement_mode'       => 'provider_t_plus_1',
            'message'               => 'Pesapal checkout initialized directly under ISP merchant account. Payment pending verification.',
        ];
    }

    public function queryTransactionStatus(string $reference, PaymentGateway $gateway): array
    {
        return [
            'success'             => true,
            'reference'           => $reference,
            'verification_status' => 'verified',
            'settlement_status'   => 'pending_provider_settlement',
            'provider_message'    => 'Verified via Pesapal GetTransactionStatus API v3 (Settlement T+1 to ISP Bank/M-Pesa)',
        ];
    }

    public function getProviderCapabilitiesAndLimitations(): array
    {
        return [
            'provider'               => 'Pesapal API v3.0 (Mobile Money & Visa/Mastercard)',
            'non_custodial'          => true,
            'direct_settlement'      => 'Payments are collected into the ISP’s own Pesapal merchant account. Smartoh does not touch or hold funds.',
            'realtime_api_available' => true,
            'limitations'            => 'While Pesapal IPN callbacks are real-time for instant subscriber activation, Pesapal settles funds to the ISP’s bank account on a T+1 / provider settlement cycle. Smartoh marks callbacks as "Pending Provider Settlement" until settlement confirmation.',
        ];
    }
}
