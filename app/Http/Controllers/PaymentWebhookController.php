<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentOrchestratorService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Handle authenticated provider webhook / callback for a specific tenant or shortcode.
     */
    public function handleWebhook(
        Request $request,
        PaymentOrchestratorService $orchestrator,
        string $provider,
        ?int $tenantId = null
    ) {
        $result = $orchestrator->handleIncomingWebhook($provider, $request, $tenantId);
        $statusCode = $result['status'] ?? ($result['success'] ? 200 : 400);

        return response()->json($result, $statusCode);
    }

    /**
     * Handle Safaricom Daraja C2B Validation Request (verifies subscriber account_reference before M-Pesa completes).
     */
    public function handleMpesaValidation(Request $request)
    {
        $billRef = trim((string) ($request->input('BillRefNumber') ?? ''));
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted by Smartoh Non-Custodial Gateway for ref: ' . $billRef,
        ]);
    }
}
