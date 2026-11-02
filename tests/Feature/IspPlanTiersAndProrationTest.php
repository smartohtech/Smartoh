<?php

namespace Tests\Feature;

use App\Models\SaasPlan;
use App\Models\SaasPlanChangeRequest;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SaaS\SubscriptionLifecycleService;
use Database\Seeders\SaasPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IspPlanTiersAndProrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SaasPlanSeeder::class);
    }

    public function test_isp_plan_tiers_have_distinct_benefit_levels_and_quotas(): void
    {
        $starter = SaasPlan::where('slug', 'starter')->with('currentVersion')->firstOrFail();
        $pro = SaasPlan::where('slug', 'pro')->with('currentVersion')->firstOrFail();
        $enterprise = SaasPlan::where('slug', 'enterprise')->with('currentVersion')->firstOrFail();

        $this->assertEquals(1, $starter->currentVersion->features['tier_level']);
        $this->assertEquals(2, $pro->currentVersion->features['tier_level']);
        $this->assertEquals(3, $enterprise->currentVersion->features['tier_level']);

        // Quotas scale across tiers
        $this->assertLessThan($pro->currentVersion->limits['max_subscribers'], $starter->currentVersion->limits['max_subscribers']);
        $this->assertLessThan($enterprise->currentVersion->limits['max_subscribers'], $pro->currentVersion->limits['max_subscribers']);

        // Distinct feature flags across tiers
        $this->assertFalse($starter->currentVersion->features['has_api_access']);
        $this->assertTrue($pro->currentVersion->features['has_api_access']);
        $this->assertFalse($pro->currentVersion->features['has_bgp_ospf']);
        $this->assertTrue($enterprise->currentVersion->features['has_bgp_ospf']);

        // Included benefits and locked benefits are populated
        $this->assertNotEmpty($starter->includedBenefits());
        $this->assertNotEmpty($starter->lockedBenefits());
        $this->assertNotEmpty($pro->includedBenefits());
        $this->assertNotEmpty($enterprise->includedBenefits());
    }

    public function test_proration_calculates_exact_amount_to_add_when_upgrading_during_existing_tier(): void
    {
        $starter = SaasPlan::where('slug', 'starter')->firstOrFail(); // KES 5,000 / 30 days
        $pro = SaasPlan::where('slug', 'pro')->firstOrFail();         // KES 14,000 / 30 days

        $tenant = Tenant::create([
            'name' => 'Kili Wireless ISP',
            'subdomain' => 'kiliwireless',
            'subscription_tier' => 'starter',
            'status' => 'active',
        ]);

        SaasSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $starter->id,
            'saas_plan_version_id' => $starter->currentVersion->id,
            'status' => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval' => 'monthly',
            'amount' => 5000.00,
            'currency' => 'KES',
            'current_period_start' => now()->subDays(15),
            'current_period_end' => now()->addDays(15),
        ]);

        $lifecycle = app(SubscriptionLifecycleService::class);

        // 15 days remaining out of 30 days:
        // Unused credit on Starter (5000 * 15/30) = KES 2,500.00
        // Prorated charge on Pro (14000 * 15/30) = KES 7,000.00
        // Net amount to add = 7,000 - 2,500 = KES 4,500.00
        $calc = $lifecycle->calculateTierProration($tenant, $pro, 'monthly', 15, 30);

        $this->assertTrue($calc['is_upgrade']);
        $this->assertEquals('upgrade', $calc['change_type']);
        $this->assertEquals(2500.00, $calc['unused_credit']);
        $this->assertEquals(7000.00, $calc['prorated_new_charge']);
        $this->assertEquals(4500.00, $calc['prorated_amount_to_add']);
        $this->assertEquals(0.00, $calc['credit_carryover']);
    }

    public function test_isp_can_request_upgrade_and_pay_prorated_amount_to_activate_higher_tier(): void
    {
        $starter = SaasPlan::where('slug', 'starter')->firstOrFail();
        $pro = SaasPlan::where('slug', 'pro')->firstOrFail();

        $tenant = Tenant::create([
            'name' => 'Savannah Fiber Ltd',
            'subdomain' => 'savannah',
            'subscription_tier' => 'starter',
            'status' => 'active',
            'max_routers' => 3,
            'max_clients' => 300,
        ]);

        $ispAdmin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Savannah Admin',
            'email' => 'admin@savannah.co.ke',
            'password' => bcrypt('password123'),
            'role' => 'isp_admin',
            'is_approved' => true,
        ]);

        SaasSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $starter->id,
            'saas_plan_version_id' => $starter->currentVersion->id,
            'status' => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval' => 'monthly',
            'amount' => 5000.00,
            'currency' => 'KES',
            'current_period_start' => now()->subDays(12),
            'current_period_end' => now()->addDays(18),
        ]);

        // 1. ISP views the subscription & plan preferences page
        $this->actingAs($ispAdmin)
            ->get(route('billing.subscription'))
            ->assertOk()
            ->assertSee('Starter ISP')
            ->assertSee('Pro Growth ISP')
            ->assertSee('Enterprise Tier-2 Core')
            ->assertSee('Mid-Cycle ISP Tier Upgrade / Downgrade');

        // 2. ISP submits an upgrade request for 18 remaining days out of 30:
        // Unused credit = 5000 * (18/30) = 3,000
        // New prorated charge = 14000 * (18/30) = 8,400
        // Amount to add = 8,400 - 3,000 = 5,400
        $this->actingAs($ispAdmin)
            ->post(route('billing.subscription.change-tier'), [
                'target_plan_id' => $pro->id,
                'billing_interval' => 'monthly',
                'total_cycle_days' => 30,
                'remaining_days' => 18,
                'effective_mode' => 'immediate',
                'payment_method' => 'mpesa',
                'pay_now' => false,
                'reason_notes' => 'Scaling to 1,500 clients',
            ])
            ->assertRedirect();

        $changeRequest = SaasPlanChangeRequest::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertEquals('upgrade', $changeRequest->change_type);
        $this->assertEquals(3000.00, (float) $changeRequest->unused_credit);
        $this->assertEquals(8400.00, (float) $changeRequest->prorated_new_charge);
        $this->assertEquals(5400.00, (float) $changeRequest->prorated_amount_to_add);
        $this->assertEquals(SaasPlanChangeRequest::STATUS_PENDING_PAYMENT, $changeRequest->status);
        $this->assertNotNull($changeRequest->saas_invoice_id);

        // 3. ISP pays the prorated top-up amount (KES 5,400) to complete the upgrade
        $this->actingAs($ispAdmin)
            ->post(route('billing.subscription.change-requests.pay', $changeRequest), [
                'provider' => 'mpesa',
            ])
            ->assertRedirect();

        $changeRequest->refresh();
        $tenant->refresh();
        $subscription = $tenant->activeSubscription()->first();

        $this->assertEquals(SaasPlanChangeRequest::STATUS_COMPLETED, $changeRequest->status);
        $this->assertEquals('pro', $tenant->subscription_tier);
        $this->assertEquals(1500, $tenant->max_clients);
        $this->assertEquals(10, $tenant->max_routers);
        $this->assertEquals($pro->id, $subscription->saas_plan_id);
        $this->assertEquals(14000.00, (float) $subscription->amount);
    }

    public function test_isp_can_request_downgrade_with_credit_carryover_and_super_admin_can_approve(): void
    {
        $pro = SaasPlan::where('slug', 'pro')->firstOrFail();
        $starter = SaasPlan::where('slug', 'starter')->firstOrFail();

        $tenant = Tenant::create([
            'name' => 'Coastline Net',
            'subdomain' => 'coastline',
            'subscription_tier' => 'pro',
            'status' => 'active',
        ]);

        $ispAdmin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Coastline Admin',
            'email' => 'admin@coastline.co.ke',
            'password' => bcrypt('password123'),
            'role' => 'isp_admin',
            'is_approved' => true,
        ]);

        $superAdmin = User::create([
            'name' => 'Platform Owner',
            'email' => 'super@smartoh.net',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'is_approved' => true,
        ]);

        SaasSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $pro->id,
            'saas_plan_version_id' => $pro->currentVersion->id,
            'status' => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval' => 'monthly',
            'amount' => 14000.00,
            'currency' => 'KES',
            'current_period_start' => now()->subDays(20),
            'current_period_end' => now()->addDays(10),
        ]);

        // 10 days remaining out of 30:
        // Unused Pro credit = 14000 * (10/30) = 4666.67
        // Starter charge = 5000 * (10/30) = 1666.67
        // Credit carryover = 3000.00
        $this->actingAs($ispAdmin)
            ->post(route('admin.system-plan.change-tier'), [
                'target_plan_id' => $starter->id,
                'billing_interval' => 'monthly',
                'total_cycle_days' => 30,
                'remaining_days' => 10,
                'effective_mode' => 'end_of_cycle',
                'reason_notes' => 'Seasonal adjustment',
            ])
            ->assertRedirect();

        $changeRequest = SaasPlanChangeRequest::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertEquals('downgrade', $changeRequest->change_type);
        $this->assertEquals(0.00, (float) $changeRequest->prorated_amount_to_add);
        $this->assertEquals(3000.00, (float) $changeRequest->credit_carryover);
        $this->assertEquals(SaasPlanChangeRequest::STATUS_PENDING_REVIEW, $changeRequest->status);

        // Super Admin approves the downgrade request
        $this->actingAs($superAdmin)
            ->post(route('platform.subscriptions.change-requests.approve', $changeRequest), [
                'admin_notes' => 'Approved downgrade with KES 3,000 credit carryover.',
            ])
            ->assertRedirect();

        $changeRequest->refresh();
        $tenant->refresh();

        $this->assertEquals(SaasPlanChangeRequest::STATUS_COMPLETED, $changeRequest->status);
        $this->assertEquals('starter', $tenant->subscription_tier);
        $this->assertEquals(3000.00, $tenant->settings['saas_credit_balance'] ?? 0);
    }
}
