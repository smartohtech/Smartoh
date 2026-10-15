<?php

namespace App\Services\SaaS;

use App\Models\AuditLog;
use App\Models\IspApplication;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SaasPlanVersion;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use App\Models\TenantUsage;
use App\Models\TrialEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubscriptionLifecycleService
{
    /**
     * Start a 7-day trial subscription for a tenant.
     */
    public function createTrialSubscription(
        Tenant $tenant,
        SaasPlan $plan,
        ?SaasPlanVersion $version = null,
        string $billingInterval = 'monthly',
        ?float $negotiatedPrice = null
    ): SaasSubscription {
        $version = $version ?? $plan->currentVersion ?? $plan->versions()->first();
        $trialDays = $plan->trial_days ?? 7;

        $amount = $negotiatedPrice ?? ($billingInterval === 'annually' ? $version->annual_price : $version->monthly_price);

        $subscription = SaasSubscription::create([
            'tenant_id'            => $tenant->id,
            'saas_plan_id'         => $plan->id,
            'saas_plan_version_id' => $version->id,
            'status'               => SaasSubscription::STATUS_TRIAL_ACTIVE,
            'billing_interval'     => $billingInterval,
            'amount'               => $amount,
            'currency'             => $plan->currency ?? 'KES',
            'trial_started_at'     => Carbon::now(),
            'trial_ends_at'        => Carbon::now()->addDays($trialDays),
            'current_period_start' => Carbon::now(),
            'current_period_end'   => Carbon::now()->addDays($trialDays),
            'custom_limits'        => $version->features,
        ]);

        TrialEvent::create([
            'tenant_id'       => $tenant->id,
            'subscription_id' => $subscription->id,
            'event_type'      => 'started',
            'recorded_at'     => Carbon::now(),
            'notes'           => "7-day trial initiated for {$tenant->name} on {$plan->name} (v{$version->version_number}).",
        ]);

        return $subscription;
    }

    /**
     * Idempotently approve an ISP application:
     * - Generates or binds tenant
     * - Creates or updates ISP admin user
     * - Starts 7-day trial subscription
     * - Never duplicates records on repeated requests
     */
    public function idempotentApproveApplication(
        IspApplication $application,
        User $adminUser,
        ?float $customPrice = null,
        ?string $customNotes = null
    ): array {
        return DB::transaction(function () use ($application, $adminUser, $customPrice, $customNotes) {
            // 1. Idempotency Check: If already approved, return existing records
            if ($application->status === 'approved' && $application->provisioned_tenant_id) {
                $existingTenant = Tenant::find($application->provisioned_tenant_id);
                $existingSub = $existingTenant?->activeSubscription;
                $existingUser = User::where('tenant_id', $existingTenant?->id)->where('email', $application->email)->first();

                return [
                    'tenant'       => $existingTenant,
                    'user'         => $existingUser,
                    'subscription' => $existingSub,
                    'is_duplicate' => true,
                ];
            }

            // 2. Provision or resolve Tenant
            $tenant = Tenant::where('subdomain', $application->subdomain)->first();
            if (!$tenant) {
                $tenant = Tenant::create([
                    'name'              => $application->company_name,
                    'subdomain'         => strtolower($application->subdomain),
                    'company_email'     => $application->email,
                    'company_phone'     => $application->phone,
                    'address'           => $application->address,
                    'subscription_tier' => $application->saasPlan?->slug ?? 'starter',
                    'status'            => 'active',
                    'brand_color'       => '#1565C0',
                    'accent_color'      => '#00BCD4',
                    'settings'          => [
                        'network_type'         => $application->network_type,
                        'current_subscribers'  => $application->current_subscribers,
                        'hardware_type'        => $application->hardware_type,
                        'application_number'   => $application->application_number,
                        'approved_by'          => $adminUser->name,
                        'approved_at'          => Carbon::now()->toIso8601String(),
                    ],
                ]);
            } else {
                $tenant->update(['status' => 'active']);
            }

            // 3. Provision or resolve default ISP Administrator
            $user = User::where('email', $application->email)->first();
            $tempPassword = $application->admin_temp_password ?? 'Smartoh@' . rand(1000, 9999);

            if (!$user) {
                $user = User::create([
                    'name'        => $application->contact_name,
                    'email'       => $application->email,
                    'phone'       => $application->phone,
                    'password'    => Hash::make($tempPassword),
                    'tenant_id'   => $tenant->id,
                    'role'        => 'isp_admin',
                    'is_approved' => true,
                ]);
            } else {
                $user->update([
                    'tenant_id'   => $tenant->id,
                    'role'        => 'isp_admin',
                    'is_approved' => true,
                ]);
            }

            // 4. Resolve SaaS Plan & Version
            $plan = $application->saasPlan ?? SaasPlan::where('slug', 'starter')->first() ?? SaasPlan::first();
            $version = $application->saasPlanVersion ?? $plan->currentVersion ?? $plan->versions()->first();

            // 5. Start 7-Day Free Trial Subscription
            $price = $customPrice ?? $application->negotiated_price;
            $subscription = $this->createTrialSubscription($tenant, $plan, $version, $application->billing_interval, $price);

            // 6. Initialize Tenant Usage Tracker
            TenantUsage::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'active_subscribers_count' => 0,
                    'managed_routers_count'    => 0,
                    'active_staff_count'       => 1,
                    'active_sites_count'       => 1,
                    'last_calculated_at'       => Carbon::now(),
                ]
            );

            // 7. Update Application status
            $application->update([
                'status'                => 'approved',
                'reviewed_by_user_id'   => $adminUser->id,
                'reviewed_at'           => Carbon::now(),
                'provisioned_tenant_id' => $tenant->id,
                'provisioned_at'        => Carbon::now(),
                'admin_temp_password'   => $tempPassword,
            ]);

            // 8. Audit Log
            AuditLog::create([
                'user_id'   => $adminUser->id,
                'tenant_id' => $tenant->id,
                'action'    => 'ISP_APPLICATION_APPROVED',
                'details'   => "Approved ISP application {$application->application_number} for {$tenant->name}. 7-day trial started on {$plan->name}.",
                'ip_address'=> request()->ip() ?? '127.0.0.1',
            ]);

            return [
                'tenant'        => $tenant,
                'user'          => $user,
                'subscription'  => $subscription,
                'temp_password' => $tempPassword,
                'is_duplicate'  => false,
            ];
        });
    }

    /**
     * Reject an application with required reason.
     */
    public function rejectApplication(IspApplication $application, User $adminUser, string $reason): void
    {
        $application->update([
            'status'              => 'rejected',
            'rejection_reason'    => $reason,
            'reviewed_by_user_id' => $adminUser->id,
            'reviewed_at'         => Carbon::now(),
        ]);

        AuditLog::create([
            'user_id'   => $adminUser->id,
            'action'    => 'ISP_APPLICATION_REJECTED',
            'details'   => "Rejected ISP application {$application->application_number} ({$application->company_name}). Reason: {$reason}",
            'ip_address'=> request()->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Request additional information from applicant.
     */
    public function requestAdditionalInfo(IspApplication $application, User $adminUser, string $notes): void
    {
        $application->update([
            'status'              => 'info_requested',
            'info_request_notes'  => $notes,
            'reviewed_by_user_id' => $adminUser->id,
            'reviewed_at'         => Carbon::now(),
        ]);

        AuditLog::create([
            'user_id'   => $adminUser->id,
            'action'    => 'ISP_APPLICATION_INFO_REQUESTED',
            'details'   => "Requested additional information for {$application->application_number} ({$application->company_name}): {$notes}",
            'ip_address'=> request()->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Scan active trials in UTC and transition states (cron/worker).
     */
    public function checkAndTransitionTrials(): array
    {
        $results = [
            'reminders_day_5' => 0,
            'reminders_day_6' => 0,
            'expired'         => 0,
            'grace_period'    => 0,
        ];

        $now = Carbon::now();

        // 1. Process active trials
        $activeTrials = SaasSubscription::whereIn('status', [
            SaasSubscription::STATUS_TRIAL_ACTIVE,
            SaasSubscription::STATUS_TRIAL_EXPIRING,
        ])->get();

        foreach ($activeTrials as $sub) {
            if (!$sub->trial_ends_at) continue;

            $hoursRemaining = $now->diffInHours($sub->trial_ends_at, false);

            // A. Trial is expired (0 or negative hours)
            if ($hoursRemaining <= 0) {
                // Enter 48-hour Grace Period if not already in grace
                $sub->update([
                    'status'               => SaasSubscription::STATUS_GRACE_PERIOD,
                    'grace_period_ends_at' => Carbon::now()->addDays(2),
                ]);

                TrialEvent::create([
                    'tenant_id'       => $sub->tenant_id,
                    'subscription_id' => $sub->id,
                    'event_type'      => 'grace_entered',
                    'recorded_at'     => $now,
                    'notes'           => 'Trial ended. 48-hour grace period active before operational restriction.',
                ]);

                // Create pending invoice for upcoming billing period
                $this->generateRenewalInvoice($sub);

                $results['grace_period']++;
            }
            // B. Day 6 Reminder (< 24 hours remaining)
            elseif ($hoursRemaining <= 24) {
                $hasLoggedDay6 = TrialEvent::where('subscription_id', $sub->id)
                    ->where('event_type', 'reminder_day_6')
                    ->exists();

                if (!$hasLoggedDay6) {
                    $sub->update(['status' => SaasSubscription::STATUS_TRIAL_EXPIRING]);

                    TrialEvent::create([
                        'tenant_id'       => $sub->tenant_id,
                        'subscription_id' => $sub->id,
                        'event_type'      => 'reminder_day_6',
                        'recorded_at'     => $now,
                        'notes'           => "Urgent: Trial expires in {$hoursRemaining} hours. Upgrade to prevent interruption.",
                    ]);

                    $results['reminders_day_6']++;
                }
            }
            // C. Day 5 Reminder (< 48 hours remaining)
            elseif ($hoursRemaining <= 48) {
                $hasLoggedDay5 = TrialEvent::where('subscription_id', $sub->id)
                    ->where('event_type', 'reminder_day_5')
                    ->exists();

                if (!$hasLoggedDay5) {
                    $sub->update(['status' => SaasSubscription::STATUS_TRIAL_EXPIRING]);

                    TrialEvent::create([
                        'tenant_id'       => $sub->tenant_id,
                        'subscription_id' => $sub->id,
                        'event_type'      => 'reminder_day_5',
                        'recorded_at'     => $now,
                        'notes'           => 'Trial reminder: 2 days remaining. Review your selected plan terms.',
                    ]);

                    $results['reminders_day_5']++;
                }
            }
        }

        // 2. Check Grace Periods that have lapsed
        $lapsedGrace = SaasSubscription::where('status', SaasSubscription::STATUS_GRACE_PERIOD)
            ->where('grace_period_ends_at', '<=', $now)
            ->get();

        foreach ($lapsedGrace as $sub) {
            $sub->update(['status' => SaasSubscription::STATUS_TRIAL_EXPIRED]);

            TrialEvent::create([
                'tenant_id'       => $sub->tenant_id,
                'subscription_id' => $sub->id,
                'event_type'      => 'expired',
                'recorded_at'     => $now,
                'notes'           => 'Grace period ended. Operational modules restricted until paid activation.',
            ]);

            $results['expired']++;
        }

        return $results;
    }

    /**
     * Generate platform SaaS invoice for tenant subscription.
     */
    public function generateRenewalInvoice(SaasSubscription $subscription): SaasInvoice
    {
        $plan = $subscription->plan;
        $periodStart = Carbon::now();
        $periodEnd = $subscription->billing_interval === 'annually'
            ? Carbon::now()->addYear()
            : Carbon::now()->addMonth();

        $invoice = SaasInvoice::create([
            'invoice_number'       => 'SINV-' . date('Ym') . '-' . strtoupper(Str::random(5)),
            'tenant_id'            => $subscription->tenant_id,
            'subscription_id'      => $subscription->id,
            'subtotal'             => $subscription->amount,
            'tax'                  => 0,
            'total'                => $subscription->amount,
            'currency'             => $subscription->currency,
            'status'               => 'pending',
            'billing_period_start' => $periodStart->toDateString(),
            'billing_period_end'   => $periodEnd->toDateString(),
            'due_date'             => Carbon::now()->addDays(5)->toDateString(),
            'notes'                => "Smartoh Platform Subscription ({$plan->name} - " . ucfirst($subscription->billing_interval) . ")",
        ]);

        SaasInvoiceItem::create([
            'saas_invoice_id' => $invoice->id,
            'description'     => "Smartoh Autonomous ISP Billing Engine ({$plan->name} Plan)",
            'quantity'        => 1,
            'unit_price'      => $subscription->amount,
            'total'           => $subscription->amount,
        ]);

        return $invoice;
    }

    /**
     * Record payment and transition to ACTIVE_PAID.
     */
    public function recordPaymentAndActivate(
        SaasSubscription $subscription,
        float $amount,
        string $reference,
        string $provider = 'manual',
        ?User $recordedBy = null,
        ?string $notes = null
    ): SaasPayment {
        return DB::transaction(function () use ($subscription, $amount, $reference, $provider, $recordedBy, $notes) {
            $invoice = SaasInvoice::where('subscription_id', $subscription->id)
                ->where('status', '!=', 'paid')
                ->latest()
                ->first();

            if (!$invoice) {
                $invoice = $this->generateRenewalInvoice($subscription);
            }

            // Create Payment record
            $payment = SaasPayment::create([
                'saas_invoice_id'     => $invoice->id,
                'tenant_id'           => $subscription->tenant_id,
                'payment_reference'   => $reference,
                'provider'            => $provider,
                'amount'              => $amount,
                'currency'            => $subscription->currency,
                'status'              => 'completed',
                'paid_at'             => Carbon::now(),
                'notes'               => $notes ?? "Payment confirmed via {$provider}",
                'recorded_by_user_id' => $recordedBy?->id,
            ]);

            // Update invoice
            $invoice->update([
                'status'         => 'paid',
                'paid_at'        => Carbon::now(),
                'payment_method' => $provider,
            ]);

            // Transition subscription to ACTIVE_PAID
            $periodEnd = $subscription->billing_interval === 'annually'
                ? Carbon::now()->addYear()
                : Carbon::now()->addMonth();

            $subscription->update([
                'status'               => SaasSubscription::STATUS_ACTIVE_PAID,
                'current_period_start' => Carbon::now(),
                'current_period_end'   => $periodEnd,
                'grace_period_ends_at' => null,
            ]);

            // Ensure tenant is active
            $subscription->tenant()->update(['status' => 'active']);

            // Audit Log
            AuditLog::create([
                'user_id'   => $recordedBy?->id,
                'tenant_id' => $subscription->tenant_id,
                'action'    => 'SUBSCRIPTION_PAID_ACTIVATION',
                'details'   => "Subscription activated (ACTIVE_PAID) for {$subscription->tenant->name}. Paid KES {$amount} ref {$reference}.",
                'ip_address'=> request()->ip() ?? '127.0.0.1',
            ]);

            return $payment;
        });
    }

    /**
     * Suspend tenant workspace.
     */
    public function suspendTenant(Tenant $tenant, User $adminUser, string $reason): void
    {
        $tenant->update(['status' => 'suspended']);

        if ($sub = $tenant->activeSubscription) {
            $sub->update(['status' => SaasSubscription::STATUS_SUSPENDED]);
        }

        AuditLog::create([
            'user_id'   => $adminUser->id,
            'tenant_id' => $tenant->id,
            'action'    => 'TENANT_SUSPENDED',
            'details'   => "Tenant {$tenant->name} suspended by {$adminUser->name}. Reason: {$reason}",
            'ip_address'=> request()->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Reinstate a suspended tenant.
     */
    public function reinstateTenant(Tenant $tenant, User $adminUser): void
    {
        $tenant->update(['status' => 'active']);

        if ($sub = $tenant->activeSubscription) {
            $status = ($sub->current_period_end && Carbon::now()->lessThan($sub->current_period_end))
                ? SaasSubscription::STATUS_ACTIVE_PAID
                : SaasSubscription::STATUS_TRIAL_ACTIVE;

            $sub->update(['status' => $status]);
        }

        AuditLog::create([
            'user_id'   => $adminUser->id,
            'tenant_id' => $tenant->id,
            'action'    => 'TENANT_REINSTATED',
            'details'   => "Tenant {$tenant->name} reinstated to active status by {$adminUser->name}.",
            'ip_address'=> request()->ip() ?? '127.0.0.1',
        ]);
    }
}
