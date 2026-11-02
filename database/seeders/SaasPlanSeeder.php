<?php

namespace Database\Seeders;

use App\Models\SaasPlan;
use App\Models\SaasPlanVersion;
use Illuminate\Database\Seeder;

class SaasPlanSeeder extends Seeder
{
    public static function defaultPlansData(): array
    {
        return [
            [
                'name'          => 'Starter ISP',
                'slug'          => 'starter',
                'description'   => 'Ideal for emerging local WISPs and neighborhood fiber operators launching automated operations.',
                'monthly_price' => 5000.00,
                'annual_price'  => 50000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 1,
                'features'      => [
                    'tier_level'                => 1,
                    'tier_badge'                => 'Essential Launch Tier',
                    'sla_tier'                  => 'Standard 8x5 Business SLA (12h Response)',
                    'support_channel'           => 'Ticket & Email Support',
                    'max_subscribers'           => 300,
                    'max_routers'               => 3,
                    'max_staff'                 => 3,
                    'max_sites'                 => 1,
                    'max_cpes'                  => 100,
                    'storage_gb'                => 10,
                    'has_mpesa_daraja'          => true,
                    'has_hotspot_vouchers'      => true,
                    'has_wireguard_tunnels'     => true,
                    'has_adie_discovery'        => true,
                    'has_sms_reminders'         => true,
                    'has_api_access'            => false,
                    'has_advanced_noc'          => false,
                    'has_white_label'           => false,
                    'has_genieacs_cwmp'         => false,
                    'has_gis_fiber_maps'        => false,
                    'has_bgp_ospf'              => false,
                    'has_wholesale_market'      => false,
                    'has_multi_site_accounting' => false,
                    'has_custom_domain'         => false,
                    'has_dedicated_sla'         => false,
                    'included_benefits'         => [
                        'Up to 300 Active PPPoE / Hotspot Subscribers',
                        'Up to 3 Core MikroTik RouterOS v7 NAS Gateways',
                        '3 Staff Accounts & 1 Operating Site Branch',
                        'Up to 100 Managed CPE Devices & 10 GB Telemetry Vault',
                        'Automated M-Pesa Daraja STK Push & C2B Reconciliation',
                        'Hotspot Voucher Generator & Subscriber Self-Service Portal',
                        'Basic UDIE Device Discovery & WireGuard VPN Tunnels',
                        'Automated SMS & Email Dunning Reminders',
                        'Standard 8x5 Helpdesk Support (12-Hour SLA)',
                    ],
                    'locked_benefits'           => [
                        'TR-069 GenieACS & GPON OLT Optical Provisioning',
                        'Interactive GIS Fiber Topology & Splicing Maps',
                        'AI NOC Copilot, Capacity Telemetry & Rogue Sniffer',
                        'White-Label Subscriber Portal & Custom CNAME Domain',
                        'REST API, Pesapal/PayPal & Bank Statement Auto-Match',
                        'Autonomous BGP / OSPF Routing & Wholesale Marketplace',
                    ],
                ],
            ],
            [
                'name'          => 'Pro Growth ISP',
                'slug'          => 'pro',
                'description'   => 'Engineered for scaling WISPs and FTTH operators needing GPON OLTs, TR-069 ACS, GIS maps, and multi-site control.',
                'monthly_price' => 14000.00,
                'annual_price'  => 140000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 2,
                'features'      => [
                    'tier_level'                => 2,
                    'tier_badge'                => 'Most Popular for Scaling ISPs',
                    'sla_tier'                  => 'Priority 24/7 NOC Support (4h Response)',
                    'support_channel'           => 'Priority WhatsApp, Phone & Ticket Desk',
                    'max_subscribers'           => 1500,
                    'max_routers'               => 10,
                    'max_staff'                 => 10,
                    'max_sites'                 => 5,
                    'max_cpes'                  => 1000,
                    'storage_gb'                => 50,
                    'has_mpesa_daraja'          => true,
                    'has_hotspot_vouchers'      => true,
                    'has_wireguard_tunnels'     => true,
                    'has_adie_discovery'        => true,
                    'has_sms_reminders'         => true,
                    'has_api_access'            => true,
                    'has_advanced_noc'          => true,
                    'has_white_label'           => true,
                    'has_genieacs_cwmp'         => true,
                    'has_gis_fiber_maps'        => true,
                    'has_bgp_ospf'              => false,
                    'has_wholesale_market'      => false,
                    'has_multi_site_accounting' => false,
                    'has_custom_domain'         => false,
                    'has_dedicated_sla'         => false,
                    'included_benefits'         => [
                        'Up to 1,500 Active Subscribers across 5 POP Branches',
                        'Up to 10 Core MikroTik Gateways & Huawei/ZTE GPON OLTs',
                        '10 Staff & Field Technician Accounts with Work Orders',
                        'Up to 1,000 TR-069 CWMP CPEs & 50 GB Cloud Backups',
                        'Full TR-069 GenieACS Auto-Provisioning & Optical Power Tests',
                        'Interactive GIS Fiber Map, FAT Box Core Planner & Dispatch',
                        'AI NOC Copilot, Live Capacity Telemetry & Rogue DHCP Sniffer',
                        'White-Label Brand Colors, WhatsApp Dunning & REST API Access',
                        'Multi-Gateway Collections (M-Pesa, Pesapal, PayPal, Bank CSV)',
                        'Priority 24/7 NOC Support (4-Hour Response SLA)',
                    ],
                    'locked_benefits'           => [
                        'Autonomous BGP & OSPF Core Routing Orchestration',
                        'Wholesale Bandwidth Marketplace & Reseller Wallet Pools',
                        'Multi-Site Double-Entry General Ledger & P&L Accounting',
                        'Custom CNAME Domain & Dedicated Carrier Account Engineer',
                    ],
                ],
            ],
            [
                'name'          => 'Enterprise Tier-2 Core',
                'slug'          => 'enterprise',
                'description'   => 'Carrier-grade autonomous command center with BGP/OSPF routing, wholesale marketplace, multi-site P&L, and dedicated SLA.',
                'monthly_price' => 32000.00,
                'annual_price'  => 320000.00,
                'currency'      => 'KES',
                'trial_days'    => 7,
                'sort_order'    => 3,
                'features'      => [
                    'tier_level'                => 3,
                    'tier_badge'                => 'Full Carrier & Telecom Suite',
                    'sla_tier'                  => 'Dedicated 99.99% Carrier SLA (30m Response)',
                    'support_channel'           => '24/7 Dedicated NOC Engineer & Direct Line',
                    'max_subscribers'           => 10000,
                    'max_routers'               => 50,
                    'max_staff'                 => 50,
                    'max_sites'                 => 25,
                    'max_cpes'                  => 10000,
                    'storage_gb'                => 250,
                    'has_mpesa_daraja'          => true,
                    'has_hotspot_vouchers'      => true,
                    'has_wireguard_tunnels'     => true,
                    'has_adie_discovery'        => true,
                    'has_sms_reminders'         => true,
                    'has_api_access'            => true,
                    'has_advanced_noc'          => true,
                    'has_white_label'           => true,
                    'has_genieacs_cwmp'         => true,
                    'has_gis_fiber_maps'        => true,
                    'has_bgp_ospf'              => true,
                    'has_wholesale_market'      => true,
                    'has_multi_site_accounting' => true,
                    'has_custom_domain'         => true,
                    'has_dedicated_sla'         => true,
                    'included_benefits'         => [
                        'Up to 10,000+ Subscribers & 25 Regional POP Sites',
                        'Up to 50 Core MikroTik CCR Routers & Multi-Vendor OLTs',
                        '50 Staff, NOC & Finance Accounts with Granular RBAC',
                        '10,000 TR-069 / TR-181 USP CPEs & 250 GB Encrypted S3 Vault',
                        'Autonomous BGP & OSPF Dynamic Routing & Upstream Failover',
                        'Wholesale Bandwidth Marketplace & Reseller Capacity Pools',
                        'Multi-Site Double-Entry General Ledger, Trial Balance & P&L',
                        'Full White-Label Custom CNAME Domain & Sub-ISP Franchising',
                        'Predictive AI Optical Failure Maintenance & Auto-Quarantine',
                        'Dedicated 24/7/365 Carrier SLA (30-Min Response) & Engineer',
                    ],
                    'locked_benefits'           => [],
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::defaultPlansData() as $planData) {
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
                    'changelog'     => 'Initial version 1.0 release with tiered ISP benefits',
                    'is_current'    => true,
                ]
            );
        }
    }
}
