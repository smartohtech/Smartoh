<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('brand_color')->default('#0284c7');
            $table->string('accent_color')->default('#0ea5e9');
            $table->string('logo_url')->nullable();
            $table->enum('theme_mode', ['dark', 'light', 'system'])->default('dark');
            $table->enum('subscription_tier', ['starter', 'pro', 'enterprise'])->default('starter');
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->integer('max_routers')->default(5);
            $table->integer('max_clients')->default(200);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'isp_admin', 'technician', 'billing_officer', 'client'])->default('isp_admin')->after('password');
            $table->boolean('is_approved')->default(true)->after('role');
            $table->string('two_factor_secret')->nullable()->after('is_approved');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->string('avatar_url')->nullable()->after('two_factor_confirmed_at');
            $table->foreignId('client_id')->nullable()->after('avatar_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id', 'phone', 'role', 'is_approved', 
                'two_factor_secret', 'two_factor_confirmed_at', 'avatar_url', 'client_id'
            ]);
        });

        Schema::dropIfExists('tenants');
    }
};
