<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('advertiser_name');
            $table->string('media_image_url');
            $table->string('destination_url')->nullable();
            $table->integer('duration_seconds')->default(15);
            $table->integer('required_views')->default(1000);
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->integer('reward_points_per_view')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_loyalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->integer('free_connections_redeemed')->default(0);
            $table->timestamps();
        });

        Schema::create('wholesale_capacity_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('exchange_point'); // e.g. "KIXP Nairobi", "IXPN Lagos", "London LINX"
            $table->integer('total_capacity_mbps');
            $table->integer('allocated_capacity_mbps')->default(0);
            $table->integer('reserved_capacity_mbps')->default(0);
            $table->timestamps();
        });

        Schema::create('wholesale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('capacity_pool_id')->constrained('wholesale_capacity_pools')->cascadeOnDelete();
            $table->string('name');
            $table->integer('committed_bandwidth_cir_mbps');
            $table->integer('burst_bandwidth_pir_mbps');
            $table->integer('contention_ratio')->default(1);
            $table->enum('handoff_type', ['vlan_qinq', 'bgp_direct', 'cross_connect', 'gre_tunnel'])->default('vlan_qinq');
            $table->decimal('price_per_mbps', 10, 2);
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wholesale_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('reseller_company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone');
            $table->decimal('balance', 14, 2)->default(0.00);
            $table->decimal('credit_limit', 14, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('wholesale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('wholesale_wallet_id')->constrained('wholesale_wallets')->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->enum('type', ['deposit', 'service_deduction', 'credit_adjustment', 'refund']);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('wholesale_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('wholesale_wallet_id')->constrained('wholesale_wallets')->cascadeOnDelete();
            $table->foreignId('wholesale_product_id')->constrained('wholesale_products')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->integer('bandwidth_mbps');
            $table->decimal('monthly_charge', 12, 2);
            $table->enum('status', ['requested', 'approved', 'contract_signed', 'provisioning', 'active', 'rejected'])->default('requested');
            $table->integer('vlan_id')->nullable();
            $table->string('allocated_subnet')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wholesale_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('wholesale_order_id')->constrained('wholesale_orders')->cascadeOnDelete();
            $table->decimal('target_uptime_pct', 5, 2)->default(99.95);
            $table->integer('max_mttr_hours')->default(4); // Mean time to repair
            $table->decimal('breach_penalty_pct', 5, 2)->default(5.00);
            $table->timestamps();
        });

        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('provider', ['africastalking', 'twilio', 'infobip', 'whatsapp_cloud', 'bulksms'])->default('africastalking');
            $table->string('sender_id')->nullable();
            $table->text('credentials_encrypted');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'critical', 'success'])->default('info');
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event'); // e.g. "tenant.created", "client.suspended", "router.rebooted", "payment.received"
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->boolean('require_two_factor')->default(false);
            $table->integer('session_timeout_minutes')->default(60);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('password_expiry_days')->default(90);
            $table->boolean('ip_whitelist_enabled')->default(false);
            $table->text('ip_whitelist')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('sms_gateways');
        Schema::dropIfExists('wholesale_slas');
        Schema::dropIfExists('wholesale_orders');
        Schema::dropIfExists('wholesale_transactions');
        Schema::dropIfExists('wholesale_wallets');
        Schema::dropIfExists('wholesale_products');
        Schema::dropIfExists('wholesale_capacity_pools');
        Schema::dropIfExists('client_loyalties');
        Schema::dropIfExists('hotspot_ads');
    }
};
