<?php

namespace Database\Seeders;

use App\Models\BgpConfig;
use App\Models\BillingPlan;
use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\ClientRouter;
use App\Models\FiberInfrastructure;
use App\Models\FinanceTransaction;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MikroTikDevice;
use App\Models\NOCIncident;
use App\Models\OLTDevice;
use App\Models\ONTDevice;
use App\Models\OspfConfig;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Site;
use App\Models\SupportTicket;
use App\Models\Technician;
use App\Models\Tenant;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VPNTunnel;
use App\Models\WholesaleCapacityPool;
use App\Models\WholesaleOrder;
use App\Models\WholesaleProduct;
use App\Models\WholesaleWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Platform Owner)
        $superAdmin = User::create([
            'name' => 'Smartoh Super Admin',
            'email' => 'superadmin@smartoh.net',
            'phone' => '+254700000001',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_approved' => true,
        ]);

        // 2. Tenant 1: Alpha Net Fiber
        $tenant1 = Tenant::create([
            'name' => 'Alpha Net Fiber',
            'subdomain' => 'alpha',
            'custom_domain' => 'portal.alphanet.co.ke',
            'company_email' => 'info@alphanet.co.ke',
            'company_phone' => '+254722100200',
            'address' => 'Alpha Towers, Upper Hill, Nairobi',
            'brand_color' => '#0284c7', // Ocean Blue
            'accent_color' => '#0ea5e9',
            'theme_mode' => 'dark',
            'subscription_tier' => 'enterprise',
            'status' => 'active',
            'max_routers' => 25,
            'max_clients' => 5000,
        ]);

        // 3. Tenant 2: Metro Speed ISP
        $tenant2 = Tenant::create([
            'name' => 'Metro Speed ISP',
            'subdomain' => 'metro',
            'custom_domain' => 'billing.metrospeed.net',
            'company_email' => 'support@metrospeed.net',
            'company_phone' => '+254733400500',
            'address' => 'Westlands Commercial Hub, Nairobi',
            'brand_color' => '#7c3aed', // Royal Violet
            'accent_color' => '#a855f7',
            'theme_mode' => 'dark',
            'subscription_tier' => 'pro',
            'status' => 'active',
            'max_routers' => 10,
            'max_clients' => 1500,
        ]);

        // Populate Tenant 1 in detail
        app()->instance('current_tenant_id', $tenant1->id);

        // Users for Tenant 1
        $admin1 = User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Brian Mwangi (ISP Admin)',
            'email' => 'admin@alphanet.co.ke',
            'phone' => '+254712345678',
            'password' => Hash::make('password123'),
            'role' => 'isp_admin',
            'is_approved' => true,
        ]);

        $billingOfficer = User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Sarah Nduta (Billing Officer)',
            'email' => 'billing@alphanet.co.ke',
            'phone' => '+254722334455',
            'password' => Hash::make('password123'),
            'role' => 'billing_officer',
            'is_approved' => true,
        ]);

        $techUser = User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Kevin Omondi (Field Technician)',
            'email' => 'tech@alphanet.co.ke',
            'phone' => '+254733998877',
            'password' => Hash::make('password123'),
            'role' => 'technician',
            'is_approved' => true,
        ]);

        // Sites
        $site1 = Site::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Nairobi Central POP',
            'code' => 'NBO-POP-01',
            'location' => 'Upper Hill, Nairobi',
            'latitude' => -1.2985,
            'longitude' => 36.8142,
            'bank_account_name' => 'Alpha Net KCB Account',
            'bank_account_number' => '1209384729',
            'mpesa_paybill' => '400200',
            'mpesa_till' => '592819',
            'invoice_prefix' => 'ALPH-INV',
            'receipt_prefix' => 'ALPH-REC',
            'next_invoice_number' => 1042,
        ]);

        $site2 = Site::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Westlands Hub',
            'code' => 'WST-HUB-02',
            'location' => 'Westlands, Nairobi',
            'latitude' => -1.2675,
            'longitude' => 36.8045,
            'bank_account_name' => 'Alpha Net Westlands',
            'invoice_prefix' => 'WST-INV',
            'receipt_prefix' => 'WST-REC',
            'next_invoice_number' => 2015,
        ]);

        // Field Technician Profile
        $technician = Technician::create([
            'tenant_id' => $tenant1->id,
            'user_id' => $techUser->id,
            'site_id' => $site1->id,
            'staff_id_number' => 'TECH-001',
            'phone' => $techUser->phone,
            'specialization' => 'GPON Fiber & Splicing Specialist',
            'is_available' => true,
            'current_latitude' => -1.2990,
            'current_longitude' => 36.8150,
            'last_active_at' => now(),
        ]);

        // Billing Plans
        $plan10M = BillingPlan::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Home Fiber Basic 10M',
            'code' => 'HF-10M',
            'description' => 'Unlimited standard home streaming & browsing',
            'type' => 'monthly',
            'price' => 2500.00,
            'currency' => 'KES',
            'download_speed_kbps' => 10240,
            'upload_speed_kbps' => 5120,
            'fup_limit_gb' => 350,
            'fup_throttled_download_kbps' => 2048,
            'fup_throttled_upload_kbps' => 1024,
            'burst_limit' => '15M/10M',
            'burst_threshold' => '8M/4M',
            'burst_time' => '16/16',
            'contention_ratio' => 4,
            'validity_days' => 30,
            'vlan_id' => 100,
            'mikrotik_profile_name' => 'plan_10M',
            'is_active' => true,
        ]);

        $plan25M = BillingPlan::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Home Fiber Ultra 25M',
            'code' => 'HF-25M',
            'description' => '4K Ultra HD streaming, multi-device work & gaming',
            'type' => 'monthly',
            'price' => 4000.00,
            'currency' => 'KES',
            'download_speed_kbps' => 25600,
            'upload_speed_kbps' => 15360,
            'fup_limit_gb' => 600,
            'burst_limit' => '35M/20M',
            'contention_ratio' => 2,
            'validity_days' => 30,
            'vlan_id' => 100,
            'mikrotik_profile_name' => 'plan_25M',
            'is_active' => true,
        ]);

        $plan50M = BillingPlan::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Business Dedicated 50M',
            'code' => 'BIZ-50M',
            'description' => '1:1 CIR Dedicated corporate fiber with SLA guarantee',
            'type' => 'monthly',
            'price' => 8500.00,
            'currency' => 'KES',
            'download_speed_kbps' => 51200,
            'upload_speed_kbps' => 51200,
            'contention_ratio' => 1,
            'validity_days' => 30,
            'vlan_id' => 200,
            'mikrotik_profile_name' => 'plan_50M_cir',
            'is_active' => true,
        ]);

        // Gateways
        $mpesaGateway = PaymentGateway::create([
            'tenant_id' => $tenant1->id,
            'provider' => 'mpesa_daraja',
            'name' => 'Alpha Net Safaricom Paybill',
            'is_active' => true,
            'is_sandbox' => true,
            'credentials_encrypted' => encrypt(json_encode([
                'consumer_key' => 'MockConsumerKey992831',
                'consumer_secret' => 'MockConsumerSecret882910',
                'shortcode' => '400200',
                'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
            ])),
        ]);

        // MikroTik Routers
        $router1 = MikroTikDevice::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'name' => 'Core-CCR2004-Gateway',
            'ip_address' => '192.168.88.1',
            'api_port' => 8728,
            'api_username' => 'api_smartoh',
            'api_password_encrypted' => encrypt('AlphaMikro@2026'),
            'model' => 'CCR2004-16G-2S+',
            'routeros_version' => 'v7.16.2',
            'identity' => 'Alpha-Core-NBO',
            'is_online' => true,
            'cpu_load' => 18,
            'free_memory_bytes' => 872415232,
            'total_memory_bytes' => 1073741824,
            'uptime_seconds' => 1845200,
            'active_pppoe_sessions' => 348,
            'active_hotspot_sessions' => 92,
            'last_synced_at' => now(),
        ]);

        $router2 = MikroTikDevice::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site2->id,
            'name' => 'Westlands-Edge-CCR1009',
            'ip_address' => '192.168.90.1',
            'api_port' => 8728,
            'api_username' => 'api_smartoh',
            'api_password_encrypted' => encrypt('Westlands@2026'),
            'model' => 'CCR1009-7G-1C-1S+',
            'routeros_version' => 'v7.15.3',
            'identity' => 'Alpha-Edge-WST',
            'is_online' => true,
            'cpu_load' => 24,
            'free_memory_bytes' => 524288000,
            'total_memory_bytes' => 1073741824,
            'uptime_seconds' => 945200,
            'active_pppoe_sessions' => 164,
            'last_synced_at' => now(),
        ]);

        // BGP & OSPF
        BgpConfig::create([
            'tenant_id' => $tenant1->id,
            'mikrotik_device_id' => $router1->id,
            'as_number' => 37492,
            'router_id' => '102.215.14.1',
            'peer_name' => 'Safaricom-Upstream-Transit',
            'peer_address' => '197.248.0.2',
            'peer_as' => 37061,
            'is_active' => true,
            'status' => 'established',
        ]);

        OspfConfig::create([
            'tenant_id' => $tenant1->id,
            'mikrotik_device_id' => $router1->id,
            'area' => '0.0.0.0',
            'router_id' => '10.10.0.1',
            'interface' => 'sfp-plus1-FiberUplink',
            'cost' => 10,
            'is_active' => true,
        ]);

        // GPON OLT Device
        $olt1 = OLTDevice::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'name' => 'Huawei MA5800-X7 OLT',
            'vendor' => 'huawei',
            'ip_address' => '10.10.100.2',
            'pon_ports_count' => 16,
            'registered_onus_count' => 512,
            'is_online' => true,
        ]);

        // Fiber Infrastructure (POPs, FAT Boxes)
        $fat1 = FiberInfrastructure::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'type' => 'fat_box',
            'name' => 'FAT-UPP-04 (Elgon Court)',
            'code' => 'FAT-04',
            'latitude' => -1.2982,
            'longitude' => 36.8145,
            'capacity_cores' => 16,
            'used_cores' => 11,
            'status' => 'operational',
        ]);

        $fat2 = FiberInfrastructure::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'type' => 'fat_box',
            'name' => 'FAT-UPP-05 (Hill Park Suites)',
            'code' => 'FAT-05',
            'latitude' => -1.2995,
            'longitude' => 36.8138,
            'capacity_cores' => 16,
            'used_cores' => 14,
            'status' => 'operational',
        ]);

        // WireGuard VPN Tunnel
        VPNTunnel::create([
            'tenant_id' => $tenant1->id,
            'tunnel_name' => 'Remote-Tech-Support-wg0',
            'tunnel_type' => 'wireguard',
            'assigned_ip' => '10.150.0.10/32',
            'server_endpoint' => 'vpn.alphanet.co.ke:51820',
            'public_key' => 'b2FsaVByb3RlY3Rpb25LZXlTbWFydG9oMjAyNg==',
            'allowed_ips' => '0.0.0.0/0',
            'bytes_rx' => 184592000,
            'bytes_tx' => 392817200,
            'last_handshake_at' => now()->subMinutes(2),
            'is_active' => true,
        ]);

        // Subscribers (Clients)
        $client1 = Client::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'billing_plan_id' => $plan25M->id,
            'account_number' => 'ALPH-10021',
            'name' => 'Dr. James Kariuki',
            'email' => 'kariuki@example.com',
            'phone' => '+254711223344',
            'address' => 'Apt 4B, Elgon Court, Upper Hill',
            'latitude' => -1.2983,
            'longitude' => 36.8146,
            'service_type' => 'pppoe',
            'pppoe_username' => 'kariuki_fiber',
            'pppoe_password' => 'Pass@10021',
            'assigned_ip' => '10.200.12.45',
            'mac_address' => '48:8F:5A:23:44:01',
            'status' => 'active',
            'wallet_balance' => 0.00,
            'data_usage_bytes' => 194827104256, // ~194 GB
            'current_cycle_start' => now()->subDays(12),
            'current_cycle_end' => now()->addDays(18),
            'auto_renew' => true,
        ]);

        $clientUser1 = User::create([
            'tenant_id' => $tenant1->id,
            'client_id' => $client1->id,
            'name' => $client1->name,
            'email' => $client1->email,
            'phone' => $client1->phone,
            'password' => Hash::make('password123'),
            'role' => 'client',
            'is_approved' => true,
        ]);

        // ONT for Client 1
        ONTDevice::create([
            'tenant_id' => $tenant1->id,
            'olt_device_id' => $olt1->id,
            'client_id' => $client1->id,
            'serial_number' => 'HWTC19A82012',
            'mac_address' => '48:8F:5A:23:44:01',
            'pon_port' => 3,
            'onu_slot' => 7,
            'model' => 'EchoLife HG8245H5',
            'rx_power_dbm' => -19.42, // Healthy optical signal!
            'tx_power_dbm' => 2.35,
            'vlan_id' => 100,
            'wan_mode' => 'pppoe',
            'status' => 'online',
        ]);

        // Client Router with TR-069
        ClientRouter::create([
            'tenant_id' => $tenant1->id,
            'client_id' => $client1->id,
            'site_id' => $site1->id,
            'serial_number' => 'HWTC19A82012',
            'manufacturer' => 'Huawei Technologies',
            'model' => 'HG8245H5',
            'preferred_management_path' => 'tr069',
            'cwmp_connection_url' => 'http://10.200.12.45:7547/cwmp',
            'wifi_ssid' => 'Kariuki_5G_Fiber',
            'wifi_password' => 'AlphaKariuki2026',
            'optical_power_rx' => -19.42,
            'optical_power_tx' => 2.35,
            'uptime_seconds' => 482100,
            'last_inform_at' => now()->subMinutes(4),
            'status' => 'online',
        ]);

        // Client 2: Expiring Soon
        $client2 = Client::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'billing_plan_id' => $plan10M->id,
            'account_number' => 'ALPH-10022',
            'name' => 'Grace Wanjiku',
            'email' => 'wanjiku@example.com',
            'phone' => '+254722991122',
            'address' => 'Flat 12, Hill Park Suites, Upper Hill',
            'latitude' => -1.2996,
            'longitude' => 36.8139,
            'service_type' => 'pppoe',
            'pppoe_username' => 'wanjiku_home',
            'pppoe_password' => 'Pass@10022',
            'status' => 'expiring_soon',
            'data_usage_bytes' => 280194821000,
            'current_cycle_start' => now()->subDays(28),
            'current_cycle_end' => now()->addDays(2),
        ]);

        // Client 3: Suspended (Unpaid)
        $client3 = Client::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'billing_plan_id' => $plan25M->id,
            'account_number' => 'ALPH-10023',
            'name' => 'Apex Logistics Ltd',
            'email' => 'finance@apexlogistics.co.ke',
            'phone' => '+254700554433',
            'address' => 'Floor 2, Alpha Towers, Upper Hill',
            'latitude' => -1.2985,
            'longitude' => 36.8142,
            'service_type' => 'pppoe',
            'pppoe_username' => 'apex_logistics',
            'pppoe_password' => 'Pass@10023',
            'status' => 'suspended',
            'current_cycle_end' => now()->subDays(5),
            'service_suspended_at' => now()->subDays(5),
        ]);

        // Invoices & Payments for Client 1 & 3
        $inv1 = Invoice::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'client_id' => $client1->id,
            'billing_plan_id' => $plan25M->id,
            'invoice_number' => 'ALPH-INV-1039',
            'issue_date' => now()->subDays(12),
            'due_date' => now()->subDays(5),
            'subtotal' => 4000.00,
            'tax_amount' => 640.00,
            'total_amount' => 4640.00,
            'paid_amount' => 4640.00,
            'status' => 'paid',
            'notes' => 'Home Fiber Ultra 25M Monthly Renewal',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'description' => 'Home Fiber Ultra 25M (25Mbps / 15Mbps)',
            'quantity' => 1,
            'unit_price' => 4000.00,
            'total' => 4000.00,
        ]);

        Payment::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'client_id' => $client1->id,
            'invoice_id' => $inv1->id,
            'payment_gateway_id' => $mpesaGateway->id,
            'transaction_reference' => 'SMART-M882910',
            'external_transaction_id' => 'QGH8492019',
            'payment_method' => 'mpesa_stk',
            'amount' => 4640.00,
            'currency' => 'KES',
            'phone_number' => $client1->phone,
            'payer_name' => $client1->name,
            'status' => 'completed',
            'paid_at' => now()->subDays(12),
        ]);

        // Unpaid Invoice for Client 3
        Invoice::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'client_id' => $client3->id,
            'billing_plan_id' => $plan25M->id,
            'invoice_number' => 'ALPH-INV-1041',
            'issue_date' => now()->subDays(8),
            'due_date' => now()->subDays(3),
            'subtotal' => 4000.00,
            'tax_amount' => 640.00,
            'total_amount' => 4640.00,
            'paid_amount' => 0.00,
            'status' => 'overdue',
            'notes' => 'Service Suspended due to non-payment',
        ]);

        // Vouchers batch
        for ($i = 1; $i <= 5; $i++) {
            Voucher::create([
                'tenant_id' => $tenant1->id,
                'billing_plan_id' => $plan10M->id,
                'code' => 'VCH-' . strtoupper(Str::random(4) . '-' . Str::random(4)),
                'type' => 'hotspot',
                'price' => 100.00,
                'data_limit_mb' => 2048,
                'duration_minutes' => 1440,
                'status' => 'active',
                'expires_at' => now()->addDays(60),
            ]);
        }

        // Support Tickets
        $ticket1 = SupportTicket::create([
            'tenant_id' => $tenant1->id,
            'client_id' => $client1->id,
            'assigned_user_id' => $techUser->id,
            'ticket_number' => 'TCK-948102',
            'subject' => 'Wi-Fi coverage weak in backyard room',
            'category' => 'slow_speeds',
            'priority' => 'medium',
            'status' => 'in_progress',
            'sla_due_at' => now()->addHours(8),
        ]);

        TicketReply::create([
            'support_ticket_id' => $ticket1->id,
            'user_id' => $clientUser1->id,
            'is_client_reply' => true,
            'message' => 'Hello, speeds in my backyard office are around 2Mbps while main living room is 25Mbps.',
        ]);

        TicketReply::create([
            'support_ticket_id' => $ticket1->id,
            'user_id' => $techUser->id,
            'is_client_reply' => false,
            'message' => 'Hi Dr. Kariuki, technician dispatched to install a secondary mesh access point on Saturday morning.',
        ]);

        // Installation Job
        InstallationJob::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'client_id' => $client2->id,
            'technician_id' => $technician->id,
            'fiber_box_id' => $fat2->id,
            'job_number' => 'JOB-481920',
            'scheduled_at' => now()->addDay()->setHour(10),
            'latitude' => $client2->latitude,
            'longitude' => $client2->longitude,
            'fiber_cable_meters' => 85,
            'optical_power_rx_dbm' => -20.15,
            'status' => 'scheduled',
            'technician_notes' => 'Route fiber drop from FAT-05 pole 3 across rear conduit.',
        ]);

        // NOC Incident
        NOCIncident::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'assigned_technician_id' => $technician->id,
            'incident_number' => 'INC-1094',
            'title' => 'Upstream Fiber Jitter Warning on Safari Transit',
            'description' => 'Latency spiked from 8ms to 45ms towards IXP peering exchange. Failover routing to secondary SEACOM transit initiated.',
            'severity' => 'warning',
            'status' => 'investigating',
            'affected_component' => 'Safaricom-BGP-Peer-37061',
            'affected_clients_count' => 140,
            'started_at' => now()->subHours(1),
        ]);

        // Finance: Chart of Accounts & General Ledger
        $accBank = ChartOfAccount::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'code' => '1010',
            'name' => 'Bank & M-Pesa Collections Account',
            'type' => 'asset',
            'current_balance' => 450000.00,
            'is_active' => true,
        ]);

        $accRev = ChartOfAccount::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'code' => '4010',
            'name' => 'Monthly Internet Subscription Revenue',
            'type' => 'revenue',
            'current_balance' => 520000.00,
            'is_active' => true,
        ]);

        $accExp = ChartOfAccount::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'code' => '5010',
            'name' => 'Upstream Bandwidth Transit Costs',
            'type' => 'expense',
            'current_balance' => 140000.00,
            'is_active' => true,
        ]);

        FinanceTransaction::create([
            'tenant_id' => $tenant1->id,
            'site_id' => $site1->id,
            'transaction_number' => 'TX-849102',
            'transaction_date' => now()->subDays(2),
            'debit_account_id' => $accBank->id,
            'credit_account_id' => $accRev->id,
            'amount' => 4640.00,
            'reference' => 'REC-4819',
            'description' => 'M-Pesa payment from Dr. James Kariuki',
            'is_posted' => true,
        ]);

        // Wholesale Marketplace
        $pool = WholesaleCapacityPool::create([
            'tenant_id' => $tenant1->id,
            'name' => 'KIXP Nairobi 10Gbps Core Pool',
            'exchange_point' => 'KIXP Nairobi IXP',
            'total_capacity_mbps' => 10000,
            'allocated_capacity_mbps' => 6400,
            'reserved_capacity_mbps' => 1200,
        ]);

        $wsProduct = WholesaleProduct::create([
            'tenant_id' => $tenant1->id,
            'capacity_pool_id' => $pool->id,
            'name' => 'Wholesale 1Gbps QinQ VLAN Transit',
            'committed_bandwidth_cir_mbps' => 1000,
            'burst_bandwidth_pir_mbps' => 1200,
            'contention_ratio' => 1,
            'handoff_type' => 'vlan_qinq',
            'price_per_mbps' => 4.50, // $4.50 / Mbps
            'setup_fee' => 250.00,
            'is_active' => true,
        ]);

        $wallet = WholesaleWallet::create([
            'tenant_id' => $tenant1->id,
            'reseller_company_name' => 'Rift Valley Wireless Ltd',
            'contact_person' => 'Daniel Kimani',
            'email' => 'dk@riftwireless.co.ke',
            'phone' => '+254719283746',
            'balance' => 4500.00,
            'credit_limit' => 2000.00,
            'currency' => 'USD',
        ]);

        WholesaleOrder::create([
            'tenant_id' => $tenant1->id,
            'wholesale_wallet_id' => $wallet->id,
            'wholesale_product_id' => $wsProduct->id,
            'order_number' => 'WSO-99201',
            'bandwidth_mbps' => 500,
            'monthly_charge' => 2250.00,
            'status' => 'active',
            'vlan_id' => 304,
            'allocated_subnet' => '102.215.18.0/29',
            'activated_at' => now()->subMonths(1),
        ]);
    }
}
