<?php

namespace Tests\Feature;

use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\MikroTikDevice;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class NocCapacityFeatureTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        return User::where('role', 'super_admin')->first() 
            ?? User::first();
    }

    public function test_noc_section_renders_isp_capacity_and_realtime_metrics(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/noc');
        $response->assertStatus(200);

        // Check for the newly added capacity remaining and real-time pulled sections
        $response->assertSee('Total Upstream Capacity');
        $response->assertSee('Capacity Sold to Clients');
        $response->assertSee('Capacity Remaining to Sell');
        $response->assertSee('Real-Time Pulled');
        $response->assertSee('Commercial Capacity Headroom');
        $response->assertSee('Core Gateways &amp; WAN Interfaces Telemetry', false);
        $response->assertSee('Live WAN Bandwidth Pulled vs. Capacity Ceiling');
    }

    public function test_noc_capacity_telemetry_endpoint_returns_json_metrics(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->getJson('/noc/capacity/telemetry');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'tenant' => ['id', 'name'],
                'total_capacity_mbps',
                'total_capacity_formatted',
                'sold_download_mbps',
                'sold_download_formatted',
                'remaining_capacity_mbps',
                'remaining_capacity_formatted',
                'remaining_pct',
                'headroom_status' => ['level', 'badge', 'color', 'text'],
                'sales_projections',
                'pulled_rx_mbps',
                'pulled_tx_mbps',
                'pulled_total_mbps',
                'pulled_utilization_pct',
                'routers_telemetry',
                'live_history',
            ]
        ]);

        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertGreaterThan(0, $json['metrics']['total_capacity_mbps']);
        $this->assertGreaterThanOrEqual(0, $json['metrics']['remaining_capacity_mbps']);
        $this->assertGreaterThanOrEqual(0, $json['metrics']['pulled_total_mbps']);
    }

    public function test_isp_can_update_contracted_upstream_capacity(): void
    {
        $user = $this->getSuperAdmin();

        $updateResponse = $this->actingAs($user)->post('/noc/capacity/update', [
            'capacity_mbps' => 25000, // 25 Gbps
        ]);

        $updateResponse->assertSessionHas('success');

        // Check that telemetry now reflects 25 Gbps
        $telemetryResponse = $this->actingAs($user)->getJson('/noc/capacity/telemetry');
        $telemetryResponse->assertStatus(200);
        $this->assertEquals(25000, $telemetryResponse->json('metrics.total_capacity_mbps'));
        $this->assertEquals('25 Gbps', $telemetryResponse->json('metrics.total_capacity_formatted'));
    }

    public function test_noc_ai_copilot_answers_capacity_queries(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->getJson('/noc/ask-ai?query=' . urlencode('How much capacity is remaining to sell to clients?'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'answer']);
        $answer = $response->json('answer');

        $this->assertStringContainsString('Capacity Remaining to Sell', $answer);
        $this->assertStringContainsString('Real-Time Capacity Pulled', $answer);
    }
}
