<?php

namespace Database\Seeders;

use App\Models\SaasPlan;
use App\Models\SaasPlanVersion;
use Illuminate\Database\Seeder;

class SaasPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter ISP',
                'slug'          => 'starter',
                'description'   => 'Ideal for emerging local WISPs and neighborhood fiber operators launching operations.',
                'monthly_price' => 5000.00,
                'annual_price'  => 50000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 1,
                'features'      => [
                    'max_subscribers'       => 250,
                    'max_routers'           => 3,
                    'max_staff'             => 3,
                    'max_sites'             => 1,
                    'has_api_access'        => false,
                    'has_adie_discovery'    => true,
                    'has_advanced_noc'      => false,
                    'has_white_label'       => false,
                    'has_wireguard_tunnels' => true,
                    'has_mpesa_daraja'      => true,
                    'has_hotspot_vouchers'  => true,
                    'has_genieacs_cwmp'     => false,
                    'has_gis_fiber_maps'    => false,
                ],
            ],
            [
                'name'          => 'Pro ISP',
                'slug'          => 'pro',
                'description'   => 'Engineered for scaling ISPs needing high-availability OLTs, GenieACS, and multi-branch management.',
                'monthly_price' => 14000.00,
                'annual_price'  => 140000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 2,
                'features'      => [
                    'max_subscribers'       => 1500,
                    'max_routers'           => 10,
                    'max_staff'             => 10,
                    'max_sites'             => 5,
                    'has_api_access'        => true,
                    'has_adie_discovery'    => true,
                    'has_advanced_noc'      => true,
                    'has_white_label'       => true,
                    'has_wireguard_tunnels' => true,
                    'has_mpesa_daraja'      => true,
                    'has_hotspot_vouchers'  => true,
                    'has_genieacs_cwmp'     => true,
                    'has_gis_fiber_maps'    => true,
                ],
            ],
            [
                'name'          => 'Enterprise Tier-2 Core',
                'slug'          => 'enterprise',
                'description'   => 'Carrier-grade autonomous command center with BGP/OSPF, multi-site accounting, and dedicated SLA.',
                'monthly_price' => 32000.00,
                'annual_price'  => 320000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 3,
                'features'      => [
                    'max_subscribers'           => 10000,
                    'max_routers'               => 50,
                    'max_staff'                 => 50,
                    'max_sites'                 => 25,
                    'has_api_access'            => true,
                    'has_adie_discovery'        => true,
                    'has_advanced_noc'          => true,
                    'has_white_label'           => true,
                    'has_wireguard_tunnels'     => true,
                    'has_mpesa_daraja'          => true,
                    'has_hotspot_vouchers'      => true,
                    'has_genieacs_cwmp'         => true,
                    'has_gis_fiber_maps'        => true,
                    'has_dedicated_sla'         => true,
                    'has_custom_domain'         => true,
                    'has_multi_site_accounting' => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = SaasPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            SaasPlanVersion::updateOrCreate(
                [
                    'saas_plan_id'   => $plan->id,
                    'version_number' => 1,
                ],
                [
                    'features'      => $features,
                    'monthly_price' => $plan->monthly_price,
                    'annual_price'  => $plan->annual_price,
                    'currency'      => $plan->currency,
                    'changelog'     => 'Initial version 1.0 release',
                    'is_current'    => true,
                ]
            );
        }
    }
}
