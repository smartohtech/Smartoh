<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. SaaS Platform Subscription Plans (Starter, Pro, Enterprise)
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price', 10, 2);
            $table->string('currency', 10)->default('KES');
            $table->unsignedInteger('trial_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. SaaS Plan Versions (Immutable version snapshot to preserve agreed pricing/terms)
        Schema::create('saas_plan_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saas_plan_id')->constrained('saas_plans')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->json('features'); // max_subscribers, max_routers, max_staff, max_sites, api_access, etc.
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price', 10, 2);
            $table->string('currency', 10)->default('KES');
            $table->string('changelog')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->unique(['saas_plan_id', 'version_number']);
        });

        // 3. ISP Onboarding Applications (Pre-tenant stage)
        Schema::create('isp_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->string('company_name');
            $table->string('subdomain')->unique();
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('country', 100)->default('Kenya');
            $table->text('address')->nullable();
            $table->string('network_type')->default('Fiber');
            $table->unsignedInteger('current_subscribers')->default(0);
            $table->string('hardware_type')->nullable();
            $table->foreignId('saas_plan_id')->nullable()->constrained('saas_plans')->nullOnDelete();
            $table->foreignId('saas_plan_version_id')->nullable()->constrained('saas_plan_versions')->nullOnDelete();
            $table->decimal('negotiated_price', 10, 2)->nullable();
            $table->enum('billing_interval', ['monthly', 'annually'])->default('monthly');
            $table->enum('status', ['pending_approval', 'info_requested', 'approved', 'rejected'])->default('pending_approval')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('info_request_notes')->nullable();
            $table->text('resubmitted_notes')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('provisioned_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamp('provisioned_at')->nullable();
            $table->string('admin_temp_password')->nullable();
            $table->timestamps();
        });

        // 4. SaaS Subscriptions with 12-state lifecycle
        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('saas_plan_id')->constrained('saas_plans');
            $table->foreignId('saas_plan_version_id')->constrained('saas_plan_versions');
            $table->string('status', 40)->default('PENDING_APPROVAL')->index();
            $table->enum('billing_interval', ['monthly', 'annually'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('KES');
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('custom_limits')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 5. Trial Events & Audit Logging for Trial Lifecycle
        Schema::create('trial_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('saas_subscriptions')->cascadeOnDelete();
            $table->string('event_type', 50)->index(); // started, reminder_day_5, reminder_day_6, expired, grace_entered, converted
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Platform SaaS Invoices (Billed to ISPs)
        Schema::create('saas_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('saas_subscriptions')->nullOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 10)->default('KES');
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'void'])->default('pending')->index();
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. SaaS Invoice Items
        Schema::create('saas_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saas_invoice_id')->constrained('saas_invoices')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // 8. SaaS Payments (Subscriptions paid to Smartoh Technologies)
        Schema::create('saas_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saas_invoice_id')->nullable()->constrained('saas_invoices')->nullOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('payment_reference')->unique();
            $table->string('provider', 50)->default('manual'); // mpesa, stripe, bank_transfer, manual
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('KES');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed')->index();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Payment Webhook Events (Immutable idempotency log)
        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('event_id')->nullable()->index();
            $table->string('event_type');
            $table->json('payload');
            $table->enum('status', ['received', 'processed', 'failed'])->default('received')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 10. Tenant Usage Real-Time Cache
        Schema::create('tenant_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->unsignedInteger('active_subscribers_count')->default(0);
            $table->unsignedInteger('managed_routers_count')->default(0);
            $table->unsignedInteger('active_staff_count')->default(0);
            $table->unsignedInteger('active_sites_count')->default(0);
            $table->timestamp('last_calculated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usage');
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('saas_payments');
        Schema::dropIfExists('saas_invoice_items');
        Schema::dropIfExists('saas_invoices');
        Schema::dropIfExists('trial_events');
        Schema::dropIfExists('saas_subscriptions');
        Schema::dropIfExists('isp_applications');
        Schema::dropIfExists('saas_plan_versions');
        Schema::dropIfExists('saas_plans');
    }
};
