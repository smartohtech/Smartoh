<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Voucher;
use App\Services\Accounting\FinanceService;
use App\Services\Payments\MpesaDarajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function plans()
    {
        $plans = BillingPlan::withCount('clients')->latest()->get();
        return view('billing.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:daily,weekly,monthly,unlimited,data_cap',
            'price' => 'required|numeric|min:0',
            'download_speed_kbps' => 'required|integer|min:256',
            'upload_speed_kbps' => 'required|integer|min:256',
            'fup_limit_gb' => 'nullable|integer',
            'burst_limit' => 'nullable|string',
            'contention_ratio' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
        ]);

        $plan = BillingPlan::create(array_merge($validated, [
            'currency' => 'KES',
            'is_active' => true,
        ]));

        return back()->with('success', "Service Plan '{$plan->name}' created successfully!");
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['client', 'billingPlan'])->latest('issue_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15);
        $totalBilled = Invoice::sum('total_amount');
        $totalCollected = Invoice::sum('paid_amount');
        $totalOutstanding = max(0, $totalBilled - $totalCollected);

        return view('billing.invoices', compact('invoices', 'totalBilled', 'totalCollected', 'totalOutstanding'));
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['client', 'invoice'])->latest('created_at');

        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        $payments = $query->paginate(15);
        $totalVolume = Payment::where('status', 'completed')->sum('amount');
        $gateways = PaymentGateway::where('is_active', true)->get();

        return view('billing.payments', compact('payments', 'totalVolume', 'gateways'));
    }

    public function triggerStkPush(Request $request, MpesaDarajaService $mpesa, FinanceService $finance)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $client = Client::findOrFail($request->client_id);
        $invoice = Invoice::where('client_id', $client->id)->whereIn('status', ['unpaid', 'partially_paid'])->first();

        $result = $mpesa->initiateStkPush($client, (float) $request->amount, $invoice);

        // Auto reconcile for local demo/testing if simulation mode
        if ($result['success'] && request()->boolean('simulate_instant_payment')) {
            $payment = Payment::find($result['payment_id']);
            if ($payment) {
                $mockCallback = [
                    'Body' => [
                        'stkCallback' => [
                            'CheckoutRequestID' => $result['checkout_request_id'],
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
                $finance->recordPayment($payment->fresh());
            }
        }

        return back()->with('success', $result['message']);
    }

    public function vouchers()
    {
        $vouchers = Voucher::with('client')->latest()->paginate(20);
        $plans = BillingPlan::where('is_active', true)->get();
        $activeCount = Voucher::where('status', 'active')->count();
        $usedCount = Voucher::where('status', 'used')->count();

        return view('billing.vouchers', compact('vouchers', 'plans', 'activeCount', 'usedCount'));
    }

    public function generateVouchers(Request $request)
    {
        $validated = $request->validate([
            'billing_plan_id' => 'required|exists:billing_plans,id',
            'type' => 'required|in:hotspot,pppoe',
            'quantity' => 'required|integer|min:1|max:100',
            'duration_hours' => 'required|integer|min:1',
        ]);

        $plan = BillingPlan::findOrFail($validated['billing_plan_id']);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            Voucher::create([
                'tenant_id' => $plan->tenant_id,
                'billing_plan_id' => $plan->id,
                'code' => strtoupper(Str::random(4) . '-' . Str::random(4)),
                'type' => $validated['type'],
                'price' => $plan->price,
                'duration_minutes' => $validated['duration_hours'] * 60,
                'status' => 'active',
                'expires_at' => now()->addDays(90),
            ]);
        }

        return back()->with('success', "Generated {$validated['quantity']} {$validated['type']} vouchers for plan '{$plan->name}'!");
    }

    /**
     * Display the ISP Tenant's current SaaS subscription, 7-day trial status, and platform invoices.
     */
    public function subscription()
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()->tenant_id;
        $tenant = \App\Models\Tenant::findOrFail($tenantId);

        $subscription = $tenant->activeSubscription;
        $plans = \App\Models\SaasPlan::where('is_active', true)->with('currentVersion')->get();
        $invoices = $tenant->saasInvoices()->with('items')->latest()->take(10)->get();

        return view('billing.saas_subscription', compact('tenant', 'subscription', 'plans', 'invoices'));
    }

    /**
     * Submit simulated or M-Pesa payment for the ISP's SaaS platform subscription.
     */
    public function paySubscription(Request $request, \App\Services\SaaS\SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'phone'    => 'nullable|string',
            'provider' => 'required|in:mpesa,bank_transfer,card,manual',
        ]);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()->tenant_id;
        $tenant = \App\Models\Tenant::findOrFail($tenantId);
        $subscription = $tenant->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'No subscription found for this tenant.');
        }

        $ref = 'PAY-' . strtoupper(\Illuminate\Support\Str::random(8));

        $payment = $lifecycle->recordPaymentAndActivate(
            $subscription,
            (float) $subscription->amount,
            $ref,
            $validated['provider'],
            $request->user(),
            "Online subscription renewal paid via " . strtoupper($validated['provider'])
        );

        return back()->with('success', "Payment of {$subscription->currency} " . number_format($subscription->amount) . " confirmed (Ref: {$ref})! Your subscription is now ACTIVE_PAID.");
    }
}
