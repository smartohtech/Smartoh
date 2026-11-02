<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_plan_change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('saas_subscriptions')->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_plan_id')->nullable()->constrained('saas_plans')->nullOnDelete();
            $table->foreignId('target_plan_id')->constrained('saas_plans')->cascadeOnDelete();
            $table->string('change_type', 20)->default('upgrade')->index(); // upgrade, downgrade, switch
            $table->enum('billing_interval', ['monthly', 'annually'])->default('monthly');
            $table->unsignedInteger('total_cycle_days')->default(30);
            $table->unsignedInteger('remaining_days')->default(30);
            $table->decimal('current_plan_rate', 10, 2)->default(0);
            $table->decimal('target_plan_rate', 10, 2)->default(0);
            $table->decimal('unused_credit', 10, 2)->default(0);
            $table->decimal('prorated_new_charge', 10, 2)->default(0);
            $table->decimal('prorated_amount_to_add', 10, 2)->default(0);
            $table->decimal('credit_carryover', 10, 2)->default(0);
            $table->string('currency', 10)->default('KES');
            $table->string('effective_mode', 30)->default('immediate'); // immediate, end_of_cycle
            $table->string('payment_method', 40)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->foreignId('saas_invoice_id')->nullable()->constrained('saas_invoices')->nullOnDelete();
            $table->string('status', 30)->default('pending_review')->index(); // pending_review, pending_payment, approved, completed, rejected, cancelled
            $table->text('reason_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_plan_change_requests');
    }
};
