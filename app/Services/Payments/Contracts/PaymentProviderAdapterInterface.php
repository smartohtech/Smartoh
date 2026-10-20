<?php

namespace App\Services\Payments\Contracts;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

interface PaymentProviderAdapterInterface
{
    public function getProviderIdentifier(): string;

    /**
     * Validate authenticity and signature of an incoming webhook or callback.
     */
    public function verifyWebhookSignature(Request $request, ?PaymentGateway $gateway = null): bool;

    /**
     * Parse raw provider payload into a normalized event array:
     * - event_id
     * - external_transaction_id
     * - internal_reference
     * - account_reference
     * - amount
     * - currency
     * - phone_number
     * - payer_name
     * - status ('verified', 'failed', 'reversed', 'pending_verification')
     * - settlement_status ('direct_to_isp_merchant', 'pending_provider_settlement', 'settled_in_isp_account', 'held_by_provider')
     * - shortcode
     */
    public function parseWebhookPayload(array $payload, ?PaymentGateway $gateway = null): array;

    /**
     * Initiate a payment prompt or checkout session directly against the ISP's own merchant credentials.
     */
    public function initiatePayment(Client $client, float $amount, ?Invoice $invoice, PaymentGateway $gateway): array;

    /**
     * Query transaction status directly from provider API when callback is delayed.
     */
    public function queryTransactionStatus(string $reference, PaymentGateway $gateway): array;

    /**
     * Disclose provider-specific API & settlement capabilities/limitations transparently.
     */
    public function getProviderCapabilitiesAndLimitations(): array;
}
