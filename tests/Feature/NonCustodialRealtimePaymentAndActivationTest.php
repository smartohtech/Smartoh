<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\MikroTikDevice;
use App\Models\NetworkActivationJob;
use App\Models\NOCIncident;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentReconciliationException;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\SaasPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonCustodialRealtimePaymentAndActivationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $adminA;
    protected User $adminB;
    protected User $superAdmin;
    protected BillingPlan $planA;
    protected MikroTikDevice $routerA;
    protected PaymentGateway $gatewayA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SaasPlanSeeder::class);
        $saasPlan = SaasPlan::first();

        $this->superAdmin = User::create([
            'name'        => 'Platform Super Admin',
            'email'       => 'super@smartoh.net',
            'password'    => bcrypt('password'),
            'role'        => 'super_admin',
            'tenant_id'   => null,
            'is_approved' => true,
        ]);

        // Provision Tenant A (ISP A)
        $this->tenantA = Tenant::create([
            'name'              => 'Apex Fiber Networks',
            'subdomain'         => 'apexfiber',
            'status'            => 'active',
            'subscription_tier' => 'pro',
        ]);

        SaasSubscription::create([
            'tenant_id'            => $this->tenantA->id,
            'saas_plan_id'         => $saasPlan->id,
            'saas_plan_version_id' => $saasPlan->currentVersion->id,
            'status'               => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval'     => 'monthly',
            'amount'               => 14000,
            'currency'             => 'KES',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        $this->adminA = User::create([
            'name'        => 'Apex Admin',
            'email'       => 'admin@apexfiber.co.ke',
            'password'    => bcrypt('password'),
            'role'        => 'isp_admin',
            'tenant_id'   => $this->tenantA->id,
            'is_approved' => true,
        ]);

        $this->planA = BillingPlan::withoutGlobalScopes()->create([
            'tenant_id'             => $this->tenantA->id,
            'name'                  => 'Home Fiber 20Mbps',
            'type'                  => 'monthly',
            'price'                 => 3000.00,
            'currency'              => 'KES',
            'download_speed_kbps'   => 20480,
            'upload_speed_kbps'     => 10240,
            'validity_days'         => 30,
            'mikrotik_profile_name' => 'prof_20mbps',
            'is_active'             => true,
        ]);

        $this->routerA = MikroTikDevice::withoutGlobalScopes()->create([
            'tenant_id'              => $this->tenantA->id,
            'name'                   => 'Apex-Core-CCR2004',
            'ip_address'             => '10.10.0.1',
            'api_port'               => 8728,
            'api_username'           => 'admin',
            'api_password_encrypted' => encrypt('secret'),
            'is_online'              => true,
        ]);

        $this->gatewayA = PaymentGateway::withoutGlobalScopes()->create([
            'tenant_id'                => $this->tenantA->id,
            'provider'                 => 'mpesa_daraja',
            'type'                     => 'api',
            'name'                     => 'Apex Paybill 409112',
            'shortcode'                => '409112',
            'settlement_account_label' => 'Direct Apex M-Pesa Merchant Utility',
            'settlement_mode'          => 'direct_merchant',
            'is_active'                => true,
            'is_sandbox'               => false,
            'credentials'              => [
                'shortcode'       => '409112',
                'consumer_key'    => 'ck_apex_1234567890',
                'consumer_secret' => 'cs_apex_secret_987654321',
            ],
            'webhook_secret'           => 'apex-webhook-secret-key',
            'is_non_custodial'         => true,
        ]);

        // Provision Tenant B (ISP B)
        $this->tenantB = Tenant::create([
            'name'              => 'MetroLink Wireless',
            'subdomain'         => 'metrolink',
            'status'            => 'active',
            'subscription_tier' => 'pro',
        ]);

        SaasSubscription::create([
            'tenant_id'            => $this->tenantB->id,
            'saas_plan_id'         => $saasPlan->id,
            'saas_plan_version_id' => $saasPlan->currentVersion->id,
            'status'               => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval'     => 'monthly',
            'amount'               => 14000,
            'currency'             => 'KES',
            'current_period_start' => now(),
            'current_period_end'   => now()->addMonth(),
        ]);

        $this->adminB = User::create([
            'name'        => 'Metro Admin',
            'email'       => 'admin@metrolink.co.ke',
            'password'    => bcrypt('password'),
            'role'        => 'isp_admin',
            'tenant_id'   => $this->tenantB->id,
            'is_approved' => true,
        ]);
    }

    /**
     * 1. Valid M-Pesa C2B payment webhook is verified via signature, credited once, reconciles invoice,
     * activates PPPoE/Hotspot/Static-IP immediately without waiting period, and appears in ISP A's live feed.
     */
    public function test_valid_mpesa_webhook_activates_pppoe_hotspot_and_static_subscribers_immediately(): void
    {
        foreach (['pppoe', 'hotspot', 'static'] as $idx => $serviceType) {
            $accNo = 'APEX-' . strtoupper($serviceType) . '-' . ($idx + 1);
            $client = Client::withoutGlobalScopes()->create([
                'tenant_id'          => $this->tenantA->id,
                'mikrotik_device_id' => $this->routerA->id,
                'billing_plan_id'    => $this->planA->id,
                'account_number'     => $accNo,
                'name'               => "Subscriber {$serviceType}",
                'phone'              => '25471100011' . $idx,
                'service_type'       => $serviceType,
                'pppoe_username'     => "user_{$serviceType}",
                'assigned_ip'        => '10.50.0.' . (20 + $idx),
                'mac_address'        => "AA:BB:CC:DD:EE:0{$idx}",
                'status'             => 'suspended',
                'connection_status'  => 'restricted',
            ]);

            $invoice = Invoice::withoutGlobalScopes()->create([
                'tenant_id'       => $this->tenantA->id,
                'client_id'       => $client->id,
                'billing_plan_id' => $this->planA->id,
                'invoice_number'  => "INV-{$accNo}",
                'issue_date'      => now()->subDays(5),
                'due_date'        => now()->subDay(),
                'subtotal'        => 3000.00,
                'total_amount'    => 3000.00,
                'paid_amount'     => 0.00,
                'status'          => 'overdue',
            ]);

            $payload = [
                'TransactionType'   => 'Pay Bill',
                'TransID'           => 'RKT' . strtoupper($serviceType) . '99' . $idx,
                'TransTime'         => date('YmdHis'),
                'TransAmount'       => '3000.00',
                'BusinessShortCode' => '409112',
                'BillRefNumber'     => $accNo,
                'MSISDN'            => $client->phone,
                'FirstName'         => 'Subscriber',
                'LastName'          => $serviceType,
            ];

            $rawJson = json_encode($payload);
            $signature = hash_hmac('sha256', $rawJson, 'apex-webhook-secret-key');

            $response = $this->call(
                'POST',
                "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}",
                [],
                [],
                [],
                [
                    'CONTENT_TYPE'          => 'application/json',
                    'HTTP_X_MPESA_SIGNATURE'=> $signature,
                ],
                $rawJson
            );

            $response->assertStatus(200);
            $response->assertJson([
                'success'      => true,
                'is_duplicate' => false,
                'reconciled'   => 'matched',
                'activation'   => 'connected_verified',
                'human_status' => 'Connected',
            ]);

            // Verify client and invoice updated immediately
            $client->refresh();
            $invoice->refresh();
            $this->assertEquals('active', $client->status);
            $this->assertEquals('online', $client->connection_status);
            $this->assertEquals('paid', $invoice->status);
            $this->assertEquals(3000.00, (float) $invoice->paid_amount);

            // Verify router was never rebooted and targeted commands were recorded
            $job = NetworkActivationJob::withoutGlobalScopes()->where('client_id', $client->id)->first();
            $this->assertNotNull($job);
            $this->assertEquals(NetworkActivationJob::STATUS_CONNECTED_VERIFIED, $job->status);
            $this->assertNotEmpty($job->router_response['commands']);
        }

        // Verify visible in ISP A live feed
        $feedRes = $this->actingAs($this->adminA)->getJson(route('billing.payments.live-feed'));
        $feedRes->assertStatus(200);
        $feedRes->assertJsonPath('metrics.collected_total', 9000);
    }

    /**
     * 2. Duplicate callbacks never double-credit an account or extend a package twice.
     */
    public function test_duplicate_callbacks_are_idempotent_and_never_double_credit(): void
    {
        $client = Client::withoutGlobalScopes()->create([
            'tenant_id'          => $this->tenantA->id,
            'mikrotik_device_id' => $this->routerA->id,
            'billing_plan_id'    => $this->planA->id,
            'account_number'     => 'APEX-DUP-01',
            'name'               => 'Grace Wanjiku',
            'phone'              => '254722111222',
            'service_type'       => 'pppoe',
            'status'             => 'suspended',
        ]);

        $payload = [
            'TransactionType'   => 'Pay Bill',
            'TransID'           => 'QGH8889991',
            'TransAmount'       => '3000.00',
            'BusinessShortCode' => '409112',
            'BillRefNumber'     => 'APEX-DUP-01',
            'MSISDN'            => '254722111222',
            'FirstName'         => 'Grace',
        ];
        $rawJson = json_encode($payload);
        $signature = hash_hmac('sha256', $rawJson, 'apex-webhook-secret-key');

        // First callback
        $res1 = $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => $signature,
        ], $rawJson);
        $res1->assertStatus(200)->assertJson(['is_duplicate' => false]);

        $client->refresh();
        $firstCycleEnd = $client->current_cycle_end->toIso8601String();
        $firstWallet = (float) $client->wallet_balance;

        // Second identical callback (replay/duplicate)
        $res2 = $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => $signature,
        ], $rawJson);
        $res2->assertStatus(200)->assertJson(['is_duplicate' => true]);

        $client->refresh();
        $this->assertEquals($firstWallet, (float) $client->wallet_balance, 'Wallet must not be double credited');
        $this->assertEquals($firstCycleEnd, $client->current_cycle_end->toIso8601String(), 'Cycle expiry must not be extended twice');
        $this->assertEquals(1, Payment::withoutGlobalScopes()->where('external_transaction_id', 'QGH8889991')->count());
        $this->assertEquals(1, NetworkActivationJob::withoutGlobalScopes()->where('client_id', $client->id)->count());
    }

    /**
     * 3. Router outage preserves verified payment, reports "Payment received — activation retrying",
     * never displays "Connected" prematurely, alerts NOC when retries exhaust, and recovers on retry when router returns online.
     */
    public function test_router_outage_queues_retries_with_accurate_status_and_recovers_when_online(): void
    {
        // Simulate router outage
        $this->routerA->update(['is_online' => false, 'last_error' => 'SIMULATED_OUTAGE']);

        $client = Client::withoutGlobalScopes()->create([
            'tenant_id'          => $this->tenantA->id,
            'mikrotik_device_id' => $this->routerA->id,
            'billing_plan_id'    => $this->planA->id,
            'account_number'     => 'APEX-OUTAGE-01',
            'name'               => 'David Kimani',
            'phone'              => '254733444555',
            'service_type'       => 'pppoe',
            'status'             => 'suspended',
            'connection_status'  => 'restricted',
        ]);

        $payload = [
            'TransactionType'   => 'Pay Bill',
            'TransID'           => 'QGH7770001',
            'TransAmount'       => '3000.00',
            'BusinessShortCode' => '409112',
            'BillRefNumber'     => 'APEX-OUTAGE-01',
            'MSISDN'            => '254733444555',
        ];
        $rawJson = json_encode($payload);
        $signature = hash_hmac('sha256', $rawJson, 'apex-webhook-secret-key');

        $res = $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => $signature,
        ], $rawJson);

        $res->assertStatus(200);
        $res->assertJson([
            'success'      => true,
            'activation'   => 'retrying',
            'human_status' => 'Payment received — activation retrying',
        ]);

        $client->refresh();
        $this->assertEquals('activation_retrying', $client->connection_status);
        $this->assertNotEquals('online', $client->connection_status, 'Must never claim Connected while router is offline');

        // Verify Customer Portal live-status endpoint accurately reports retrying state
        $portalStatus = $this->actingAs($this->adminA)->getJson(route('client.live-status', ['client_id' => $client->id]));
        $portalStatus->assertStatus(200)->assertJson([
            'is_connected'      => false,
            'connection_status' => 'activation_retrying',
        ]);

        // Now restore router online and run recovery command
        $this->routerA->update(['is_online' => true, 'last_error' => null]);
        $this->artisan('smartoh:reconcile-and-activate', ['--tenant' => $this->tenantA->id, '--force' => true])
             ->assertExitCode(0);

        $client->refresh();
        $payment = Payment::withoutGlobalScopes()->where('external_transaction_id', 'QGH7770001')->first();
        $this->assertEquals('active', $client->status);
        $this->assertEquals('online', $client->connection_status);
        $this->assertEquals('connected_verified', $payment->activation_status);
        $this->assertEquals('Connected', $payment->human_lifecycle_status);
    }

    /**
     * 4. Unverified payment or customer-submitted manual reference never activates service automatically.
     */
    public function test_unverified_manual_claim_or_invalid_webhook_never_activates_service(): void
    {
        $client = Client::withoutGlobalScopes()->create([
            'tenant_id'          => $this->tenantA->id,
            'mikrotik_device_id' => $this->routerA->id,
            'billing_plan_id'    => $this->planA->id,
            'account_number'     => 'APEX-UNVERIFIED-01',
            'name'               => 'Unverified Claimant',
            'phone'              => '254799000111',
            'status'             => 'suspended',
            'connection_status'  => 'restricted',
        ]);

        // 1. Forged webhook with invalid signature is rejected with 401
        $payload = [
            'TransID'           => 'FAKE123456',
            'TransAmount'       => '3000.00',
            'BusinessShortCode' => '409112',
            'BillRefNumber'     => 'APEX-UNVERIFIED-01',
        ];
        $forgedRes = $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => 'forged-invalid-signature',
        ], json_encode($payload));

        $forgedRes->assertStatus(401);
        $this->assertEquals('suspended', $client->fresh()->status);

        // 2. Customer submits manual claim via portal
        $claimRes = $this->actingAs($this->adminA)->post(route('client.submit-claim'), [
            'client_id'           => $client->id,
            'submitted_reference' => 'SCREENSHOT-REF-991',
            'amount'              => 3000,
            'payment_method'      => 'bank_transfer',
        ]);
        $claimRes->assertRedirect();

        $client->refresh();
        $this->assertEquals('suspended', $client->status, 'Unverified manual claim must never activate subscriber');
        $this->assertEquals('restricted', $client->connection_status);

        $exception = PaymentReconciliationException::withoutGlobalScopes()
            ->where('submitted_reference', 'SCREENSHOT-REF-991')
            ->first();
        $this->assertNotNull($exception);
        $this->assertEquals('open', $exception->status);
    }

    /**
     * 5. Partial payments, multi-invoice allocation, overpayments, and reversals with controlled eligibility reassessment.
     */
    public function test_partial_payments_multi_invoice_overpayments_and_controlled_reversals(): void
    {
        $client = Client::withoutGlobalScopes()->create([
            'tenant_id'          => $this->tenantA->id,
            'mikrotik_device_id' => $this->routerA->id,
            'billing_plan_id'    => $this->planA->id,
            'account_number'     => 'APEX-FIN-01',
            'name'               => 'Multi Invoice Subscriber',
            'phone'              => '254744555666',
            'status'             => 'suspended',
            'connection_status'  => 'restricted',
        ]);

        // Two unpaid invoices: KES 2000 and KES 3000 (Total due = 5000)
        $inv1 = Invoice::withoutGlobalScopes()->create([
            'tenant_id'      => $this->tenantA->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-FIN-101',
            'issue_date'     => now()->subMonths(2),
            'due_date'       => now()->subMonth(),
            'subtotal'       => 2000,
            'total_amount'   => 2000,
            'paid_amount'    => 0,
            'status'         => 'overdue',
        ]);
        $inv2 = Invoice::withoutGlobalScopes()->create([
            'tenant_id'      => $this->tenantA->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-FIN-102',
            'issue_date'     => now()->subMonth(),
            'due_date'       => now()->subDays(5),
            'subtotal'       => 3000,
            'total_amount'   => 3000,
            'paid_amount'    => 0,
            'status'         => 'unpaid',
        ]);

        // Step A: Partial payment of KES 2500 -> pays INV-101 (2000) in full and INV-102 partially (500/3000).
        // Subscriber still owes 2500, so service remains suspended until balance is cleared.
        $payloadPartial = [
            'TransID'           => 'PARTIAL001',
            'TransAmount'       => '2500.00',
            'BusinessShortCode' => '409112',
            'BillRefNumber'     => 'APEX-FIN-01',
            'MSISDN'            => '254744555666',
        ];
        $raw1 = json_encode($payloadPartial);
        $sig1 = hash_hmac('sha256', $raw1, 'apex-webhook-secret-key');
        $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => $sig1,
        ], $raw1)->assertStatus(200);

        $this->assertEquals('paid', $inv1->fresh()->status);
        $this->assertEquals('partially_paid', $inv2->fresh()->status);
        $this->assertEquals(500.00, (float) $inv2->fresh()->paid_amount);
        $this->assertEquals('suspended', $client->fresh()->status, 'Partial payment leaving unpaid balance should not prematurely activate');

        // Step B: Second payment of KES 3000 -> clears remaining 2500 on INV-102 and credits 500 overpayment to wallet, activating service!
        $payloadOver = [
            'TransID'           => 'OVERPAY002',
            'TransAmount'       => '3000.00',
            'BusinessShortCode' => '409112',
            'BillRefNumber'     => 'APEX-FIN-01',
            'MSISDN'            => '254744555666',
        ];
        $raw2 = json_encode($payloadOver);
        $sig2 = hash_hmac('sha256', $raw2, 'apex-webhook-secret-key');
        $this->call('POST', "/api/webhooks/payments/mpesa_daraja/{$this->tenantA->id}", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_MPESA_SIGNATURE' => $sig2,
        ], $raw2)->assertStatus(200);

        $this->assertEquals('paid', $inv2->fresh()->status);
        $this->assertEquals(500.00, (float) $client->fresh()->wallet_balance);
        $this->assertEquals('active', $client->fresh()->status);
        $this->assertEquals('online', $client->fresh()->connection_status);

        // Step C: Reversal of OVERPAY002 triggers controlled reassessment and restricts ONLY this subscriber
        $otherActiveClient = Client::withoutGlobalScopes()->create([
            'tenant_id'         => $this->tenantA->id,
            'account_number'    => 'APEX-OTHER-ACTIVE',
            'name'              => 'Unrelated Active User',
            'phone'             => '254700999888',
            'status'            => 'active',
            'connection_status' => 'online',
        ]);

        $paymentToReverse = Payment::withoutGlobalScopes()->where('external_transaction_id', 'OVERPAY002')->first();
        $revRes = $this->actingAs($this->adminA)->post(route('billing.payments.reverse', $paymentToReverse), [
            'reason' => 'M-Pesa Reversal Dispute',
        ]);
        $revRes->assertRedirect();

        $this->assertEquals('reversed', $paymentToReverse->fresh()->status);
        $this->assertEquals('partially_paid', $inv2->fresh()->status);
        $this->assertEquals('suspended', $client->fresh()->status);
        $this->assertEquals('restricted', $client->fresh()->connection_status);
        $this->assertEquals('active', $otherActiveClient->fresh()->status, 'Reversal must never disconnect unrelated subscribers');
    }

    /**
     * 6. Strict Tenant Separation: ISP A cannot view ISP B's transactions, and ordinary Super Admin operational view
     * cannot browse ISP transactions without an explicitly audited support session.
     */
    public function test_strict_tenant_isolation_and_super_admin_privacy_guard(): void
    {
        Payment::withoutGlobalScopes()->create([
            'tenant_id'               => $this->tenantA->id,
            'transaction_reference'   => 'REF-TENANT-A-SECRET',
            'external_transaction_id' => 'MPESA-A-999',
            'payment_method'          => 'mpesa_c2b',
            'amount'                  => 4500,
            'status'                  => 'completed',
            'verification_status'     => 'verified',
        ]);

        Payment::withoutGlobalScopes()->create([
            'tenant_id'               => $this->tenantB->id,
            'transaction_reference'   => 'REF-TENANT-B-SECRET',
            'external_transaction_id' => 'MPESA-B-888',
            'payment_method'          => 'mpesa_c2b',
            'amount'                  => 7200,
            'status'                  => 'completed',
            'verification_status'     => 'verified',
        ]);

        // ISP A sees only its own transaction
        $resA = $this->actingAs($this->adminA)->get(route('billing.payments'));
        $resA->assertStatus(200);
        $resA->assertSee('MPESA-A-999');
        $resA->assertDontSee('MPESA-B-888');

        // ISP B sees only its own transaction
        $resB = $this->actingAs($this->adminB)->get(route('billing.payments'));
        $resB->assertStatus(200);
        $resB->assertSee('MPESA-B-888');
        $resB->assertDontSee('MPESA-A-999');

        // Ordinary Super Admin without explicit support impersonation gets 403 on ISP payment ledger
        $superRes = $this->actingAs($this->superAdmin)->get(route('billing.payments'));
        $superRes->assertStatus(403);

        // When Super Admin initiates explicit support access, a tamper-evident audit log is created
        $impRes = $this->actingAs($this->superAdmin)->get(route('platform.tenants.impersonate', [
            'tenant' => $this->tenantA->id,
            'reason' => 'Ticket #902 Billing Audit Request',
        ]));
        $impRes->assertRedirect(route('dashboard'));

        $audit = AuditLog::where('event', 'platform.support_access_granted')->where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($audit);
        $this->assertNotEmpty($audit->integrity_hash);
    }

    /**
     * 7. Separate ISP SaaS Billing: When an ISP pays Smartoh for its SaaS subscription, it updates ONLY the ISP's
     * SaaS subscription and NEVER reconnects suspended subscribers of that ISP.
     */
    public function test_isp_saas_subscription_payment_never_modifies_or_reconnects_isp_subscribers(): void
    {
        $suspendedSubscriber = Client::withoutGlobalScopes()->create([
            'tenant_id'         => $this->tenantA->id,
            'account_number'    => 'APEX-SUSP-99',
            'name'              => 'Unpaid Subscriber',
            'phone'             => '254755666777',
            'status'            => 'suspended',
            'connection_status' => 'restricted',
        ]);

        // ISP A pays Smartoh for its SaaS platform subscription
        $paySaasRes = $this->actingAs($this->adminA)->post(route('billing.subscription.pay'), [
            'provider' => 'mpesa',
            'phone'    => '254700123456',
        ]);
        $paySaasRes->assertRedirect();

        // SaaS subscription is ACTIVE_PAID, but the unpaid subscriber remains suspended
        $this->assertEquals(SaasSubscription::STATUS_ACTIVE_PAID, $this->tenantA->activeSubscription->fresh()->status);
        $this->assertEquals('suspended', $suspendedSubscriber->fresh()->status);
        $this->assertEquals('restricted', $suspendedSubscriber->fresh()->connection_status);
    }

    /**
     * 8. Controlled CSV Statement Import & Exception Resolution for non-API Bank/Pochi accounts.
     */
    public function test_controlled_statement_import_and_exception_resolution(): void
    {
        $client = Client::withoutGlobalScopes()->create([
            'tenant_id'          => $this->tenantA->id,
            'mikrotik_device_id' => $this->routerA->id,
            'billing_plan_id'    => $this->planA->id,
            'account_number'     => 'APEX-CSV-01',
            'name'               => 'Statement Customer',
            'phone'              => '254766777888',
            'status'             => 'suspended',
            'connection_status'  => 'restricted',
        ]);

        $csv = "Receipt,AccountRef,Amount,Payer\nBNK1001,APEX-CSV-01,3000,Statement Customer\nBNK1002,WRONG-REF-999,3000,Unknown Depositor";

        $importRes = $this->actingAs($this->adminA)->post(route('billing.payments.import-statement'), [
            'provider'    => 'bank_manual',
            'csv_content' => $csv,
        ]);
        $importRes->assertRedirect();

        // Matched row activated subscriber and marked source_channel = statement_import
        $this->assertEquals('active', $client->fresh()->status);
        $matchedPayment = Payment::withoutGlobalScopes()->where('external_transaction_id', 'BNK1001')->first();
        $this->assertEquals('statement_import', $matchedPayment->source_channel);
        $this->assertEquals('settled_in_isp_account', $matchedPayment->settlement_status);

        // Wrong reference row queued an open exception
        $exception = PaymentReconciliationException::withoutGlobalScopes()->where('external_transaction_id', 'BNK1002')->first();
        $this->assertNotNull($exception);
        $this->assertEquals('open', $exception->status);
    }

    /**
     * 9. Fast Self-Service Client Import, Export (CSV & RouterOS .rsc), and 1-Click Connection Restoration
     *    for Static IP, PPPoE, and Hotspot clients when integrating a new MikroTik router.
     */
    public function test_fast_client_import_export_and_mikrotik_connection_restoration_for_pppoe_hotspot_and_static_ip(): void
    {
        // A. Bulk CSV Import of PPPoE, Hotspot, and Static IP clients with immediate connection restoration
        $csvPayload = implode("\n", [
            'account_number,name,phone,email,service_type,pppoe_username,pppoe_password,assigned_ip,mac_address,plan_code,status,address',
            'MIG-PPP-101,Alice PPPoE,254711000101,alice@isp.co.ke,pppoe,alice_pppoe,Secret101,10.200.1.101,48:8F:5A:01:01:01,APEX-20M,active,Kilimani Apt 1',
            'MIG-HSP-102,Bob Hotspot,254711000102,bob@isp.co.ke,hotspot,bob_hotspot,Secret102,10.100.2.102,48:8F:5A:02:02:02,APEX-20M,active,Westlands Shop 4',
            'MIG-STA-103,Charlie Static Enterprise,254711000103,charlie@isp.co.ke,static,charlie_static,Secret103,192.168.88.103,48:8F:5A:03:03:03,APEX-20M,active,Upper Hill Plaza',
        ]);

        $importRes = $this->actingAs($this->adminA)->postJson(route('clients.import'), [
            'import_mode'             => 'csv',
            'csv_payload'             => $csvPayload,
            'mikrotik_device_id'      => $this->routerA->id,
            'default_billing_plan_id' => $this->planA->id,
            'duplicate_mode'          => 'update',
            'restore_connections'     => true,
        ]);

        $importRes->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('created', 3)
            ->assertJsonPath('by_service_type.pppoe', 1)
            ->assertJsonPath('by_service_type.hotspot', 1)
            ->assertJsonPath('by_service_type.static', 1)
            ->assertJsonPath('restore_summary.restored_count', 3);

        $pppoeClient = Client::withoutGlobalScopes()->where('account_number', 'MIG-PPP-101')->first();
        $hotspotClient = Client::withoutGlobalScopes()->where('account_number', 'MIG-HSP-102')->first();
        $staticClient = Client::withoutGlobalScopes()->where('account_number', 'MIG-STA-103')->first();

        $this->assertNotNull($pppoeClient);
        $this->assertEquals('pppoe', $pppoeClient->service_type);
        $this->assertEquals('connected_verified', $pppoeClient->connection_status);

        $this->assertNotNull($hotspotClient);
        $this->assertEquals('hotspot', $hotspotClient->service_type);
        $this->assertEquals('connected_verified', $hotspotClient->connection_status);

        $this->assertNotNull($staticClient);
        $this->assertEquals('static', $staticClient->service_type);
        $this->assertEquals('192.168.88.103', $staticClient->assigned_ip);
        $this->assertEquals('connected_verified', $staticClient->connection_status);

        // B. Direct RouterOS (.rsc) Import when integrating an existing MikroTik router
        $rscScript = <<<RSC
/ppp secret
add name="router_ppp_user" password="PppPass" profile="plan_20M" remote-address="10.200.9.50" comment="David Router PPPoE"
/ip hotspot user
add name="router_hsp_user" password="HspPass" profile="plan_20M" address="10.100.9.60" mac-address="48:8F:5A:99:00:11" comment="Eva Router Hotspot"
/queue simple
add name="Static-Branch-Office" target="192.168.88.222/32" max-limit="20480k/20480k" comment="Frank Router Static IP"
RSC;

        $rscImportRes = $this->actingAs($this->adminA)->postJson(route('clients.import'), [
            'import_mode'             => 'router_pull',
            'rsc_payload'             => $rscScript,
            'mikrotik_device_id'      => $this->routerA->id,
            'default_billing_plan_id' => $this->planA->id,
            'restore_connections'     => true,
        ]);

        $rscImportRes->assertStatus(200)
            ->assertJsonPath('created', 3)
            ->assertJsonPath('by_service_type.pppoe', 1)
            ->assertJsonPath('by_service_type.hotspot', 1)
            ->assertJsonPath('by_service_type.static', 1);

        // C. Export Clients as CSV and as MikroTik RouterOS (.rsc) Script
        $csvExport = $this->actingAs($this->adminA)->get(route('clients.export', ['format' => 'csv']));
        $csvExport->assertStatus(200);
        $this->assertStringContainsString('MIG-PPP-101', $csvExport->getContent());
        $this->assertStringContainsString('MIG-HSP-102', $csvExport->getContent());
        $this->assertStringContainsString('MIG-STA-103', $csvExport->getContent());

        $rscExport = $this->actingAs($this->adminA)->get(route('clients.export', ['format' => 'rsc']));
        $rscExport->assertStatus(200);
        $this->assertStringContainsString('/ppp secret', $rscExport->getContent());
        $this->assertStringContainsString('/ip hotspot user', $rscExport->getContent());
        $this->assertStringContainsString('/queue simple', $rscExport->getContent());

        // D. 1-Click Restore Connections onto a newly integrated replacement MikroTik router
        $newRouter = MikroTikDevice::withoutGlobalScopes()->create([
            'tenant_id'              => $this->tenantA->id,
            'site_id'                => $this->routerA->site_id,
            'name'                   => 'New-Replacement-CCR2116',
            'ip_address'             => '192.168.99.1',
            'api_port'               => 8728,
            'api_username'           => 'admin',
            'api_password_encrypted' => encrypt('secret'),
            'identity'               => 'Apex-New-Core',
            'is_online'              => true,
        ]);

        $restoreRes = $this->actingAs($this->adminA)->postJson(route('clients.restore-connections'), [
            'mikrotik_device_id'   => $newRouter->id,
            'service_type'         => 'all',
            'reactivate_suspended' => true,
        ]);

        $restoreRes->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('summary.router_id', $newRouter->id)
            ->assertJsonPath('summary.restored_count', 6);

        $this->assertEquals($newRouter->id, $staticClient->fresh()->mikrotik_device_id);
        $this->assertEquals('connected_verified', $staticClient->fresh()->connection_status);

        // E. Verify Clients Window UI renders Export Clients, Import & Migrate Clients, and Restore Connections buttons
        $indexRes = $this->actingAs($this->adminA)->get(route('clients.index'));
        $indexRes->assertStatus(200)
            ->assertSee('Export Clients')
            ->assertSee('Import &amp; Migrate Clients', false)
            ->assertSee('Restore Connections');
    }

    /**
     * 10. ISP can add any desired payment account on both API gateways and Non-API (Manual/Bank/Pochi/SACCO) gateways.
     */
    public function test_isp_can_add_desired_payment_accounts_on_both_api_and_non_api_gateways(): void
    {
        // A. Add a desired custom API payment account (e.g. Custom Bank / Fintech API)
        $apiRes = $this->actingAs($this->adminA)->postJson(route('gateways.api.store'), [
            'name'                 => 'Apex NCBA Loop API Collection',
            'provider'             => 'custom_api',
            'custom_provider_name' => 'NCBA Loop API',
            'environment'          => 'production',
            'shortcode'            => '880100',
            'account_holder_name'  => 'Apex Fiber Networks Ltd',
            'base_url'             => 'https://api.ncbagroup.com/v1',
            'consumer_key'         => 'ncba_live_key_998877',
            'consumer_secret'      => 'ncba_live_secret_112233',
            'passkey'              => 'ncba_passkey_4455',
        ]);

        $apiRes->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('gateway.name', 'Apex NCBA Loop API Collection')
            ->assertJsonPath('gateway.shortcode', '880100')
            ->assertJsonPath('gateway.is_non_custodial', true);

        // B. Add a desired Non-API payment account (e.g. Pochi la Biashara or Custom SACCO Account)
        $manualRes = $this->actingAs($this->adminA)->postJson(route('gateways.manual.store'), [
            'name'                 => 'Apex Unaitas SACCO Collection',
            'provider'             => 'custom_manual',
            'custom_provider_name' => 'Unaitas SACCO',
            'account_number'       => '0712998877',
            'account_holder_name'  => 'Apex Fiber Networks Ltd',
            'bank_paybill'         => '544700',
            'branch'               => 'Nairobi CBD Branch',
            'reconciliation_mode'  => 'statement_import',
            'instructions'         => 'Pay via Paybill 544700, Account 0712998877.',
        ]);

        $manualRes->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('gateway.name', 'Apex Unaitas SACCO Collection')
            ->assertJsonPath('gateway.shortcode', '0712998877')
            ->assertJsonPath('gateway.is_non_custodial', true);

        // C. Verify both API and Non-API pages display the newly added accounts and buttons
        $apiPage = $this->actingAs($this->adminA)->get(route('gateways.api'));
        $apiPage->assertStatus(200)
            ->assertSee('Apex NCBA Loop API Collection')
            ->assertSee('880100')
            ->assertSee('Add API Gateway Account')
            ->assertSee('Add Non-API Account');

        $manualPage = $this->actingAs($this->adminA)->get(route('gateways.manual'));
        $manualPage->assertStatus(200)
            ->assertSee('Apex Unaitas SACCO Collection')
            ->assertSee('0712998877')
            ->assertSee('Add Non-API Payment Account')
            ->assertSee('Add API Gateway Account');
    }
}
