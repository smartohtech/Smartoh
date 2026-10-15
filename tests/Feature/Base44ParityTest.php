<?php

namespace Tests\Feature;

use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class Base44ParityTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        return User::where('role', 'super_admin')->first() ?? User::first();
    }

    public function test_radius_and_aaa_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/radius');
        $response->assertStatus(200);
        $response->assertSee('RADIUS');

        // Test Base44 direct alias
        $aliasResponse = $this->actingAs($user)->get('/Radius');
        $aliasResponse->assertStatus(200);
    }

    public function test_api_gateways_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/gateways/api');
        $response->assertStatus(200);
        $response->assertSee('Daraja');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/APIGateways');
        $aliasResponse->assertStatus(200);
    }

    public function test_manual_payment_gateways_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/gateways/manual');
        $response->assertStatus(200);
        $response->assertSee('Payment Gateways');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/PaymentGateways');
        $aliasResponse->assertStatus(200);
    }

    public function test_whatsapp_messaging_page_renders_and_sends(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/communication/whatsapp');
        $response->assertStatus(200);
        $response->assertSee('WhatsApp');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/WhatsApp');
        $aliasResponse->assertStatus(200);

        // Send WhatsApp message
        $sendResponse = $this->actingAs($user)->post('/communication/whatsapp/send', [
            'recipient' => '+254712345678',
            'message' => 'Your subscription has been renewed successfully.',
            'category' => 'billing',
        ]);
        $sendResponse->assertSessionHas('success');
    }

    public function test_automated_reminders_page_renders_and_executes(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/communication/reminders');
        $response->assertStatus(200);
        $response->assertSee('Automated Payment Reminders');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/Reminders');
        $aliasResponse->assertStatus(200);

        // Run reminders job
        $runResponse = $this->actingAs($user)->post('/communication/reminders/run');
        $runResponse->assertSessionHas('success');
    }

    public function test_sms_gateways_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/communication/sms');
        $response->assertStatus(200);
        $response->assertSee('SMS');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/SMSGateways');
        $aliasResponse->assertStatus(200);
    }

    public function test_predictive_maintenance_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/operations/predictive');
        $response->assertStatus(200);
        $response->assertSee('Predictive Maintenance');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/PredictiveMaintenance');
        $aliasResponse->assertStatus(200);
    }

    public function test_routing_config_page_renders_and_stores(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/operations/routing');
        $response->assertStatus(200);
        $response->assertSee('Routing');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/RoutingConfig');
        $aliasResponse->assertStatus(200);

        // Deploy OSPF
        $ospfResponse = $this->actingAs($user)->post('/operations/routing/ospf', [
            'router_id' => '10.0.0.1',
            'instance_name' => 'ospf-test',
        ]);
        $ospfResponse->assertSessionHas('success');
    }

    public function test_router_detection_and_quarantine_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/operations/detection');
        $response->assertStatus(200);
        $response->assertSee('Rogue Router');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/RouterDetection');
        $aliasResponse->assertStatus(200);

        // Quarantine POST
        $quarantineResponse = $this->actingAs($user)->post('/operations/detection/action', [
            'mac_address' => 'D4:CA:6D:88:99:AA',
            'action' => 'block',
        ]);
        $quarantineResponse->assertSessionHas('success');
    }

    public function test_crm_pipeline_renders_and_stores_lead(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/crm');
        $response->assertStatus(200);
        $response->assertSee('CRM');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/CRM');
        $aliasResponse->assertStatus(200);

        // Store Lead
        $leadResponse = $this->actingAs($user)->post('/crm/lead', [
            'name' => 'Acme Corporation',
            'email' => 'tech@acmecorp.com',
            'phone' => '+254711223344',
            'location' => 'Westlands Towers, 4th Floor',
            'estimated_mrc' => 15000,
            'stage' => 'qualified',
        ]);
        $leadResponse->assertSessionHas('success');
    }

    public function test_inventory_stock_renders_and_stores(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/inventory');
        $response->assertStatus(200);
        $response->assertSee('Inventory');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/Inventory');
        $aliasResponse->assertStatus(200);

        // Store Stock Item
        $stockResponse = $this->actingAs($user)->post('/inventory/stock', [
            'name' => 'MikroTik hEX S (RB760iGS)',
            'sku' => 'RB760IGS-' . rand(100, 999),
            'category' => 'Router',
            'quantity' => 12,
            'unit_cost' => 8500,
            'location' => 'Central Warehouse Shelf B2',
        ]);
        $stockResponse->assertSessionHas('success');
    }

    public function test_advanced_reports_page_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/AdvancedReports');
        $aliasResponse->assertStatus(200);
    }

    public function test_isp_approvals_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/isp-approvals');
        $response->assertStatus(200);
        $response->assertSee('Onboarding Approvals');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/ISPApprovals');
        $aliasResponse->assertStatus(200);
    }

    public function test_user_approvals_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/user-approvals');
        $response->assertStatus(200);
        $response->assertSee('Role Approvals');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/UserApprovals');
        $aliasResponse->assertStatus(200);
    }

    public function test_tenant_branding_settings_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/tenant-branding');
        $response->assertStatus(200);
        $response->assertSee('Organization');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/TenantBrandingSettings');
        $aliasResponse->assertStatus(200);
    }

    public function test_system_settings_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/system-settings');
        $response->assertStatus(200);
        $response->assertSee('System Settings');
    }

    public function test_audit_logs_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
    }

    public function test_proration_calculator_renders_and_calculates(): void
    {
        $user = $this->getSuperAdmin();
        $client = Client::first();
        $plan = BillingPlan::latest()->first();

        $response = $this->actingAs($user)->get('/admin/proration');
        $response->assertStatus(200);
        $response->assertSee('Proration Calculator');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/Proration');
        $aliasResponse->assertStatus(200);

        if ($client && $plan) {
            $calcResponse = $this->actingAs($user)->post('/admin/proration/calculate', [
                'client_id' => $client->id,
                'new_plan_id' => $plan->id,
            ]);
            $calcResponse->assertSessionHas('success');
            $calcResponse->assertSessionHas('proration_result');
        }
    }

    public function test_system_plan_quotas_renders(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/admin/system-plan');
        $response->assertStatus(200);
        $response->assertSee('Subscription');

        // Base44 alias
        $aliasResponse = $this->actingAs($user)->get('/SystemPlan');
        $aliasResponse->assertStatus(200);
    }
}
