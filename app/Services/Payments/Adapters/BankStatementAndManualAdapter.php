<?php

namespace App\Services\Payments\Adapters;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentProviderAdapterInterface;
use Illuminate\Http\Request;

class BankStatementAndManualAdapter implements PaymentProviderAdapterInterface
{
    public function getProviderIdentifier(): string
    {
        return 'bank_manual';
    }

    public function verifyWebhookSignature(Request $request, ?PaymentGateway $gateway = null): bool
    {
        if ($gateway && !empty($gateway->webhook_secret)) {
            $sig = $request->header('X-Bank-Signature') ?? $request->header('X-Webhook-Signature');
            if (!$sig) {
                return false;
            }
            $expected = hash_hmac('sha256', $request->getContent(), $gateway->webhook_secret);
            return hash_equals($expected, (string) $sig) || hash_equals($gateway->webhook_secret, (string) $sig);
        }
        return false; // Bank webhooks without configured secret are never trusted automatically
    }

    public function parseWebhookPayload(array $payload, ?PaymentGateway $gateway = null): array
    {
        $txId = $payload['transaction_reference'] ?? $payload['bank_ref'] ?? null;
        return [
            'event_id'                => $txId,
            'external_transaction_id' => $txId,
            'checkout_request_id'     => null,
            'internal_reference'      => $txId,
            'account_reference'       => trim((string) ($payload['account_reference'] ?? $payload['narration'] ?? '')),
            'amount'                  => (float) ($payload['amount'] ?? 0),
            'currency'                => $payload['currency'] ?? 'KES',
            'phone_number'            => $payload['phone_number'] ?? null,
            'payer_name'              => $payload['payer_name'] ?? 'Bank Depositor',
            'status'                  => 'verified',
            'settlement_status'       => 'settled_in_isp_account',
            'shortcode'               => $gateway?->shortcode,
            'payment_method'          => 'bank_transfer',
            'source_channel'          => 'api_webhook',
            'result_desc'             => 'Confirmed Bank Deposit Notification',
        ];
    }

    public function initiatePayment(Client $client, float $amount, ?Invoice $invoice, PaymentGateway $gateway): array
    {
        return [
            'success'               => true,
            'checkout_request_id'   => null,
            'transaction_reference' => 'MANUAL-' . $client->account_number,
            'settlement_mode'       => 'manual_statement',
            'message'               => "This bank/Pochi account does not support real-time API push. Please deposit to {$gateway->shortcode} referencing {$client->account_number}. Service activates upon verified statement import or bank IPN.",
        ];
    }

    public function queryTransactionStatus(string $reference, PaymentGateway $gateway): array
    {
        return [
            'success'             => false,
            'reference'           => $reference,
            'verification_status' => 'pending_verification',
            'settlement_status'   => 'pending_settlement',
            'provider_message'    => 'Limitation disclosed: Non-API bank accounts and personal Pochi la Biashara accounts do not provide real-time status query APIs. Use Statement CSV/XLSX Import or authorized bank IPN.',
        ];
    }

    public function getProviderCapabilitiesAndLimitations(): array
    {
        return [
            'provider'               => 'Direct Bank Accounts (Equity/KCB/Co-op/NCBA) & Non-API Pochi la Biashara',
            'non_custodial'          => true,
            'direct_settlement'      => 'Funds are deposited directly into the ISP’s bank account or Pochi wallet.',
            'realtime_api_available' => false,
            'limitations'            => 'Accounts without a signed bank IPN webhook or Daraja API integration do NOT offer real-time API push. Smartoh supports controlled CSV/XLSX statement import and manual verification queue. Customer-submitted screenshots or unverified text codes never automatically authorize network access.',
        ];
    }
}
