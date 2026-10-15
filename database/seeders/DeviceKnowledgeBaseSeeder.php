<?php

namespace Database\Seeders;

use App\Models\DeviceModel;
use App\Models\NetworkSegment;
use App\Models\Tenant;
use App\Models\TenantDeviceModel;
use App\Models\Vrf;
use Illuminate\Database\Seeder;

class DeviceKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Global Device Models
        $models = [
            [
                'manufacturer' => 'Huawei',
                'model' => 'HG8145H',
                'product_class' => 'HG8145H',
                'device_type' => 'ont',
                'image_url' => '/images/devices/generic_ont.svg',
                'thumbnail_url' => '/images/devices/generic_ont.svg',
                'icon_url' => '/images/devices/generic_ont.svg',
                'identification_signatures' => [
                    'ouis' => ['00259E', '485702', 'F86E05'],
                    'product_classes' => ['HG8145H', 'EchoLife HG8145H'],
                    'hostname_patterns' => ['HG8145H', 'HuaweiHome'],
                ],
                'capabilities' => ['tr069', 'tr181_usp', 'olt_api', 'wireguard_vpn', 'lan_http'],
                'supported_protocols' => ['TR-069', 'TR-181', 'OMCI', 'WireGuard', 'HTTP'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'Huawei',
                'model' => 'EG8145V5',
                'product_class' => 'EG8145V5',
                'device_type' => 'ont',
                'image_url' => '/images/devices/generic_ont.svg',
                'thumbnail_url' => '/images/devices/generic_ont.svg',
                'icon_url' => '/images/devices/generic_ont.svg',
                'identification_signatures' => [
                    'ouis' => ['00259E', '485702'],
                    'product_classes' => ['EG8145V5', 'OptiXstar EG8145V5'],
                    'hostname_patterns' => ['EG8145V5'],
                ],
                'capabilities' => ['tr069', 'tr181_usp', 'olt_api', 'lan_http'],
                'supported_protocols' => ['TR-069', 'TR-181', 'OMCI'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'ZTE',
                'model' => 'F670L',
                'product_class' => 'F670L',
                'device_type' => 'ont',
                'image_url' => '/images/devices/generic_ont.svg',
                'thumbnail_url' => '/images/devices/generic_ont.svg',
                'icon_url' => '/images/devices/generic_ont.svg',
                'identification_signatures' => [
                    'ouis' => ['085531', '74B57E', '842615'],
                    'product_classes' => ['ZXHN F670L', 'F670L'],
                    'hostname_patterns' => ['ZTE-F670L', 'ZXHN'],
                ],
                'capabilities' => ['tr069', 'tr181_usp', 'olt_api', 'lan_http'],
                'supported_protocols' => ['TR-069', 'TR-181', 'OMCI'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'MikroTik',
                'model' => 'RB4011iGS+5HacQ2HnD-IN',
                'product_class' => 'RB4011',
                'device_type' => 'router',
                'image_url' => '/images/devices/generic_router.svg',
                'thumbnail_url' => '/images/devices/generic_router.svg',
                'icon_url' => '/images/devices/generic_router.svg',
                'identification_signatures' => [
                    'ouis' => ['488D36', '6C3B6B', 'B869F4', 'D4CA6D'],
                    'product_classes' => ['RB4011', 'RB4011iGS+'],
                    'hostname_patterns' => ['RB4011', 'MikroTik-RB4011'],
                ],
                'capabilities' => ['mikrotik_api', 'wireguard_vpn', 'snmp', 'lan_http'],
                'supported_protocols' => ['RouterOS API', 'WireGuard', 'SNMP v2c/v3', 'SSH'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'MikroTik',
                'model' => 'CCR2004-16G-2S+',
                'product_class' => 'CCR2004',
                'device_type' => 'router',
                'image_url' => '/images/devices/generic_router.svg',
                'thumbnail_url' => '/images/devices/generic_router.svg',
                'icon_url' => '/images/devices/generic_router.svg',
                'identification_signatures' => [
                    'ouis' => ['488D36', 'D4CA6D', 'B869F4'],
                    'product_classes' => ['CCR2004', 'CCR2004-16G-2S+'],
                    'hostname_patterns' => ['CCR2004', 'Core-CCR2004'],
                ],
                'capabilities' => ['mikrotik_api', 'wireguard_vpn', 'snmp', 'lan_http'],
                'supported_protocols' => ['RouterOS API', 'WireGuard', 'SNMP', 'BGP', 'OSPF'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'MikroTik',
                'model' => 'CCR2216-16G-2S+',
                'product_class' => 'CCR2216',
                'device_type' => 'router',
                'image_url' => '/images/devices/generic_router.svg',
                'thumbnail_url' => '/images/devices/generic_router.svg',
                'icon_url' => '/images/devices/generic_router.svg',
                'identification_signatures' => [
                    'ouis' => ['488D36', 'D4CA6D'],
                    'product_classes' => ['CCR2216'],
                    'hostname_patterns' => ['CCR2216'],
                ],
                'capabilities' => ['mikrotik_api', 'wireguard_vpn', 'snmp', 'lan_http'],
                'supported_protocols' => ['RouterOS API', 'WireGuard', 'SNMP', 'BGP', 'OSPF'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'TP-Link',
                'model' => 'Archer C6',
                'product_class' => 'Archer C6',
                'device_type' => 'router',
                'image_url' => '/images/devices/generic_router.svg',
                'thumbnail_url' => '/images/devices/generic_router.svg',
                'icon_url' => '/images/devices/generic_router.svg',
                'identification_signatures' => [
                    'ouis' => ['50D4F7', '60A44C', '74DA38'],
                    'product_classes' => ['Archer C6', 'Archer_C6'],
                    'hostname_patterns' => ['Archer_C6', 'TP-Link'],
                ],
                'capabilities' => ['tr069', 'tr181_usp', 'lan_http'],
                'supported_protocols' => ['TR-069', 'HTTP'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'VSOL',
                'model' => 'V2801SG',
                'product_class' => 'V2801SG',
                'device_type' => 'onu',
                'image_url' => '/images/devices/generic_ont.svg',
                'thumbnail_url' => '/images/devices/generic_ont.svg',
                'icon_url' => '/images/devices/generic_ont.svg',
                'identification_signatures' => [
                    'ouis' => ['E848B8'],
                    'product_classes' => ['V2801SG', 'XPON ONU'],
                    'hostname_patterns' => ['VSOL', 'V2801SG'],
                ],
                'capabilities' => ['olt_api', 'lan_http'],
                'supported_protocols' => ['OMCI', 'OAM'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'Huawei',
                'model' => 'SmartAX MA5800-X7',
                'product_class' => 'MA5800-X7',
                'device_type' => 'olt',
                'image_url' => '/images/devices/generic_olt.svg',
                'thumbnail_url' => '/images/devices/generic_olt.svg',
                'icon_url' => '/images/devices/generic_olt.svg',
                'identification_signatures' => [
                    'ouis' => ['00259E', '485702'],
                    'product_classes' => ['MA5800', 'MA5800-X7'],
                    'hostname_patterns' => ['MA5800'],
                ],
                'capabilities' => ['olt_api', 'snmp', 'lan_http'],
                'supported_protocols' => ['SNMP', 'SSH', 'TELNET'],
                'is_global' => true,
            ],
            [
                'manufacturer' => 'Cisco',
                'model' => 'Catalyst 2960-X',
                'product_class' => 'WS-C2960X-48TS-L',
                'device_type' => 'switch',
                'image_url' => '/images/devices/generic_switch.svg',
                'thumbnail_url' => '/images/devices/generic_switch.svg',
                'icon_url' => '/images/devices/generic_switch.svg',
                'identification_signatures' => [
                    'ouis' => ['00180A', '0024B2'],
                    'product_classes' => ['C2960X', 'Catalyst 2960'],
                    'hostname_patterns' => ['c2960', 'Switch'],
                ],
                'capabilities' => ['snmp', 'lan_http'],
                'supported_protocols' => ['SNMP v2c/v3', 'SSH', 'NETCONF'],
                'is_global' => true,
            ],
        ];

        foreach ($models as $m) {
            DeviceModel::updateOrCreate(
                ['manufacturer' => $m['manufacturer'], 'model' => $m['model']],
                $m
            );
        }

        // 2. Seed VRFs and Network Segments for Tenants
        $tenants = Tenant::all();
        foreach ($tenants as $t) {
            $vrfA = Vrf::firstOrCreate(
                ['tenant_id' => $t->id, 'name' => 'VRF-CUSTOMER-A'],
                ['route_distinguisher' => "65000:{$t->id}01", 'description' => 'Customer A Residential Segment']
            );

            $vrfB = Vrf::firstOrCreate(
                ['tenant_id' => $t->id, 'name' => 'VRF-CUSTOMER-B'],
                ['route_distinguisher' => "65000:{$t->id}02", 'description' => 'Customer B Corporate Leased Line']
            );

            NetworkSegment::firstOrCreate(
                ['tenant_id' => $t->id, 'name' => 'VLAN 100 - Fiber Access'],
                [
                    'vrf_id' => $vrfA->id,
                    'vlan_id' => 100,
                    'subnet_cidr' => '192.168.10.0/24',
                    'gateway_ip' => '192.168.10.1',
                    'segment_type' => 'customer_access',
                ]
            );

            NetworkSegment::firstOrCreate(
                ['tenant_id' => $t->id, 'name' => 'VLAN 200 - Business Fiber'],
                [
                    'vrf_id' => $vrfB->id,
                    'vlan_id' => 200,
                    'subnet_cidr' => '192.168.20.0/24',
                    'gateway_ip' => '192.168.20.1',
                    'segment_type' => 'customer_access',
                ]
            );

            // 3. Seed Tenant Catalog Customization Example
            $globalHuawei = DeviceModel::where('model', 'HG8145H')->first();
            if ($globalHuawei) {
                TenantDeviceModel::firstOrCreate(
                    ['tenant_id' => $t->id, 'global_device_model_id' => $globalHuawei->id],
                    [
                        'custom_name' => "Smartoh Premium Fiber Gateway - HG8145H",
                        'custom_description' => "Standard Wi-Fi 5 Gigabit Dual-Band Optical Terminal for {$t->name} subscribers.",
                        'custom_image_url' => '/images/devices/generic_ont.svg',
                        'is_approved' => true,
                    ]
                );
            }
        }
    }
}
