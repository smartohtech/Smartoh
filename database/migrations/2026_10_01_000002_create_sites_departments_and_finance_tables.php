<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('mpesa_paybill')->nullable();
            $table->string('mpesa_till')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->string('receipt_prefix')->default('REC');
            $table->integer('next_invoice_number')->default(1001);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name');
            $table->string('head_name')->nullable();
            $table->timestamps();
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->decimal('current_balance', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g. "October 2026", "Q4 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed', 'locked'])->default('open');
            $table->timestamps();
        });

        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('financial_period_id')->nullable()->constrained('financial_periods')->nullOnDelete();
            $table->string('transaction_number')->unique();
            $table->date('transaction_date');
            $table->foreignId('debit_account_id')->constrained('chart_of_accounts');
            $table->foreignId('credit_account_id')->constrained('chart_of_accounts');
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_posted')->default(true);
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('site_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('financial_period_id')->constrained('financial_periods')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->decimal('budgeted_amount', 14, 2)->default(0.00);
            $table->decimal('actual_amount', 14, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('inter_site_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('from_site_id')->constrained('sites');
            $table->foreignId('to_site_id')->constrained('sites');
            $table->enum('transfer_type', ['stock', 'cash', 'shared_expense']);
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inter_site_transfers');
        Schema::dropIfExists('site_budgets');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('financial_periods');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('sites');
    }
};
