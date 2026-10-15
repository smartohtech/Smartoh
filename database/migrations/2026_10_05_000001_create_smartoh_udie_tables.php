<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Virtual Routing and Forwarding (VRF) Domains
        Schema::create('vrfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name', 100);
            $table->string('route_distinguisher', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        // 2. Network Segments (VLANs, Subnets & Scope)
        Schema::create('network_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('vrf_id')->nullable()->constrained('vrfs')->nullOnDelete();
            $table->string('name', 150);
            $table->unsignedInteger('vlan_id')->nullable();
            $table->string('subnet_cidr', 50)->nullable();
            $table->string('gateway_ip', 45)->nullable();
            $table->string('interface_name', 50)->nullable();
            $table->string('segment_type', 50)->default('customer_access'); // core, distribution, customer_access, management
            $table->timestamps();

            $table->index(['tenant_id', 'vlan_id']);
            $table->index(['tenant_id', 'subnet_cidr']);
        });

        // 3. Global Device Model Catalog
        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer', 100);
            $table->string('model', 100);
            $table->string('product_class', 100)->nullable();
            $table->string('device_type', 50)->default('router'); // router, ont, onu, olt, access_point, switch, cpe, wireless_cpe, gateway, unknown
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('icon_url')->nullable();
            $table->json('identification_signatures')->nullable(); // OUIs, DHCP fingerprints, TR-069 productClass, TR-181 signatures, sysObjectID, etc.
            $table->json('capabilities')->nullable(); // tr069, tr181_usp, mikrotik_api, olt_omci, snmp, wireguard_vpn, lan_http
            $table->json('supported_protocols')->nullable();
            $table->json('monitoring_templates')->nullable();
            $table->boolean('is_global')->default(true);
            $table->timestamps();

            $table->unique(['manufacturer', 'model']);
            $table->index(['manufacturer', 'device_type']);
        });

        // 4. Tenant Device Model Catalog (Tenant Customizations & Overrides)
        Schema::create('tenant_device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('global_device_model_id')->nullable()->constrained('device_models')->nullOnDelete();
            $table->string('custom_name', 150)->nullable();
            $table->text('custom_description')->nullable();
            $table->string('custom_image_url')->nullable();
            $table->string('custom_thumbnail_url')->nullable();
            $table->json('custom_capabilities')->nullable();
            $table->json('custom_signatures')->nullable();
            $table->json('configuration_templates')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'global_device_model_id']);
        });

        // 5. Universal Devices (Instances)
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('service_id')->nullable();
            $table->foreignId('device_model_id')->nullable()->constrained('device_models')->nullOnDelete();
            $table->foreignId('tenant_device_model_id')->nullable()->constrained('tenant_device_models')->nullOnDelete();
            $table->foreignId('parent_device_id')->nullable()->constrained('devices')->nullOnDelete();

            $table->string('device_type', 50)->default('unknown'); // router, ont, onu, olt, access_point, switch, cpe, wireless_cpe, gateway, unknown
            $table->string('manufacturer', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('product_class', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('ipv6_addresses')->nullable();
            $table->string('hostname', 150)->nullable();
            $table->string('firmware_version', 100)->nullable();
            $table->string('hardware_version', 100)->nullable();

            // Network Scope & VRF Isolation
            $table->foreignId('vrf_id')->nullable()->constrained('vrfs')->nullOnDelete();
            $table->foreignId('network_segment_id')->nullable()->constrained('network_segments')->nullOnDelete();
            $table->unsignedInteger('vlan_id')->nullable();
            $table->string('subnet', 50)->nullable();
            $table->string('interface', 50)->nullable();

            // Verification & Status
            $table->string('status', 50)->default('discovered'); // discovered, candidate, identifying, identified, verified, enrolled, monitored, managed, online, unreachable, offline
            $table->string('identification_status', 50)->default('unknown'); // verified, high_confidence, partially_identified, unknown
            $table->decimal('confidence_score', 5, 2)->default(0.00); // 0.00 to 100.00
            $table->string('fingerprint_hash', 64)->nullable();

            // Lifecycle Timestamps
            $table->timestamp('first_seen')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_identified_at')->nullable();
            $table->timestamp('last_management_check')->nullable();

            // Management Protocols & Capabilities
            $table->string('management_status', 50)->default('pending');
            $table->string('primary_management_protocol', 50)->nullable(); // tr069, tr181_usp, mikrotik_api, olt_api, wireguard_vpn, snmp, lan_api, manual
            $table->string('fallback_management_protocol', 50)->nullable();
            $table->json('supported_protocols')->nullable();
            $table->json('capabilities')->nullable();

            // Correlated Hardware Identifiers
            $table->foreignId('olt_id')->nullable()->constrained('olt_devices')->nullOnDelete();
            $table->string('pon_id', 50)->nullable();
            $table->string('onu_id', 50)->nullable();
            $table->string('pppoe_session_id', 100)->nullable();
            $table->string('vpn_peer_id', 100)->nullable();

            $table->json('raw_metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'mac_address']);
            $table->index(['tenant_id', 'serial_number']);
            $table->index(['tenant_id', 'ip_address', 'vrf_id']);
            $table->index(['tenant_id', 'identification_status']);
            $table->index(['tenant_id', 'parent_device_id']);
        });

        // 6. Device Evidence Graph & Contradictions
        Schema::create('device_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->string('source', 50); // dhcp, arp, ipv6_nd, lldp, radius, pppoe, olt, tr069, tr181_usp, mikrotik, snmp, vpn, lan_api, manual
            $table->string('attribute_name', 100); // manufacturer, model, serial_number, mac_address, ip_address, hostname, oui, etc.
            $table->text('attribute_value');
            $table->json('evidence_scope')->nullable(); // vrf, vlan, subnet, interface
            $table->boolean('is_contradictory')->default(false);
            $table->decimal('confidence_weight', 5, 2)->default(1.00);
            $table->decimal('penalty_weight', 5, 2)->default(0.00);
            $table->timestamp('observed_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'device_id', 'source']);
            $table->index(['tenant_id', 'is_contradictory']);
        });

        // 7. Device Topology & Parent-Child Relationships
        Schema::create('device_topologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('parent_device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignId('child_device_id')->constrained('devices')->cascadeOnDelete();
            $table->string('relationship_type', 50); // olt_to_ont, ont_to_downstream_router, router_to_switch, router_to_ap, switch_to_cpe, core_to_dist, vpn_to_cpe
            $table->decimal('confidence_score', 5, 2)->default(100.00);
            $table->json('evidence')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            $table->unique(['parent_device_id', 'child_device_id', 'relationship_type'], 'dev_topo_parent_child_rel_unique');
            $table->index(['tenant_id', 'parent_device_id']);
            $table->index(['tenant_id', 'child_device_id']);
        });

        // 8. Device Events (Replacements, Topology Changes, Alarms)
        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('event_type', 50); // DEVICE_DISCOVERED, DEVICE_IDENTIFIED, DEVICE_VERIFIED, DEVICE_REPLACEMENT_DETECTED, DEVICE_TOPOLOGY_CHANGED, CONTRADICTION_DETECTED, PARENT_CHILD_CORRELATED, MANAGEMENT_PATH_CHANGED, STATUS_CHANGED
            $table->string('severity', 20)->default('info'); // info, warning, critical
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->json('details')->nullable(); // old_device, new_device, old_topology, new_topology, contradictions, etc.
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'event_type']);
            $table->index(['tenant_id', 'severity']);
        });

        // 9. Discovery Jobs
        Schema::create('discovery_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('vrf_id')->nullable()->constrained('vrfs')->nullOnDelete();
            $table->string('source', 50); // all, dhcp, arp, mikrotik, olt, tr069, tr181, snmp
            $table->string('target_scope', 200)->nullable();
            $table->string('status', 30)->default('pending'); // pending, running, completed, failed
            $table->unsignedInteger('devices_discovered_count')->default(0);
            $table->unsignedInteger('devices_identified_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_jobs');
        Schema::dropIfExists('device_events');
        Schema::dropIfExists('device_topologies');
        Schema::dropIfExists('device_evidences');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('tenant_device_models');
        Schema::dropIfExists('device_models');
        Schema::dropIfExists('network_segments');
        Schema::dropIfExists('vrfs');
    }
};
