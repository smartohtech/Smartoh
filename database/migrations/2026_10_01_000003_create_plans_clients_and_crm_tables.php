<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'unlimited', 'data_cap'])->default('monthly');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->integer('download_speed_kbps')->default(10240); // 10 Mbps
            $table->integer('upload_speed_kbps')->default(5120);   // 5 Mbps
            $table->integer('fup_limit_gb')->nullable();
            $table->integer('fup_throttled_download_kbps')->nullable();
            $table->integer('fup_throttled_upload_kbps')->nullable();
            $table->string('burst_limit')->nullable(); // e.g. "20M/10M"
            $table->string('burst_threshold')->nullable();
            $table->string('burst_time')->nullable();
            $table->integer('contention_ratio')->default(1); // 1:1, 1:4 etc.
            $table->integer('validity_days')->default(30);
            $table->integer('vlan_id')->nullable();
            $table->string('mikrotik_profile_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // 10%
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('billing_plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
            $table->foreignId('reseller_id')->nullable()->constrained('resellers')->nullOnDelete();
            $table->string('account_number')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('id_passport_number')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('service_type', ['pppoe', 'hotspot', 'static', 'dhcp'])->default('pppoe');
            $table->string('pppoe_username')->nullable();
            $table->string('pppoe_password')->nullable();
            $table->string('assigned_ip')->nullable();
            $table->string('mac_address')->nullable();
            $table->enum('status', [
                'active', 'suspended', 'expired', 'expiring_soon', 'fup_throttled', 'overdue', 'pending_installation'
            ])->default('active');
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->bigInteger('data_usage_bytes')->default(0);
            $table->timestamp('current_cycle_start')->nullable();
            $table->timestamp('current_cycle_end')->nullable();
            $table->timestamp('service_suspended_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('service_interest')->nullable(); // "Fiber 50Mbps", "Hotspot", etc.
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('resellers');
        Schema::dropIfExists('billing_plans');
    }
};
