<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\FiberInfrastructure;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KenyaGisMapFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $adminA;
    protected User $techA;
    protected User $billingA;
    protected User $adminB;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Savannah Fiber Kenya Ltd',
            'subdomain' => 'savannah-ke',
            'plan_tier' => 'growth',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Mombasa Coastal Metro ISP',
            'subdomain' => 'coastal-ke',
            'plan_tier' => 'enterprise',
            'status' => 'active',
        ]);

        $this->adminA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin Savannah',
            'email' => 'admin@savannah-ke.co.ke',
            'password' => bcrypt('password'),
            'role' => 'isp_admin',
        ]);

        $this->techA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Technician Savannah',
            'email' => 'tech@savannah-ke.co.ke',
            'password' => bcrypt('password'),
            'role' => 'technician',
        ]);

        $this->billingA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Billing Savannah',
            'email' => 'billing@savannah-ke.co.ke',
            'password' => bcrypt('password'),
            'role' => 'billing_officer',
        ]);

        $this->adminB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Admin Coastal',
            'email' => 'admin@coastal-ke.co.ke',
            'password' => bcrypt('password'),
            'role' => 'isp_admin',
        ]);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@smartoh.co.ke',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);
    }

    public function test_kenya_map_renders_full_country_view_47_counties_and_zoom_levels(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('gis.map'));

        $response->assertStatus(200);
        $response->assertSee('Kenya National GIS');
        $response->assertSee('EPSG:4326');
        $response->assertSee('47 Constitutional Counties');
        $response->assertSee('Kenya Full View (Z6)');
        $response->assertSee('County Level (Z9)');
        $response->assertSee('Town / Estate (Z13)');
        $response->assertSee('Network Site / Drop (Z17)');

        $geoJsonRes = $this->actingAs($this->adminA)->getJson(route('gis.kenya-geojson'));
        $geoJsonRes->assertStatus(200)
            ->assertJsonPath('crs', 'EPSG:4326')
            ->assertJsonPath('counties_geojson.metadata.counties_count', 47);

        // Verify 1 national boundary + 47 county features = 48 GeoJSON features
        $features = $geoJsonRes->json('counties_geojson.features');
        $this->assertCount(48, $features);
    }

    public function test_authorized_staff_can_create_edit_move_and_persist_kenya_assets_and_fiber_routes(): void
    {
        // 1. Technician creates a Splice Closure in Nairobi Westlands
        $createRes = $this->actingAs($this->techA)->postJson(route('gis.assets.store'), [
            'name' => 'Westlands Waiyaki Way Dome Closure',
            'code' => 'SPC-WST-01',
            'asset_subtype' => 'splice_closure',
            'latitude' => -1.2683,
            'longitude' => 36.8111,
            'total_ports' => 48,
            'used_ports' => 24,
            'status' => 'active',
            'coordinate_source' => 'gps_survey',
        ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('asset.code', 'SPC-WST-01')
            ->assertJsonPath('asset.asset_subtype', 'splice_closure')
            ->assertJsonPath('asset.county', 'Nairobi');

        $assetId = $createRes->json('asset.id');

        // 2. Technician creates a multi-vertex Fiber Route from Nairobi to Ruiru (Kiambu)
        $routeRes = $this->actingAs($this->techA)->postJson(route('gis.assets.store'), [
            'name' => 'Nairobi - Ruiru Thika Superhighway 96C Trunk',
            'code' => 'FBR-THK-96C',
            'asset_subtype' => 'fiber_route',
            'coordinates_json' => [
                [-1.286389, 36.817223],
                [-1.219500, 36.888500],
                [-1.148600, 36.960600],
            ],
            'status' => 'active',
        ]);

        $routeRes->assertStatus(201)
            ->assertJsonPath('asset.asset_subtype', 'fiber_route');

        $this->assertGreaterThan(20000, (float) $routeRes->json('asset.length_meters'));

        // 3. Move asset to Nakuru CBD and verify county auto-updates and persists on page refresh
        $updateRes = $this->actingAs($this->techA)->putJson(route('gis.assets.update', $assetId), [
            'name' => 'Nakuru Kenyatta Ave Splice Closure',
            'latitude' => -0.303099,
            'longitude' => 36.080025,
            'status' => 'maintenance',
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('asset.county', 'Nakuru')
            ->assertJsonPath('asset.status', 'maintenance');

        // Verify persisted coordinates appear on refreshed map page
        $refreshMap = $this->actingAs($this->adminA)->get(route('gis.map'));
        $refreshMap->assertStatus(200)
            ->assertSee('Nakuru Kenyatta Ave Splice Closure')
            ->assertSee('FBR-THK-96C');

        // 4. Reject coordinates outside Kenya (e.g., London 51.5074, -0.1278)
        $invalidRes = $this->actingAs($this->techA)->postJson(route('gis.assets.store'), [
            'name' => 'Invalid Overseas Node',
            'asset_subtype' => 'pop',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'status' => 'active',
        ]);
        $invalidRes->assertStatus(422);
    }

    public function test_search_supports_counties_towns_coordinates_and_assets_without_inventing_locations(): void
    {
        FiberInfrastructure::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Kisumu Milimani OLT Chassis',
            'code' => 'OLT-KSM-99',
            'type' => 'pop',
            'asset_subtype' => 'olt',
            'county' => 'Kisumu',
            'latitude' => -0.0917,
            'longitude' => 34.7680,
            'status' => 'active',
        ]);

        // 1. Search by County
        $countyRes = $this->actingAs($this->adminA)->getJson(route('gis.search', ['q' => 'Mombasa']));
        $countyRes->assertStatus(200);
        $this->assertNotEmpty($countyRes->json('results'));
        $this->assertEquals('Mombasa County', $countyRes->json('results.0.name'));

        // 2. Search by Coordinate Pair inside Kenya
        $coordRes = $this->actingAs($this->adminA)->getJson(route('gis.search', ['q' => '-1.286389, 36.817223']));
        $coordRes->assertStatus(200)
            ->assertJsonPath('results.0.category', 'coordinate')
            ->assertJsonPath('results.0.verified_exact', true);

        // 3. Search by Tenant Network Asset Code
        $assetSearch = $this->actingAs($this->adminA)->getJson(route('gis.search', ['q' => 'OLT-KSM-99']));
        $assetSearch->assertStatus(200)
            ->assertJsonPath('results.0.category', 'network_asset')
            ->assertJsonPath('results.0.asset_subtype', 'olt');

        // 4. Simulate external geocoder unavailable/empty for nonexistent place — must NOT invent coordinates
        Http::fake([
            '*' => Http::response([], 503),
        ]);

        $unverifiedRes = $this->actingAs($this->adminA)->getJson(route('gis.search', ['q' => 'NonExistentAtlantisEstateXYZ999']));
        $unverifiedRes->assertStatus(200)
            ->assertJsonCount(0, 'results')
            ->assertJsonPath('external_geocoder_status', 'unavailable_fallback_active');
    }

    public function test_strict_multitenant_isolation_and_super_admin_audited_support_workflow(): void
    {
        // Tenant B private asset in Mombasa
        $secretAssetB = FiberInfrastructure::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Coastal Private Subsea Landing PoP',
            'code' => 'POP-COAST-SECRET',
            'type' => 'pop',
            'asset_subtype' => 'pop',
            'county' => 'Mombasa',
            'latitude' => -4.0435,
            'longitude' => 39.6682,
            'status' => 'active',
        ]);

        // 1. Tenant A Admin must NOT see Tenant B's asset on map or in search
        $mapA = $this->actingAs($this->adminA)->get(route('gis.map'));
        $mapA->assertStatus(200)->assertDontSee('POP-COAST-SECRET');

        $searchA = $this->actingAs($this->adminA)->getJson(route('gis.search', ['q' => 'POP-COAST-SECRET']));
        $this->assertEquals(0, $searchA->json('asset_matches_count'));

        // 2. Tenant A Admin cannot update or delete Tenant B's asset
        $this->actingAs($this->adminA)->putJson(route('gis.assets.update', $secretAssetB->id), [
            'name' => 'Hijacked PoP',
        ])->assertStatus(404);

        $this->actingAs($this->adminA)->deleteJson(route('gis.assets.destroy', $secretAssetB->id))
            ->assertStatus(404);

        // 3. Billing officer is read-only (cannot create or delete assets)
        $this->actingAs($this->billingA)->postJson(route('gis.assets.store'), [
            'name' => 'Unauthorized Billing Node',
            'asset_subtype' => 'pop',
            'latitude' => -1.286389,
            'longitude' => 36.817223,
            'status' => 'active',
        ])->assertStatus(403);

        // 4. Super Admin sees Platform Overview by default (does NOT leak Tenant B private asset)
        $superOverview = $this->actingAs($this->superAdmin)->get(route('gis.map'));
        $superOverview->assertStatus(200)
            ->assertSee('Super Admin Platform Overview')
            ->assertDontSee('POP-COAST-SECRET');

        // 5. Super Admin initiates explicitly authorized & audited support session for Tenant B
        $grantRes = $this->actingAs($this->superAdmin)->post(route('gis.support.grant'), [
            'tenant_id' => $this->tenantB->id,
            'support_reason' => 'NOC Ticket #8841 Coastal Fiber Cut Investigation',
        ]);
        $grantRes->assertRedirect(route('gis.map'));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->superAdmin->id,
            'event' => 'gis.support_access.granted',
        ]);

        // Now Super Admin can inspect Tenant B's private asset during audited session
        $superSupportMap = $this->actingAs($this->superAdmin)->get(route('gis.map'));
        $superSupportMap->assertStatus(200)
            ->assertSee('Audited Support Session')
            ->assertSee('POP-COAST-SECRET');

        // Revoke support session and verify return to isolated platform overview
        $this->actingAs($this->superAdmin)->post(route('gis.support.revoke'))
            ->assertRedirect(route('gis.map'));

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->superAdmin->id,
            'event' => 'gis.support_access.revoked',
        ]);
    }
}
