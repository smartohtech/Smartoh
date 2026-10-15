<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Device;
use App\Models\IspApplication;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use App\Models\TenantUsage;
use App\Models\TrialEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminAndExistingTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Safely migrates existing ISP (C-Tech) and seeds the primary Super Admin account.
     */
    public function run(): void
    {
        // 1. Create or ensure Super Administrator Account
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@smartoh.co.ke'],
            [
                'name'              => 'Smartoh Platform Super Admin',
                'password'          => Hash::make('SuperAdmin123!'),
                'role'              => 'super_admin',
                'tenant_id'         => null, // Platform-wide authority
                'is_approved'       => true,
                'phone'             => '254700000000',
                'email_verified_at' => Carbon::now(),
            ]
        );

        // Ensure role is explicitly super_admin
        if ($superAdmin->role !== 'super_admin') {
            $superAdmin->update(['role' => 'super_admin', 'tenant_id' => null, 'is_approved' => true]);
        }

        $this->command->info("Super Admin account verified: {$superAdmin->email} (Role: {$superAdmin->role})");

        // 2. Fetch Enterprise Plan for C-Tech assignment
        $enterprisePlan = SaasPlan::where('slug', 'enterprise')->with('currentVersion')->first()
            ?? SaasPlan::where('is_active', true)->with('currentVersion')->first();

        if (!$enterprisePlan) {
            $this->command->error("No SaaS Plan found. Please run SaasPlanSeeder first.");
            return;
        }

        // 3. Migrate Existing Tenant: C-Tech (Tenant ID: 1)
        $tenant = Tenant::find(1);
        if ($tenant) {
            // Activate tenant if it was pending
            if ($tenant->status === 'pending') {
                $tenant->update([
                    'status'            => 'active',
                    'subscription_tier' => 'enterprise',
                ]);
            }

            // A. Ensure Application Record exists without creating duplicate tenant
            $application = IspApplication::firstOrCreate(
                ['provisioned_tenant_id' => $tenant->id],
                [
                    'application_number'    => 'APP-CTECH-001',
                    'company_name'          => $tenant->name,
                    'subdomain'             => $tenant->subdomain ?? 'ctech',
                    'contact_name'          => 'John Kevin',
                    'email'                 => 'smartoh25@gmail.com',
                    'phone'                 => '254700000001',
                    'country'               => 'Kenya',
                    'saas_plan_id'          => $enterprisePlan->id,
                    'saas_plan_version_id'  => $enterprisePlan->currentVersion?->id,
                    'status'                => 'approved',
                    'reviewed_by_user_id'   => $superAdmin->id,
                    'reviewed_at'           => Carbon::now()->subDays(2),
                    'provisioned_at'        => Carbon::now()->subDays(2),
                ]
            );

            // B. Ensure Active SaaS Subscription exists
            $subscription = SaasSubscription::where('tenant_id', $tenant->id)->latest()->first();
            if (!$subscription) {
                $subscription = SaasSubscription::create([
                    'tenant_id'             => $tenant->id,
                    'saas_plan_id'          => $enterprisePlan->id,
                    'saas_plan_version_id'  => $enterprisePlan->currentVersion?->id,
                    'status'                => SaasSubscription::STATUS_ACTIVE_PAID,
                    'billing_interval'      => 'monthly',
                    'amount'                => $enterprisePlan->monthly_price,
                    'currency'              => $enterprisePlan->currency ?? 'KES',
                    'current_period_start'  => Carbon::now()->subDays(2),
                    'current_period_end'    => Carbon::now()->addDays(28),
                    'trial_started_at'      => Carbon::now()->subDays(2),
                    'trial_ends_at'         => Carbon::now()->addDays(5),
                ]);

                TrialEvent::create([
                    'tenant_id'       => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'event_type'      => 'started',
                    'recorded_at'     => Carbon::now()->subDays(2),
                    'notes'           => 'Initial 7-day trial granted upon platform onboarding.',
                ]);

                TrialEvent::create([
                    'tenant_id'       => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'event_type'      => 'converted',
                    'recorded_at'     => Carbon::now(),
                    'notes'           => 'Converted to Active Paid Enterprise tier.',
                ]);
            }

            // C. Sync Initial Tenant Usage Statistics
            $clientsCount = class_exists(Client::class) ? Client::where('tenant_id', $tenant->id)->count() : 0;
            $devicesCount = class_exists(Device::class) ? Device::where('tenant_id', $tenant->id)->count() : 0;
            $staffCount = User::where('tenant_id', $tenant->id)->count();

            TenantUsage::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'active_subscribers_count' => $clientsCount,
                    'managed_routers_count'    => $devicesCount,
                    'active_staff_count'       => $staffCount,
                    'last_calculated_at'       => Carbon::now(),
                ]
            );

            $this->command->info("Migrated existing tenant #{$tenant->id} ({$tenant->name}) to Enterprise SaaS subscription without duplicating records.");
        } else {
            $this->command->warn("Tenant with ID 1 not found. Existing tenant migration skipped.");
        }
    }
}
