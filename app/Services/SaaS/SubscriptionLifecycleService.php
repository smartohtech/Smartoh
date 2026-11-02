<?php

namespace App\Services\SaaS;

use App\Models\AuditLog;
use App\Models\IspApplication;
use App\Models\SaasInvoice;
use App\Models\SaasInvoiceItem;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SaasPlanChangeRequest;
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

    /**
     * Ensure the tenant has an active SaaS subscription linked to their current tier.
     */
    public function ensureTenantHasSubscription(Tenant $tenant): SaasSubscription
    {
        SaasPlan::ensureDefaultPlansExist();

        $subscription = $tenant->activeSubscription()->with(['plan', 'planVersion'])->first();
        if ($subscription && $subscription->plan) {
            return $subscription;
        }

        $tierSlug = strtolower($tenant->subscription_tier ?: 'starter');
        $plan = SaasPlan::where('slug', $tierSlug)->with('currentVersion')->first()
            ?? SaasPlan::where('slug', 'starter')->with('currentVersion')->first()
            ?? SaasPlan::with('currentVersion')->first();

        $version = $plan->currentVersion ?? $plan->versions()->first();

        $subscription = SaasSubscription::create([
            'tenant_id'            => $tenant->id,
            'saas_plan_id'         => $plan->id,
            'saas_plan_version_id' => $version->id,
            'status'               => SaasSubscription::STATUS_ACTIVE_PAID,
            'billing_interval'     => 'monthly',
            'amount'               => $plan->monthly_price,
            'currency'             => $plan->currency ?? 'KES',
            'current_period_start' => Carbon::now()->subDays(12),
            'current_period_end'   => Carbon::now()->addDays(18),
            'custom_limits'        => $version->features,
        ]);

        return $subscription->load(['plan', 'planVersion']);
    }

    /**
     * Calculate exact mid-cycle proration when an ISP requests to upgrade or downgrade
     * from an existing tier to a target tier.
     */
    public function calculateTierProration(
        Tenant $tenant,
        SaasPlan $targetPlan,
        ?string $billingInterval = null,
        ?int $overrideRemainingDays = null,
        ?int $overrideTotalDays = null
    ): array {
        $subscription = $this->ensureTenantHasSubscription($tenant);
        $currentPlan = $subscription->plan ?? SaasPlan::where('slug', $tenant->subscription_tier ?: 'starter')->first() ?? $targetPlan;

        $interval = $billingInterval ?: ($subscription->billing_interval ?: 'monthly');
        $defaultCycleDays = $interval === 'annually' ? 365 : 30;

        if ($overrideTotalDays !== null && $overrideTotalDays > 0) {
            $totalDays = $overrideTotalDays;
        } elseif ($subscription->current_period_start && $subscription->current_period_end) {
            $diffTotal = (int) round($subscription->current_period_start->diffInDays($subscription->current_period_end));
            $totalDays = $diffTotal >= 7 ? $diffTotal : $defaultCycleDays;
        } else {
            $totalDays = $defaultCycleDays;
        }

        if ($overrideRemainingDays !== null && $overrideRemainingDays >= 0) {
            $remainingDays = min($totalDays, max(1, $overrideRemainingDays));
        } elseif ($subscription->current_period_end && Carbon::now()->lessThan($subscription->current_period_end)) {
            $hoursLeft = max(1, Carbon::now()->diffInHours($subscription->current_period_end, false));
            $remainingDays = min($totalDays, max(1, (int) ceil($hoursLeft / 24)));
        } else {
            $remainingDays = $totalDays;
        }

        $currentPlanRate = $interval === 'annually'
            ? (float) $currentPlan->annual_price
            : (float) $currentPlan->monthly_price;

        // If the subscription has a custom/negotiated amount in the same interval, respect it
        if ($subscription->billing_interval === $interval && (float) $subscription->amount > 0 && $subscription->saas_plan_id === $currentPlan->id) {
            $currentPlanRate = (float) $subscription->amount;
        }

        $targetPlanRate = $interval === 'annually'
            ? (float) $targetPlan->annual_price
            : (float) $targetPlan->monthly_price;

        $dailyCurrentRate = round($currentPlanRate / $totalDays, 2);
        $dailyTargetRate  = round($targetPlanRate / $totalDays, 2);

        $unusedCredit      = round(($currentPlanRate / $totalDays) * $remainingDays, 2);
        $proratedNewCharge = round(($targetPlanRate / $totalDays) * $remainingDays, 2);
        $netDifference     = round($proratedNewCharge - $unusedCredit, 2);

        $isSamePlan  = ($currentPlan->id === $targetPlan->id) && ($subscription->billing_interval === $interval);
        $isUpgrade   = !$isSamePlan && ($targetPlanRate > $currentPlanRate || ($targetPlanRate == $currentPlanRate && $targetPlan->sort_order > $currentPlan->sort_order));
        $isDowngrade = !$isSamePlan && !$isUpgrade;

        $changeType = $isUpgrade ? 'upgrade' : ($isDowngrade ? 'downgrade' : 'switch');
        $proratedAmountToAdd = $isUpgrade ? max(0.0, $netDifference) : 0.0;
        $creditCarryover     = $isDowngrade ? abs(min(0.0, $netDifference)) : 0.0;

        return [
            'subscription_id'        => $subscription->id,
            'current_plan_id'        => $currentPlan->id,
            'current_plan_name'      => $currentPlan->name,
            'current_plan_slug'      => $currentPlan->slug,
            'current_plan_rate'      => $currentPlanRate,
            'target_plan_id'         => $targetPlan->id,
            'target_plan_name'       => $targetPlan->name,
            'target_plan_slug'       => $targetPlan->slug,
            'target_plan_rate'       => $targetPlanRate,
            'billing_interval'       => $interval,
            'total_cycle_days'       => $totalDays,
            'remaining_days'         => $remainingDays,
            'daily_current_rate'     => $dailyCurrentRate,
            'daily_target_rate'      => $dailyTargetRate,
            'unused_credit'          => $unusedCredit,
            'prorated_new_charge'    => $proratedNewCharge,
            'net_difference'         => $netDifference,
            'prorated_amount_to_add' => $proratedAmountToAdd,
            'credit_carryover'       => $creditCarryover,
            'is_upgrade'             => $isUpgrade,
            'is_downgrade'           => $isDowngrade,
            'is_same_plan'           => $isSamePlan,
            'change_type'            => $changeType,
            'currency'               => $targetPlan->currency ?? 'KES',
        ];
    }

    /**
     * Create an ISP Upgrade or Downgrade Request with mid-cycle proration calculation,
     * generating a prorated top-up invoice for upgrades and optionally processing immediate activation.
     */
    public function submitPlanChangeRequest(
        Tenant $tenant,
        SaasPlan $targetPlan,
        ?User $user = null,
        array $options = []
    ): SaasPlanChangeRequest {
        return DB::transaction(function () use ($tenant, $targetPlan, $user, $options) {
            $interval = $options['billing_interval'] ?? null;
            $remainingDays = isset($options['remaining_days']) ? (int) $options['remaining_days'] : null;
            $totalDays = isset($options['total_cycle_days']) ? (int) $options['total_cycle_days'] : null;

            $calc = $this->calculateTierProration($tenant, $targetPlan, $interval, $remainingDays, $totalDays);

            // Cancel any previous pending change requests for this tenant
            SaasPlanChangeRequest::where('tenant_id', $tenant->id)
                ->whereIn('status', [
                    SaasPlanChangeRequest::STATUS_PENDING_REVIEW,
                    SaasPlanChangeRequest::STATUS_PENDING_PAYMENT,
                ])
                ->update(['status' => SaasPlanChangeRequest::STATUS_CANCELLED]);

            $ref = 'PCR-' . date('Ym') . '-' . strtoupper(Str::random(5));
            $effectiveMode = $options['effective_mode'] ?? ($calc['is_upgrade'] ? 'immediate' : 'end_of_cycle');
            $paymentMethod = $options['payment_method'] ?? 'mpesa';
            $payNow = !empty($options['pay_now']);
            $autoApprove = !empty($options['auto_approve']);

            $invoice = null;
            if ($calc['is_upgrade'] && $calc['prorated_amount_to_add'] > 0) {
                $subscription = SaasSubscription::find($calc['subscription_id']);
                $periodEnd = $subscription?->current_period_end ?? Carbon::now()->addDays($calc['remaining_days']);

                $invoice = SaasInvoice::create([
                    'invoice_number'       => 'SINV-UPG-' . strtoupper(Str::random(6)),
                    'tenant_id'            => $tenant->id,
                    'subscription_id'      => $calc['subscription_id'],
                    'subtotal'             => $calc['prorated_amount_to_add'],
                    'tax'                  => 0,
                    'total'                => $calc['prorated_amount_to_add'],
                    'currency'             => $calc['currency'],
                    'status'               => 'pending',
                    'billing_period_start' => Carbon::now()->toDateString(),
                    'billing_period_end'   => $periodEnd->toDateString(),
                    'due_date'             => Carbon::now()->addDays(3)->toDateString(),
                    'notes'                => "Mid-Cycle Tier Upgrade Proration: {$calc['current_plan_name']} -> {$calc['target_plan_name']} ({$calc['remaining_days']}/{$calc['total_cycle_days']} days remaining)",
                ]);

                SaasInvoiceItem::create([
                    'saas_invoice_id' => $invoice->id,
                    'description'     => "Prorated Target Tier ({$calc['target_plan_name']}) for {$calc['remaining_days']} remaining days",
                    'quantity'        => 1,
                    'unit_price'      => $calc['prorated_new_charge'],
                    'total'           => $calc['prorated_new_charge'],
                ]);

                SaasInvoiceItem::create([
                    'saas_invoice_id' => $invoice->id,
                    'description'     => "Less: Unused Credit from Current Tier ({$calc['current_plan_name']}) for {$calc['remaining_days']} days",
                    'quantity'        => 1,
                    'unit_price'      => -$calc['unused_credit'],
                    'total'           => -$calc['unused_credit'],
                ]);
            }

            $initialStatus = $calc['is_upgrade']
                ? ($calc['prorated_amount_to_add'] > 0 ? SaasPlanChangeRequest::STATUS_PENDING_PAYMENT : SaasPlanChangeRequest::STATUS_PENDING_REVIEW)
                : SaasPlanChangeRequest::STATUS_PENDING_REVIEW;

            $changeRequest = SaasPlanChangeRequest::create([
                'reference_number'       => $ref,
                'tenant_id'              => $tenant->id,
                'subscription_id'        => $calc['subscription_id'],
                'requested_by_user_id'   => $user?->id,
                'current_plan_id'        => $calc['current_plan_id'],
                'target_plan_id'         => $calc['target_plan_id'],
                'change_type'            => $calc['change_type'],
                'billing_interval'       => $calc['billing_interval'],
                'total_cycle_days'       => $calc['total_cycle_days'],
                'remaining_days'         => $calc['remaining_days'],
                'current_plan_rate'      => $calc['current_plan_rate'],
                'target_plan_rate'       => $calc['target_plan_rate'],
                'unused_credit'          => $calc['unused_credit'],
                'prorated_new_charge'    => $calc['prorated_new_charge'],
                'prorated_amount_to_add' => $calc['prorated_amount_to_add'],
                'credit_carryover'       => $calc['credit_carryover'],
                'currency'               => $calc['currency'],
                'effective_mode'         => $effectiveMode,
                'payment_method'         => $paymentMethod,
                'saas_invoice_id'        => $invoice?->id,
                'status'                 => $initialStatus,
                'reason_notes'           => $options['reason_notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id'    => $user?->id,
                'tenant_id'  => $tenant->id,
                'action'     => 'ISP_PLAN_' . strtoupper($calc['change_type']) . '_REQUESTED',
                'details'    => "ISP {$tenant->name} requested {$calc['change_type']} from {$calc['current_plan_name']} to {$calc['target_plan_name']} (Ref: {$ref}). Prorated amount to add: {$calc['currency']} {$calc['prorated_amount_to_add']} (Unused credit: {$calc['unused_credit']}, New charge: {$calc['prorated_new_charge']} for {$calc['remaining_days']}/{$calc['total_cycle_days']} days).",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            // If user opted to pay prorated upgrade amount immediately or auto-apply
            if ($payNow && $calc['is_upgrade']) {
                $payRef = $options['payment_reference'] ?? ('UPG-' . strtoupper(Str::random(8)));
                $this->payAndCompleteUpgradeRequest($changeRequest, $paymentMethod, $payRef, $user);
            } elseif ($autoApprove) {
                $this->applyPlanChangeRequest($changeRequest, $user, 'Auto-applied per ISP tier preference.');
            }

            return $changeRequest->fresh(['currentPlan', 'targetPlan', 'invoice']);
        });
    }

    /**
     * Record prorated top-up payment for an upgrade request and immediately activate the higher tier.
     */
    public function payAndCompleteUpgradeRequest(
        SaasPlanChangeRequest $changeRequest,
        string $provider = 'mpesa',
        ?string $reference = null,
        ?User $user = null
    ): SaasSubscription {
        return DB::transaction(function () use ($changeRequest, $provider, $reference, $user) {
            $reference = $reference ?: ('UPG-' . strtoupper(Str::random(8)));
            $amountToPay = (float) $changeRequest->prorated_amount_to_add;

            if ($amountToPay > 0) {
                $invoice = $changeRequest->invoice;
                if (!$invoice) {
                    $invoice = SaasInvoice::create([
                        'invoice_number'       => 'SINV-UPG-' . strtoupper(Str::random(6)),
                        'tenant_id'            => $changeRequest->tenant_id,
                        'subscription_id'      => $changeRequest->subscription_id,
                        'subtotal'             => $amountToPay,
                        'tax'                  => 0,
                        'total'                => $amountToPay,
                        'currency'             => $changeRequest->currency,
                        'status'               => 'paid',
                        'billing_period_start' => Carbon::now()->toDateString(),
                        'billing_period_end'   => Carbon::now()->addDays($changeRequest->remaining_days)->toDateString(),
                        'due_date'             => Carbon::now()->toDateString(),
                        'paid_at'              => Carbon::now(),
                        'payment_method'       => $provider,
                        'notes'                => "Prorated Upgrade Top-Up ({$changeRequest->reference_number})",
                    ]);
                    $changeRequest->update(['saas_invoice_id' => $invoice->id]);
                } else {
                    $invoice->update([
                        'status'         => 'paid',
                        'paid_at'        => Carbon::now(),
                        'payment_method' => $provider,
                    ]);
                }

                SaasPayment::create([
                    'saas_invoice_id'     => $invoice->id,
                    'tenant_id'           => $changeRequest->tenant_id,
                    'payment_reference'   => $reference,
                    'provider'            => $provider,
                    'amount'              => $amountToPay,
                    'currency'            => $changeRequest->currency,
                    'status'              => 'completed',
                    'paid_at'             => Carbon::now(),
                    'notes'               => "Mid-cycle upgrade proration top-up ({$changeRequest->reference_number})",
                    'recorded_by_user_id' => $user?->id,
                ]);
            }

            $changeRequest->update([
                'payment_method'    => $provider,
                'payment_reference' => $reference,
            ]);

            return $this->applyPlanChangeRequest(
                $changeRequest,
                $user,
                "Prorated upgrade top-up of {$changeRequest->currency} " . number_format($amountToPay, 2) . " paid via " . strtoupper($provider) . " (Ref: {$reference})."
            );
        });
    }

    /**
     * Apply an upgrade or downgrade request to the tenant's SaaS subscription & resource quotas.
     */
    public function applyPlanChangeRequest(
        SaasPlanChangeRequest $changeRequest,
        ?User $actor = null,
        ?string $adminNotes = null
    ): SaasSubscription {
        return DB::transaction(function () use ($changeRequest, $actor, $adminNotes) {
            $tenant = $changeRequest->tenant;
            $targetPlan = $changeRequest->targetPlan()->with('currentVersion')->first();
            $targetVersion = $targetPlan->currentVersion ?? $targetPlan->versions()->first();
            $features = $targetVersion->features ?? [];

            $subscription = $changeRequest->subscription ?? $this->ensureTenantHasSubscription($tenant);

            $newAmount = $changeRequest->billing_interval === 'annually'
                ? $targetPlan->annual_price
                : $targetPlan->monthly_price;

            $newStatus = $changeRequest->isUpgrade() && (float) $changeRequest->prorated_amount_to_add > 0
                ? SaasSubscription::STATUS_ACTIVE_PAID
                : ($subscription->status ?: SaasSubscription::STATUS_ACTIVE_PAID);

            $subscription->update([
                'saas_plan_id'         => $targetPlan->id,
                'saas_plan_version_id' => $targetVersion->id,
                'billing_interval'     => $changeRequest->billing_interval,
                'amount'               => $newAmount,
                'currency'             => $targetPlan->currency ?? 'KES',
                'status'               => $newStatus,
                'custom_limits'        => $features,
            ]);

            $tenantSettings = $tenant->settings ?? [];
            if ($changeRequest->credit_carryover > 0) {
                $tenantSettings['saas_credit_balance'] = round(($tenantSettings['saas_credit_balance'] ?? 0) + (float) $changeRequest->credit_carryover, 2);
            }
            $tenantSettings['last_plan_change_ref'] = $changeRequest->reference_number;
            $tenantSettings['last_plan_changed_at'] = Carbon::now()->toIso8601String();

            $tenant->update([
                'subscription_tier' => $targetPlan->slug,
                'max_routers'       => (int) ($features['max_routers'] ?? 10),
                'max_clients'       => (int) ($features['max_subscribers'] ?? 1000),
                'settings'          => $tenantSettings,
            ]);

            $changeRequest->update([
                'status'              => SaasPlanChangeRequest::STATUS_COMPLETED,
                'admin_notes'         => $adminNotes ?? $changeRequest->admin_notes,
                'reviewed_by_user_id' => $actor?->id ?? $changeRequest->reviewed_by_user_id,
                'reviewed_at'         => Carbon::now(),
                'applied_at'          => Carbon::now(),
            ]);

            TrialEvent::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription->id,
                'event_type'      => 'plan_' . $changeRequest->change_type,
                'recorded_at'     => Carbon::now(),
                'notes'           => ucfirst($changeRequest->change_type) . " applied to {$targetPlan->name} (Ref: {$changeRequest->reference_number}). " . ($adminNotes ?? ''),
            ]);

            AuditLog::create([
                'user_id'    => $actor?->id,
                'tenant_id'  => $tenant->id,
                'action'     => 'ISP_PLAN_' . strtoupper($changeRequest->change_type) . '_APPLIED',
                'details'    => "Applied {$changeRequest->change_type} for {$tenant->name} to {$targetPlan->name} ({$changeRequest->reference_number}). New quotas: {$features['max_subscribers']} subscribers, {$features['max_routers']} routers.",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $subscription->fresh(['plan', 'planVersion']);
        });
    }
}
