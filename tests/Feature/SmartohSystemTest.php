<?php

namespace Tests\Feature;

use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class SmartohSystemTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        return User::where('role', 'super_admin')->first() 
            ?? User::first();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Smartoh ISP Suite');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Operations Command');
        $response->assertSee('Subscribers');
    }

    public function test_subscribers_management_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);
        $response->assertSee('Register New Subscriber');
    }

    public function test_client_details_page_renders(): void
    {
        $user = $this->getSuperAdmin();
        $client = Client::first();

        if ($client) {
            $response = $this->actingAs($user)->get('/clients/' . $client->id);
            $response->assertStatus(200);
            $response->assertSee($client->first_name);
        } else {
            $this->markTestSkipped('No clients found in test database.');
        }
    }

    public function test_billing_plans_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/billing/plans');
        $response->assertStatus(200);
        $response->assertSee('Create New Plan');
    }

    public function test_invoices_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/billing/invoices');
        $response->assertStatus(200);
        $response->assertSee('Total Billed');
    }

    public function test_payments_and_mpesa_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/billing/payments');
        $response->assertStatus(200);
        $response->assertSee('M-Pesa Gateway');
    }

    public function test_mpesa_stk_push_flow(): void
    {
        $user = $this->getSuperAdmin();
        $client = Client::first();

        if ($client) {
            $response = $this->actingAs($user)->post('/billing/payments/stk-push', [
                'client_id' => $client->id,
                'amount' => 1500,
                'phone' => '254712345678',
            ]);

            $response->assertSessionHas('success');
        } else {
            $this->markTestSkipped('No clients found.');
        }
    }

    public function test_vouchers_generation_and_listing(): void
    {
        $user = $this->getSuperAdmin();
        $plan = BillingPlan::first();

        $response = $this->actingAs($user)->get('/billing/vouchers');
        $response->assertStatus(200);
        $response->assertSee('Batch Generate Vouchers');

        if ($plan) {
            $genResponse = $this->actingAs($user)->post('/billing/vouchers/generate', [
                'billing_plan_id' => $plan->id,
                'type' => 'hotspot',
                'quantity' => 3,
                'duration_hours' => 24,
            ]);
            $genResponse->assertSessionHas('success');
        }
    }

    public function test_network_mikrotik_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/network/mikrotik');
        $response->assertStatus(200);
        $response->assertSee('RouterOS v7 Core Engine');
    }

    public function test_cpe_and_olt_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/cpe');
        $response->assertStatus(200);
        $response->assertSee('Provision WireGuard Peer');
    }

    public function test_noc_page_and_ai_copilot_query(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/noc');
        $response->assertStatus(200);
        $response->assertSee('Raise Network Incident');

        $aiResponse = $this->actingAs($user)->get('/noc/ask-ai?query=Are+there+any+optical+signal+degradations?');
        $aiResponse->assertStatus(200);
        $aiResponse->assertJsonStructure(['success', 'answer']);
    }

    public function test_gis_fiber_map_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/gis/map');
        $response->assertStatus(200);
        $response->assertSee('GIS Fiber Infrastructure');
    }

    public function test_gis_field_jobs_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/gis/jobs');
        $response->assertStatus(200);
        $response->assertSee('Dispatch Work Order');
    }

    public function test_finance_and_ledger_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/finance');
        $response->assertStatus(200);
        $response->assertSee('Add General Ledger Account');
    }

    public function test_wholesale_marketplace_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/wholesale');
        $response->assertStatus(200);
        $response->assertSee('Order Wholesale Capacity');
    }

    public function test_platform_tenants_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/tenants');
        $response->assertStatus(200);
        $response->assertSee('Multi-Tenant ISP Workspaces');
    }

    public function test_subscriber_portal_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Subscription Cycle');
        $response->assertSee('Top-Up Voucher');
    }
}
