<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g. "Huawei HG8245H5 Profile", "TP-Link Archer C6 TR-069"
            $table->string('manufacturer');
            $table->string('model');
            $table->json('supported_protocols'); // ['tr069', 'tr181', 'wireguard']
            $table->json('parameter_mappings')->nullable();
            $table->timestamps();
        });

        Schema::create('firmware_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('filename');
            $table->string('version');
            $table->string('manufacturer');
            $table->string('compatible_model');
            $table->string('sha256_hash');
            $table->bigInteger('file_size_bytes');
            $table->string('storage_path');
            $table->text('release_notes')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('provisioning_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->enum('protocol', ['tr069', 'tr181_usp', 'mikrotik_script', 'olt_wan']);
            $table->json('template_payload'); // Variables: {{pppoe_user}}, {{pppoe_pass}}, {{vlan_id}}, {{wifi_ssid}}, etc.
            $table->timestamps();
        });

        Schema::create('client_routers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('device_profile_id')->nullable()->constrained('device_profiles')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('hardware_version')->nullable();
            $table->string('software_version')->nullable();
            $table->enum('preferred_management_path', ['tr069', 'tr181_usp', 'wireguard', 'olt_api', 'lan_direct', 'manual'])->default('tr069');
            $table->enum('fallback_management_path', ['wireguard', 'lan_direct', 'manual'])->default('wireguard');
            $table->string('cwmp_connection_url')->nullable();
            $table->string('cwmp_username')->nullable();
            $table->text('cwmp_password_encrypted')->nullable();
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->decimal('optical_power_rx', 5, 2)->nullable(); // in dBm e.g. -19.45
            $table->decimal('optical_power_tx', 5, 2)->nullable();
            $table->bigInteger('uptime_seconds')->default(0);
            $table->timestamp('last_inform_at')->nullable();
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->timestamps();
        });

        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name');
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->string('ssid');
            $table->integer('channel')->default(6);
            $table->integer('active_clients')->default(0);
            $table->boolean('is_online')->default(true);
            $table->timestamps();
        });

        Schema::create('vpn_tunnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_router_id')->nullable()->constrained('client_routers')->nullOnDelete();
            $table->string('tunnel_name');
            $table->enum('tunnel_type', ['wireguard', 'l2tp', 'sstp', 'openvpn'])->default('wireguard');
            $table->string('assigned_ip'); // e.g. "10.150.0.12/32"
            $table->string('server_endpoint')->nullable(); // e.g. "vpn.smartoh.isp:51820"
            $table->text('public_key');
            $table->text('private_key_encrypted')->nullable();
            $table->text('preshared_key_encrypted')->nullable();
            $table->string('allowed_ips')->default('0.0.0.0/0');
            $table->bigInteger('bytes_rx')->default(0);
            $table->bigInteger('bytes_tx')->default(0);
            $table->timestamp('last_handshake_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('olt_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name');
            $table->enum('vendor', ['huawei', 'zte', 'fiberhome', 'smartolt', 'vsol', 'bdcom'])->default('smartolt');
            $table->string('ip_address');
            $table->string('api_url')->nullable();
            $table->text('api_token_encrypted')->nullable();
            $table->string('snmp_community')->nullable();
            $table->integer('pon_ports_count')->default(8);
            $table->integer('registered_onus_count')->default(0);
            $table->boolean('is_online')->default(true);
            $table->timestamps();
        });

        Schema::create('ont_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('olt_device_id')->constrained('olt_devices')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('mac_address')->nullable();
            $table->integer('pon_port')->default(1);
            $table->integer('onu_slot')->default(1);
            $table->string('model')->nullable();
            $table->decimal('rx_power_dbm', 5, 2)->nullable();
            $table->decimal('tx_power_dbm', 5, 2)->nullable();
            $table->integer('vlan_id')->nullable();
            $table->enum('wan_mode', ['pppoe', 'dhcp', 'static', 'bridge'])->default('pppoe');
            $table->enum('status', ['online', 'offline', 'los', 'dying_gasp'])->default('online');
            $table->timestamps();
        });

        Schema::create('deployment_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_router_id')->nullable()->constrained('client_routers')->nullOnDelete();
            $table->foreignId('firmware_package_id')->nullable()->constrained('firmware_packages')->nullOnDelete();
            $table->foreignId('provisioning_template_id')->nullable()->constrained('provisioning_templates')->nullOnDelete();
            $table->enum('job_type', ['provision', 'firmware_upgrade', 'reboot', 'factory_reset', 'wifi_update']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'rolled_back'])->default('pending');
            $table->text('payload_data')->nullable();
            $table->text('execution_log')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('device_telemetries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('device_type'); // 'mikrotik', 'cpe_router', 'ont'
            $table->unsignedBigInteger('device_id');
            $table->decimal('cpu_usage_pct', 5, 2)->nullable();
            $table->decimal('memory_usage_pct', 5, 2)->nullable();
            $table->decimal('latency_ms', 6, 2)->nullable();
            $table->decimal('packet_loss_pct', 5, 2)->nullable();
            $table->bigInteger('download_bytes_sec')->default(0);
            $table->bigInteger('upload_bytes_sec')->default(0);
            $table->decimal('optical_rx_dbm', 5, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
        });

        Schema::create('management_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('session_id')->unique();
            $table->string('device_serial');
            $table->enum('protocol', ['tr069', 'tr181_usp']);
            $table->string('current_step')->nullable();
            $table->text('session_data')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_sessions');
        Schema::dropIfExists('device_telemetries');
        Schema::dropIfExists('deployment_jobs');
        Schema::dropIfExists('ont_devices');
        Schema::dropIfExists('olt_devices');
        Schema::dropIfExists('vpn_tunnels');
        Schema::dropIfExists('access_points');
        Schema::dropIfExists('client_routers');
        Schema::dropIfExists('provisioning_templates');
        Schema::dropIfExists('firmware_packages');
        Schema::dropIfExists('device_profiles');
    }
};
