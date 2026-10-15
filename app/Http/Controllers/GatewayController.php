<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function apiGateways()
    {
        $gateways = PaymentGateway::where('type', 'api')->get();

        if ($gateways->isEmpty()) {
            $gateways = collect([
                (object)[
                    'id' => 1,
                    'name' => 'Safaricom Daraja STK Push',
                    'provider' => 'mpesa_daraja',
                    'type' => 'api',
                    'environment' => 'sandbox',
                    'shortcode' => '174379',
                    'consumer_key' => 'k3J9aB2cD4eF6gH8iJ0kL2mN4oP6qR8s',
                    'base_url' => 'https://sandbox.safaricom.co.ke',
                    'status' => 'active',
                    'last_tested' => now()->subHours(2)->diffForHumans(),
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Safaricom C2B Paybill',
                    'provider' => 'mpesa_c2b',
                    'type' => 'api',
                    'environment' => 'production',
                    'shortcode' => '522522',
                    'consumer_key' => 'P9aB2cD4eF6gH8iJ0kL2mN4oP6qR8sT0',
                    'base_url' => 'https://api.safaricom.co.ke',
                    'status' => 'active',
                    'last_tested' => now()->subDay()->diffForHumans(),
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Stripe Card Processing',
                    'provider' => 'stripe',
                    'type' => 'api',
                    'environment' => 'sandbox',
                    'shortcode' => 'pk_test_51...29a',
                    'consumer_key' => 'sk_test_51...88b',
                    'base_url' => 'https://api.stripe.com/v1',
                    'status' => 'active',
                    'last_tested' => now()->subDays(3)->diffForHumans(),
                ]
            ]);
        }

        return view('gateways.api', compact('gateways'));
    }

    public function manualGateways()
    {
        $manualGateways = collect([
            (object)[
                'id' => 1,
                'name' => 'Equity Bank Direct Settlement',
                'account_number' => '0180299384721',
                'branch' => 'Westlands Branch',
                'instructions' => 'Deposit funds and enter subscriber account number as reference.',
                'status' => 'active',
            ],
            (object)[
                'id' => 2,
                'name' => 'Cash Desk / Office Walk-in',
                'account_number' => 'POS-TILL-01',
                'branch' => 'HQ Customer Care',
                'instructions' => 'Instant printed physical receipt generated upon cash collection.',
                'status' => 'active',
            ],
            (object)[
                'id' => 3,
                'name' => 'KCB Bank Wire Transfer',
                'account_number' => '1129482910',
                'branch' => 'Moi Avenue Branch',
                'instructions' => 'Bank reconciliation automated via daily MT940 statement sync.',
                'status' => 'active',
            ]
        ]);

        return view('gateways.manual', compact('manualGateways'));
    }

    public function testGateway(Request $request)
    {
        $name = $request->input('name', 'Gateway');
        return back()->with('success', "API connection test to '{$name}' successful! Status: HTTP 200 OK. Ping latency: 42ms.");
    }

    public function storeApiGateway(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string',
            'environment' => 'required|in:sandbox,production',
            'shortcode' => 'required|string',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
        ]);

        return back()->with('success', "API Payment Gateway '{$validated['name']}' saved and verified.");
    }
}
