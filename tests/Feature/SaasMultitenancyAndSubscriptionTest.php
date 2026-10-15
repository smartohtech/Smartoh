<?php

namespace Tests\Feature;

use App\Models\IspApplication;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SaasPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasMultitenancyAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $ispAdmin;
    protected Tenant $tenant;
    protected SaasPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // 0. Seed SaaS Plans
        $this->seed(SaasPlanSeeder::class);

        // 1. Ensure Super Admin exists
        $this->superAdmin = User::create([
            'name'        => 'Super Admin',
            'email'       => 'admin@smartoh.co.ke',
            'password'    => bcrypt('password'),
            'role'        => 'super_admin',
            'tenant_id'   => null,
            'is_approved' => true,
        ]);

        // 2. Fetch existing Plan
        $this->plan = SaasPlan::where('slug', 'pro')->first();

        // 3. Ensure Tenant 1 exists
        $this->tenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name'              => 'C-Tech ISP',
                'subdomain'         => 'ctech',
                'status'            => 'active',
                'subscription_tier' => 'enterprise',
            ]
        );

        // 4. Ensure ISP Admin exists
        $this->ispAdmin = User::firstOrCreate(
            ['email' => 'smartoh25@gmail.com'],
            [
                'name'        => 'John Kevin',
                'password'    => bcrypt('password'),
                'role'        => 'isp_admin',
                'tenant_id'   => $this->tenant->id,
                'is_approved' => true,
            ]
        );
    }

    /**
     * 1. Public marketing and pricing pages render properly.
     */
    public function test_public_pricing_and_features_pages_render_correctly(): void
    {
        $response = $this->get(route('pricing'));
        $response->assertStatus(200);
        $response->assertSee('Transparent Pricing');

        $featuresResponse = $this->get(route('features'));
        $featuresResponse->assertStatus(200);
        $featuresResponse->assertSee('Platform Architecture');
    }

    /**
     * 2. Registration collects data and creates PENDING_APPROVAL application without granting immediate tenant access.
     */
    public function test_isp_registration_creates_pending_application_without_tenant_privileges(): void
    {
        $uniqueSubdomain = 'testisp' . rand(1000, 9999);
        $uniqueEmail = "applicant_{$uniqueSubdomain}@testdomain.com";

        $response = $this->post(route('register.post'), [
            'company_name'          => 'FastNet Communications',
            'subdomain'             => $uniqueSubdomain,
            'contact_name'          => 'Alice Kiprono',
            'email'                 => $uniqueEmail,
            'phone'                 => '254711223344',
            'country'               => 'Kenya',
            'saas_plan_id'          => $this->plan->id,
            'billing_interval'      => 'monthly',
            'network_type'          => 'FTTH Fiber',
            'current_subscribers'   => 150,
            'password'              => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'terms'                 => '1',
        ]);

        $response->assertRedirect();

        // Verify application was recorded with pending_approval
        $application = IspApplication::where('email', $uniqueEmail)->first();
        $this->assertNotNull($application);
        $this->assertEquals('pending_approval', $application->status);
        $this->assertEquals($uniqueSubdomain, $application->subdomain);
        $this->assertNull($application->provisioned_tenant_id);

        // Verify no active tenant was provisioned yet
        $this->assertNull(Tenant::where('subdomain', $uniqueSubdomain)->first());
    }

    /**
     * 3. Super Admin Control Panel is strictly protected against unauthenticated and ISP admin access.
     */
    public function test_super_admin_route_is_forbidden_to_isp_admins_and_guests(): void
    {
        // Guests redirected to login
        $guestResponse = $this->get(route('platform.dashboard'));
        $guestResponse->assertRedirect(route('login'));

        // ISP Admin gets 403 Forbidden
        $ispResponse = $this->actingAs($this->ispAdmin)->get(route('platform.dashboard'));
        $ispResponse->assertStatus(403);

        // Super Admin gets 200 OK
        $adminResponse = $this->actingAs($this->superAdmin)->get(route('platform.dashboard'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Platform Executive Dashboard');
    }

    /**
     * 4. Super Admin approval idempotently provisions tenant and starts 7-day trial.
     */
    public function test_super_admin_can_approve_application_idempotently_starting_7_day_trial(): void
    {
        $uniqueSub = 'alpha' . rand(1000, 9999);
        $app = IspApplication::create([
            'application_number'   => 'APP-' . strtoupper($uniqueSub),
            'company_name'         => 'Alpha ISP Limited',
            'subdomain'            => $uniqueSub,
            'contact_name'         => 'Brian Omondi',
            'email'                => "brian_{$uniqueSub}@alphaisp.com",
            'phone'                => '254799887766',
            'country'              => 'Kenya',
            'saas_plan_id'         => $this->plan->id,
            'saas_plan_version_id' => $this->plan->currentVersion?->id,
            'status'               => 'pending_approval',
        ]);

        // First approval
        $approveResponse = $this->actingAs($this->superAdmin)->post(
            route('platform.applications.approve', $app),
            ['approval_notes' => 'Verified with Communications Authority of Kenya']
        );

        $approveResponse->assertRedirect();

        $app->refresh();
        $this->assertEquals('approved', $app->status);
        $this->assertNotNull($app->provisioned_tenant_id);

        $tenant = Tenant::find($app->provisioned_tenant_id);
        $this->assertNotNull($tenant);
        $this->assertEquals($uniqueSub, $tenant->subdomain);

        // Verify 7-day trial started
        $sub = SaasSubscription::where('tenant_id', $tenant->id)->latest()->first();
        $this->assertNotNull($sub);
        $this->assertEquals(SaasSubscription::STATUS_TRIAL_ACTIVE, $sub->status);
        $this->assertNotNull($sub->trial_started_at);
        $this->assertNotNull($sub->trial_ends_at);

        $daysRemaining = $sub->daysRemainingInTrial();
        $this->assertGreaterThanOrEqual(6, $daysRemaining);
        $this->assertLessThanOrEqual(7, $daysRemaining);

        // Test IDEMPOTENCY: calling approve again must not create duplicates
        $secondApprove = $this->actingAs($this->superAdmin)->post(
            route('platform.applications.approve', $app),
            ['approval_notes' => 'Duplicate attempt test']
        );

        $secondApprove->assertRedirect();
        $tenantCount = Tenant::where('subdomain', $uniqueSub)->count();
        $this->assertEquals(1, $tenantCount, 'Approval must be idempotent and not create duplicate tenants.');
    }

    /**
     * 5. EnforceSubscriptionStateMiddleware blocks operational modules when trial is expired.
     */
    public function test_subscription_lifecycle_enforcement_blocks_operational_routes_when_expired(): void
    {
        // Create an expired subscription for tenant
        $sub = SaasSubscription::where('tenant_id', $this->tenant->id)->latest()->first();
        if ($sub) {
            $sub->update(['status' => SaasSubscription::STATUS_TRIAL_EXPIRED]);
        } else {
            SaasSubscription::create([
                'tenant_id'            => $this->tenant->id,
                'saas_plan_id'         => $this->plan->id,
                'saas_plan_version_id' => $this->plan->currentVersion?->id,
                'status'               => SaasSubscription::STATUS_TRIAL_EXPIRED,
                'billing_interval'     => 'monthly',
                'amount'               => 14000,
                'currency'             => 'KES',
            ]);
        }

        // Accessing operational route (/dashboard) redirects to /billing/subscription
        $response = $this->actingAs($this->ispAdmin)->get(route('dashboard'));
        $response->assertRedirect(route('billing.subscription'));

        // Accessing subscription view itself is allowed
        $subResponse = $this->actingAs($this->ispAdmin)->get(route('billing.subscription'));
        $subResponse->assertStatus(200);
        $subResponse->assertSee('SaaS Platform Subscription');
    }

    /**
     * 6. Payment processing upgrades subscription to ACTIVE_PAID status.
     */
    public function test_payment_activates_subscription_to_active_paid(): void
    {
        // Create or find subscription in payment_pending status
        $sub = SaasSubscription::create([
            'tenant_id'            => $this->tenant->id,
            'saas_plan_id'         => $this->plan->id,
            'saas_plan_version_id' => $this->plan->currentVersion?->id,
            'status'               => SaasSubscription::STATUS_PAYMENT_PENDING,
            'billing_interval'     => 'monthly',
            'amount'               => 14000,
            'currency'             => 'KES',
            'current_period_start' => Carbon::now()->subMonth(),
            'current_period_end'   => Carbon::now()->subDay(),
        ]);

        $payResponse = $this->actingAs($this->ispAdmin)->post(route('billing.subscription.pay'), [
            'provider' => 'mpesa',
            'phone'    => '254700000000',
        ]);

        $payResponse->assertRedirect();

        $sub->refresh();
        $this->assertEquals(SaasSubscription::STATUS_ACTIVE_PAID, $sub->status);
        $this->assertNotNull($sub->current_period_end);
    }
}
