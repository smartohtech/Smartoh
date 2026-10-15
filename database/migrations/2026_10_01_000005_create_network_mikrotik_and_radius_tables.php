<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name');
            $table->string('ip_address');
            $table->integer('api_port')->default(8728);
            $table->string('api_username');
            $table->text('api_password_encrypted');
            $table->boolean('use_ssl')->default(false);
            $table->string('model')->nullable();
            $table->string('routeros_version')->nullable();
            $table->string('identity')->nullable();
            $table->boolean('is_online')->default(false);
            $table->integer('cpu_load')->default(0);
            $table->bigInteger('free_memory_bytes')->default(0);
            $table->bigInteger('total_memory_bytes')->default(0);
            $table->bigInteger('uptime_seconds')->default(0);
            $table->integer('active_pppoe_sessions')->default(0);
            $table->integer('active_hotspot_sessions')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('bandwidth_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('rate_limit'); // e.g. "10M/10M"
            $table->string('burst_rate')->nullable();
            $table->string('burst_threshold')->nullable();
            $table->string('burst_time')->nullable();
            $table->integer('priority')->default(8);
            $table->string('queue_type')->default('pcq-upload-default/pcq-download-default');
            $table->timestamps();
        });

        Schema::create('radius_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('rate_limit_attribute')->default('Mikrotik-Rate-Limit');
            $table->integer('simultaneous_use')->default(1);
            $table->integer('interim_update_interval_sec')->default(300);
            $table->string('ip_pool_name')->nullable();
            $table->string('dns_primary')->default('8.8.8.8');
            $table->string('dns_secondary')->default('1.1.1.1');
            $table->timestamps();
        });

        Schema::create('radius_server_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('server_ip');
            $table->integer('auth_port')->default(1812);
            $table->integer('acct_port')->default(1813);
            $table->text('shared_secret_encrypted');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accounting_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('mikrotik_device_id')->nullable()->constrained('mikrotik_devices')->nullOnDelete();
            $table->string('username');
            $table->string('framed_ip_address')->nullable();
            $table->string('calling_station_id')->nullable(); // MAC address
            $table->string('nas_ip_address')->nullable();
            $table->string('acct_session_id')->index();
            $table->bigInteger('acct_session_time_sec')->default(0);
            $table->bigInteger('acct_input_octets')->default(0); // Upload
            $table->bigInteger('acct_output_octets')->default(0); // Download
            $table->string('acct_terminate_cause')->nullable();
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_stop')->nullable();
            $table->timestamp('last_interim_update')->nullable();
            $table->timestamps();
        });

        Schema::create('bgp_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('mikrotik_device_id')->constrained('mikrotik_devices')->cascadeOnDelete();
            $table->integer('as_number');
            $table->string('router_id');
            $table->string('peer_name');
            $table->string('peer_address');
            $table->integer('peer_as');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['idle', 'connect', 'active', 'opensent', 'openconfirm', 'established'])->default('idle');
            $table->timestamps();
        });

        Schema::create('ospf_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('mikrotik_device_id')->constrained('mikrotik_devices')->cascadeOnDelete();
            $table->string('area')->default('0.0.0.0');
            $table->string('router_id');
            $table->string('interface');
            $table->integer('cost')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ospf_configs');
        Schema::dropIfExists('bgp_configs');
        Schema::dropIfExists('accounting_records');
        Schema::dropIfExists('radius_server_configs');
        Schema::dropIfExists('radius_profiles');
        Schema::dropIfExists('bandwidth_profiles');
        Schema::dropIfExists('mikrotik_devices');
    }
};
