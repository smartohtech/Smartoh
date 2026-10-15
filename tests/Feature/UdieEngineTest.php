<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceEvidence;
use App\Models\DeviceModel;
use App\Models\DiscoveryJob;
use App\Models\NetworkSegment;
use App\Models\Tenant;
use App\Models\TenantDeviceModel;
use App\Models\User;
use App\Models\Vrf;
use App\Services\UDIE\AnomalyDetector;
use App\Services\UDIE\CapabilityEngine;
use App\Services\UDIE\ConfidenceEngine;
use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DeviceImageResolver;
use App\Services\UDIE\DiscoveryCoordinator;
use App\Services\UDIE\FingerprintEngine;
use App\Services\UDIE\IdentificationEngine;
use App\Services\UDIE\ManagementPathSelector;
use App\Services\UDIE\ParentChildCorrelationEngine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UdieEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Smartoh Test ISP',
            'domain' => 'test.smartoh.net',
            'status' => 'active',
        ]);

        $this->user = User::where('tenant_id', $this->tenant->id)->first() ?? User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'UDIE Super Admin',
            'email' => 'udie_admin_' . uniqid() . '@smartoh.net',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);
    }

    /**
     * Test Rule 4: Bridged ONT and Downstream Router must be independent instances.
     * The customer router downstream must NEVER inherit the ONT's model identity.
     */
    public function test_bridged_ont_and_downstream_router_are_separated_and_router_does_not_inherit_ont_identity(): void
    {
        $coordinator = app(DiscoveryCoordinator::class);

        // 1. Create a Huawei Bridged ONT
        $ont = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'ont',
            'manufacturer' => 'Huawei',
            'model' => 'HG8145H',
            'serial_number' => '48575443A1B2C3D4',
            'mac_address' => '00:25:9E:11:22:33',
            'identification_status' => 'verified',
            'confidence_score' => 95.0,
            'raw_metadata' => ['wan_mode' => 'bridge'],
        ]);

        // 2. Telemetry observed behind the ONT's ethernet port (MikroTik downstream router)
        $routerEvidence = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'mikrotik_api',
            timestamp: now()->toIso8601String(),
            ipAddress: '192.168.1.100',
            macAddress: 'DC:2C:6E:AA:BB:CC', // MikroTik OUI
            hostname: 'MikroTik-RB4011',
            manufacturer: 'MikroTik',
            model: 'RB4011iGS+5HacQ2HnD-IN',
            deviceType: 'router',
            upstreamDeviceId: $ont->id,
            confidenceContribution: 80.0,
            rawMetadata: ['bridge_downstream' => true, 'port' => 'eth1']
        );

        // Ingest evidence through coordinator
        $childRouter = $coordinator->ingestEvidence($routerEvidence);

        $this->assertNotNull($childRouter);
        $this->assertNotEquals($ont->id, $childRouter->id);
        $this->assertEquals($ont->id, $childRouter->parent_device_id);

        // Verify independent models (Router must NOT inherit HG8145H)
        $this->assertEquals('ont', $ont->device_type);
        $this->assertEquals('HG8145H', $ont->model);

        $this->assertEquals('router', $childRouter->device_type);
        $this->assertEquals('RB4011iGS+5HacQ2HnD-IN', $childRouter->model);
        $this->assertEquals('MikroTik', $childRouter->manufacturer);

        // Verify topology link exists in DB
        $this->assertDatabaseHas('device_topologies', [
            'parent_device_id' => $ont->id,
            'child_device_id' => $childRouter->id,
            'relationship_type' => 'ont_to_downstream_router',
        ]);
    }

    /**
     * Test Rules 5 & 7: Network Scope & VRF Awareness.
     * The exact same private IP (192.168.1.1) in different VRFs or VLANs
     * must NEVER be merged into the same device instance.
     */
    public function test_scoped_ip_address_in_different_vrfs_creates_separate_devices_never_merged(): void
    {
        $coordinator = app(DiscoveryCoordinator::class);

        $vrfA = Vrf::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'VRF-CORP-A',
            'rd' => '65000:101',
        ]);

        $vrfB = Vrf::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'VRF-CORP-B',
            'rd' => '65000:102',
        ]);

        // Observation 1: 192.168.1.1 in VRF-CORP-A
        $evidence1 = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'arp',
            timestamp: now()->toIso8601String(),
            ipAddress: '192.168.1.1',
            macAddress: '00:11:22:33:44:55',
            vrfId: $vrfA->id,
            vlanId: 100,
            manufacturer: 'Cisco',
            model: 'Catalyst 2960'
        );

        $deviceA = $coordinator->processEvidence($evidence1);

        // Observation 2: Same IP 192.168.1.1 in VRF-CORP-B with different MAC
        $evidence2 = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'arp',
            timestamp: now()->toIso8601String(),
            ipAddress: '192.168.1.1',
            macAddress: 'AA:BB:CC:DD:EE:FF',
            vrfId: $vrfB->id,
            vlanId: 200,
            manufacturer: 'Huawei',
            model: 'HG8145H'
        );

        $deviceB = $coordinator->processEvidence($evidence2);

        // Assert separate records
        $this->assertNotEquals($deviceA->id, $deviceB->id);
        $this->assertEquals($vrfA->id, $deviceA->vrf_id);
        $this->assertEquals($vrfB->id, $deviceB->vrf_id);
        $this->assertEquals('00:11:22:33:44:55', $deviceA->mac_address);
        $this->assertEquals('AA:BB:CC:DD:EE:FF', $deviceB->mac_address);
    }

    /**
     * Test Rules 8 & 10: Contradiction penalization.
     * Conflicting evidence (e.g. OUI says TP-Link, but claim says Huawei)
     * must actively penalize confidence score rather than ignoring the conflict.
     */
    public function test_contradictory_evidence_reduces_confidence_score(): void
    {
        $confidenceEngine = app(ConfidenceEngine::class);

        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'router',
            'manufacturer' => 'Huawei',
            'model' => 'HG8145H',
            'mac_address' => '00:25:9E:01:02:03',
            'identification_status' => 'high_confidence',
            'confidence_score' => 80.0,
        ]);

        // Add supporting evidence (+40)
        DeviceEvidence::create([
            'tenant_id' => $this->tenant->id,
            'device_id' => $device->id,
            'source' => 'tr069',
            'attribute_name' => 'model',
            'attribute_value' => 'HG8145H',
            'is_contradictory' => false,
            'confidence_weight' => 40.0,
            'observed_at' => now(),
        ]);

        $scoreBefore = $confidenceEngine->calculate($device)['confidence_score'];

        // Add contradictory evidence (-30 penalty)
        DeviceEvidence::create([
            'tenant_id' => $this->tenant->id,
            'device_id' => $device->id,
            'source' => 'dhcp_fingerprint',
            'attribute_name' => 'manufacturer',
            'attribute_value' => 'TP-Link Corporation',
            'is_contradictory' => true,
            'confidence_weight' => 30.0,
            'observed_at' => now(),
        ]);

        $calcAfter = $confidenceEngine->calculate($device);
        $scoreAfter = $calcAfter['confidence_score'];

        $this->assertLessThan($scoreBefore, $scoreAfter);
        $this->assertGreaterThan(0, $calcAfter['contradiction_penalty']);
    }

    /**
     * Test Rule 45: No False Identification.
     * If model confidence is low, output "Model not verified" / "Unknown Device" rather than guessing.
     */
    public function test_low_confidence_devices_are_unknown_and_not_falsely_identified(): void
    {
        $coordinator = app(DiscoveryCoordinator::class);

        // Weak evidence: Only a MAC address with no vendor OUI match and unknown DHCP hostname
        $evidence = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'arp',
            timestamp: now()->toIso8601String(),
            ipAddress: '10.200.5.42',
            macAddress: '02:00:00:12:34:56', // Locally administered MAC
            hostname: 'host-xyz',
            model: null,
            manufacturer: null
        );

        $device = $coordinator->processEvidence($evidence);

        $this->assertEquals('unknown', $device->identification_status);
        $this->assertLessThan(50.0, (float) $device->confidence_score);
        $this->assertNull($device->device_model_id);
    }

    /**
     * Test Hardware Replacement Detection Anomaly.
     * When the same circuit / IP / subscriber sees a changed MAC and Serial number.
     */
    public function test_device_hardware_replacement_is_detected(): void
    {
        $anomalyDetector = app(AnomalyDetector::class);

        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'ont',
            'manufacturer' => 'Huawei',
            'model' => 'HG8145H',
            'ip_address' => '10.10.10.50',
            'mac_address' => '00:25:9E:AA:BB:CC',
            'serial_number' => '4857544311223344',
            'identification_status' => 'verified',
            'confidence_score' => 95.0,
        ]);

        // Incoming telemetry on same IP with different MAC and Serial (Replacement ONT installed)
        $newEvidence = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'tr069',
            timestamp: now()->toIso8601String(),
            ipAddress: '10.10.10.50',
            macAddress: '00:25:9E:FF:EE:DD',
            serialNumber: '4857544399887766',
            manufacturer: 'Huawei',
            model: 'EG8145V5'
        );

        $anomalies = $anomalyDetector->checkAnomalies($device, $newEvidence);

        $this->assertNotEmpty($anomalies);
        $replacementAnomaly = collect($anomalies)->firstWhere('type', 'DEVICE_REPLACEMENT_DETECTED');
        $this->assertNotNull($replacementAnomaly);
        $this->assertEquals('warning', $replacementAnomaly['severity']);
    }

    /**
     * Test Topology Movement Detection Anomaly.
     * When a device moves to a different OLT, PON port, or upstream interface.
     */
    public function test_device_topology_movement_is_detected(): void
    {
        $anomalyDetector = app(AnomalyDetector::class);

        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'ont',
            'manufacturer' => 'ZTE',
            'model' => 'F670L',
            'mac_address' => '00:1E:73:11:22:33',
            'serial_number' => 'ZTEGC1234567',
            'olt_id' => 1,
            'pon_id' => '1/1/1',
            'identification_status' => 'verified',
            'confidence_score' => 90.0,
        ]);

        // Moved to a different PON port (1/1/4)
        $newEvidence = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'olt_collector',
            timestamp: now()->toIso8601String(),
            macAddress: '00:1E:73:11:22:33',
            serialNumber: 'ZTEGC1234567',
            oltId: 1,
            ponId: '1/1/4'
        );

        $anomalies = $anomalyDetector->checkAnomalies($device, $newEvidence);

        $movementAnomaly = collect($anomalies)->firstWhere('type', 'DEVICE_TOPOLOGY_CHANGED');
        $this->assertNotNull($movementAnomaly);
    }

    /**
     * Test Global vs Tenant Catalog Isolation & Hierarchy.
     * Tenant overrides must take priority in display name and image, without altering global records.
     */
    public function test_tenant_catalog_overrides_isolate_from_global_catalog_and_other_tenants(): void
    {
        $imageResolver = app(DeviceImageResolver::class);

        // Global master definition
        $globalModel = DeviceModel::firstOrCreate(
            ['manufacturer' => 'Huawei', 'model' => 'HG8145H'],
            [
                'device_type' => 'ont',
                'image_url' => 'https://cdn.smartoh.net/devices/global/huawei_hg8145h.png',
                'thumbnail_url' => 'https://cdn.smartoh.net/devices/global/huawei_hg8145h_thumb.png',
                'is_global' => true,
            ]
        );

        // Tenant 1 overrides image and branding
        $tenantModel = TenantDeviceModel::create([
            'tenant_id' => $this->tenant->id,
            'global_device_model_id' => $globalModel->id,
            'custom_name' => 'Smartoh Ultra Fiber Gateway',
            'custom_image_url' => 'https://tenant1.smartoh.net/custom_ont.png',
            'custom_thumbnail_url' => 'https://tenant1.smartoh.net/custom_ont_thumb.png',
            'is_approved' => true,
        ]);

        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'ont',
            'manufacturer' => 'Huawei',
            'model' => 'HG8145H',
            'device_model_id' => $globalModel->id,
            'tenant_device_model_id' => $tenantModel->id,
            'confidence_score' => 95.0,
            'identification_status' => 'verified',
        ]);

        // Display name must reflect tenant customization
        $this->assertEquals('Smartoh Ultra Fiber Gateway', $device->display_name);

        // Resolved image must follow Rule 14 (Tier 1: Tenant-specific exact model image)
        $resolvedImage = $imageResolver->resolveImage($device, 'full');
        $this->assertEquals('https://tenant1.smartoh.net/custom_ont.png', $resolvedImage);
    }

    /**
     * Test 7-Tier Image Resolution Hierarchy Fallback.
     */
    public function test_seven_tier_image_resolution_hierarchy(): void
    {
        $imageResolver = app(DeviceImageResolver::class);

        // Unknown device with only device_type = 'switch'
        $switchDevice = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'switch',
            'identification_status' => 'unknown',
            'confidence_score' => 20.0,
        ]);

        $resolvedIcon = $imageResolver->resolveImage($switchDevice, 'thumbnail');
        $this->assertStringContainsString('generic_switch.svg', $resolvedIcon);
    }

    /**
     * Test Capability Detection and Autonomous Management Path Selection.
     */
    public function test_capability_detection_and_management_path_selection(): void
    {
        $capabilityEngine = app(CapabilityEngine::class);
        $pathSelector = app(ManagementPathSelector::class);

        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'router',
            'manufacturer' => 'MikroTik',
            'model' => 'CCR2004-16G-2S+',
            'mac_address' => '48:8F:5A:11:22:33',
            'vpn_peer_id' => 'wg-peer-01',
            'identification_status' => 'verified',
            'confidence_score' => 95.0,
        ]);

        $caps = $capabilityEngine->detectCapabilities($device, ['vpn_connected' => true]);
        $this->assertEquals('verified', $caps['mikrotik_api']);
        $this->assertEquals('connected', $caps['wireguard_vpn']);

        $paths = $pathSelector->selectPaths($device, $caps);
        $this->assertEquals('mikrotik_api', $paths['primary_management_protocol']);
        $this->assertEquals('wireguard_vpn', $paths['fallback_management_protocol']);
    }

    /**
     * Test Deterministic Fingerprint Generation.
     */
    public function test_deterministic_fingerprint_generation(): void
    {
        $fingerprintEngine = app(FingerprintEngine::class);

        $evidence = new DeviceEvidenceData(
            tenantId: $this->tenant->id,
            source: 'tr069',
            timestamp: now()->toIso8601String(),
            ipAddress: '10.10.20.5',
            macAddress: '00:25:9E:AA:BB:CC',
            serialNumber: 'SN48575443001',
            manufacturer: 'Huawei',
            model: 'HG8145H'
        );

        $hash1 = $fingerprintEngine->generate($evidence);
        $hash2 = $fingerprintEngine->generate($evidence);

        $this->assertEquals(64, strlen($hash1)); // sha256 hex string
        $this->assertEquals($hash1, $hash2);
    }

    /**
     * Test NOC UI Web Routes.
     */
    public function test_noc_inventory_web_routes_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('udie.index'));
        $response->assertStatus(200);
        $response->assertSee('Universal Device Intelligence Engine');

        $responseLab = $this->actingAs($this->user)->get(route('udie.unknown'));
        $responseLab->assertStatus(200);
        $responseLab->assertSee('Unknown Device Laboratory');

        $responseModels = $this->actingAs($this->user)->get(route('udie.models'));
        $responseModels->assertStatus(200);
        $responseModels->assertSee('Device Model Intelligence Registry');
    }

    /**
     * Test UDIE REST API Endpoints.
     */
    public function test_udie_rest_api_endpoints_work(): void
    {
        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'router',
            'manufacturer' => 'MikroTik',
            'model' => 'RB4011',
            'ip_address' => '192.168.88.1',
            'mac_address' => 'DC:2C:6E:00:11:22',
            'identification_status' => 'verified',
            'confidence_score' => 92.0,
        ]);

        // 1. GET /api/udie/devices
        $responseList = $this->actingAs($this->user)->getJson('/api/udie/devices');
        $responseList->assertStatus(200);
        $responseList->assertJsonStructure(['data', 'pagination' => ['current_page', 'total']]);

        // 2. GET /api/udie/devices/{id}
        $responseDetail = $this->actingAs($this->user)->getJson("/api/udie/devices/{$device->id}");
        $responseDetail->assertStatus(200);
        $responseDetail->assertJsonPath('data.id', $device->id);

        // 3. POST /api/udie/evidence/ingest
        $ingestPayload = [
            'source' => 'tr069',
            'ip_address' => '10.50.1.10',
            'mac_address' => '00:25:9E:77:88:99',
            'serial_number' => '48575443ABCDEF01',
            'manufacturer' => 'Huawei',
            'model' => 'EG8145V5',
            'device_type' => 'ont',
        ];

        $responseIngest = $this->actingAs($this->user)->postJson('/api/udie/evidence/ingest', $ingestPayload);
        $responseIngest->assertStatus(200);
        $responseIngest->assertJsonPath('success', true);
        $responseIngest->assertJsonPath('device.manufacturer', 'Huawei');
    }

    /**
     * Test Manual Device Identification Flow.
     */
    public function test_manual_device_identification_and_verification(): void
    {
        $device = Device::create([
            'tenant_id' => $this->tenant->id,
            'device_type' => 'router',
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'identification_status' => 'unknown',
            'confidence_score' => 15.0,
        ]);

        $response = $this->actingAs($this->user)->post(route('udie.devices.identify', $device), [
            'manufacturer' => 'Cisco',
            'model' => 'Catalyst 2960',
            'device_type' => 'switch',
            'catalog_target' => 'tenant',
            'custom_name' => 'Aggregation Switch 2960',
        ]);

        $response->assertSessionHas('success');

        $device->refresh();
        $this->assertEquals('verified', $device->identification_status);
        $this->assertEquals(100.0, (float) $device->confidence_score);
        $this->assertEquals('Cisco', $device->manufacturer);
        $this->assertEquals('Catalyst 2960', $device->model);
        $this->assertEquals('switch', $device->device_type);
    }

    /**
     * Test Artisan udie:discover CLI Command.
     */
    public function test_artisan_udie_discover_command_executes_successfully(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('udie:discover', [
            '--tenant' => $this->tenant->id,
            '--source' => 'all',
        ]);

        $this->assertEquals(0, $exitCode);
        $output = \Illuminate\Support\Facades\Artisan::output();
        $this->assertStringContainsString('Initiating UDIE Discovery sweep', $output);
        $this->assertStringContainsString('UDIE Discovery complete', $output);
    }

    /**
     * Test Discovery Job records audit statistics.
     */
    public function test_discovery_job_records_stats_in_database(): void
    {
        $job = DiscoveryJob::create([
            'tenant_id' => $this->tenant->id,
            'source' => 'all',
            'target_scope' => 'All Enrolled Network Gateways',
            'status' => 'pending',
        ]);

        \App\Jobs\RunDeviceDiscoveryJob::dispatchSync($job->id);

        $job->refresh();
        $this->assertEquals('completed', $job->status);
        $this->assertNotNull($job->completed_at);
        $this->assertGreaterThanOrEqual(1, $job->devices_discovered_count);
    }
}
