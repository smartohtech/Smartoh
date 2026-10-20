<?php

use App\Http\Controllers\AdminPlatformController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\CrmInventoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\NocController;
use App\Http\Controllers\OperationsHubController;
use App\Http\Controllers\RadiusController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UniversalCpeController;
use App\Http\Controllers\UdieDeviceController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\PublicMarketingController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformApplicationController;
use App\Http\Controllers\Platform\PlatformTenantController;
use App\Http\Controllers\Platform\PlatformPlanController;
use App\Http\Controllers\Platform\PlatformSubscriptionController;
use App\Http\Controllers\Platform\PlatformInvoiceController;
use App\Http\Controllers\Platform\PlatformAuditController;
use Illuminate\Support\Facades\Route;

// Public Landing & Marketing Pages
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/features', [PublicMarketingController::class, 'features'])->name('features');
Route::get('/pricing', [PublicMarketingController::class, 'pricing'])->name('pricing');
Route::get('/application-status', [PublicMarketingController::class, 'applicationStatus'])->name('application.status');
Route::post('/application-status/respond/{application}', [PublicMarketingController::class, 'submitInfoResponse'])->name('application.respond');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/switch-tenant', [AuthController::class, 'switchTenant'])->name('switch-tenant');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// TR-069 CWMP Inform Endpoint (CPEs connect here)
Route::post('/cwmp/inform', [UniversalCpeController::class, 'handleCwmpInform'])->name('cwmp.inform');

// Authenticated Application Routes (Guarded by auth & enforce_subscription)
Route::middleware(['auth', 'enforce_subscription'])->group(function () {
    // Executive Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Subscribers & CRM
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/export', [ClientController::class, 'export'])->name('export');
        Route::post('/import', [ClientController::class, 'import'])->name('import');
        Route::post('/restore-connections', [ClientController::class, 'restoreConnections'])->name('restore-connections');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{client}/generate-bill', [ClientController::class, 'generateBill'])->name('generate-bill');
    });

    // Customer Support Tickets (Helpdesk & SLAs)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/status', [TicketController::class, 'updateStatus'])->name('status');
    });

    // Billing, Plans, Invoices, Payments, Vouchers
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
        Route::post('/plans', [BillingController::class, 'storePlan'])->name('plans.store');
        Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
        Route::get('/payments', [BillingController::class, 'payments'])->name('payments');
        Route::get('/payments/live-feed', [BillingController::class, 'liveTransactionsFeed'])->name('payments.live-feed');
        Route::get('/payments/export', [BillingController::class, 'exportPaymentsCsv'])->name('payments.export');
        Route::post('/payments/stk-push', [BillingController::class, 'triggerStkPush'])->name('payments.stk-push');
        Route::post('/payments/import-statement', [BillingController::class, 'importStatement'])->name('payments.import-statement');
        Route::post('/payments/exceptions/{exception}/resolve', [BillingController::class, 'resolveException'])->name('payments.exceptions.resolve');
        Route::post('/payments/activations/{job}/retry', [BillingController::class, 'retryActivation'])->name('payments.activations.retry');
        Route::post('/payments/{payment}/reverse', [BillingController::class, 'reversePayment'])->name('payments.reverse');
        Route::get('/vouchers', [BillingController::class, 'vouchers'])->name('vouchers');
        Route::post('/vouchers/generate', [BillingController::class, 'generateVouchers'])->name('vouchers.generate');
        Route::get('/subscription', [BillingController::class, 'subscription'])->name('subscription');
        Route::post('/subscription/pay', [BillingController::class, 'paySubscription'])->name('subscription.pay');
    });

    // Network & MikroTik Core
    Route::prefix('network')->name('network.')->group(function () {
        Route::get('/mikrotik', [NetworkController::class, 'mikrotik'])->name('mikrotik');
        Route::post('/mikrotik', [NetworkController::class, 'storeRouter'])->name('mikrotik.store');
        Route::post('/mikrotik/{device}/test', [NetworkController::class, 'testConnection'])->name('mikrotik.test');
        Route::post('/mikrotik/{device}/reboot', [NetworkController::class, 'rebootRouter'])->name('mikrotik.reboot');
    });

    // Universal CPE, TR-069, TR-181 USP, WireGuard, OLT/ONT
    Route::prefix('cpe')->name('cpe.')->group(function () {
        Route::get('/', [UniversalCpeController::class, 'index'])->name('index');
        Route::post('/ont/{ont}/optical-test', [UniversalCpeController::class, 'testOptical'])->name('ont.test');
        Route::post('/wireguard', [UniversalCpeController::class, 'createWireGuardTunnel'])->name('wireguard.store');
    });

    // Network Operations Center (NOC)
    Route::prefix('noc')->name('noc.')->group(function () {
        Route::get('/', [NocController::class, 'index'])->name('index');
        Route::post('/incident', [NocController::class, 'storeIncident'])->name('incident.store');
        Route::post('/incident/{incident}/resolve', [NocController::class, 'resolveIncident'])->name('incident.resolve');
        Route::get('/ask-ai', [NocController::class, 'askAi'])->name('ask-ai');
        Route::get('/capacity/telemetry', [NocController::class, 'capacityTelemetry'])->name('capacity.telemetry');
        Route::post('/capacity/update', [NocController::class, 'updateCapacity'])->name('capacity.update');
    });

    // Smartoh Universal Device Intelligence & Discovery Engine (UDIE)
    Route::prefix('devices')->name('udie.')->group(function () {
        Route::get('/', [UdieDeviceController::class, 'index'])->name('index');
        Route::get('/device/{device}', [UdieDeviceController::class, 'show'])->name('show');
        Route::get('/unknown', [UdieDeviceController::class, 'unknownLaboratory'])->name('unknown');
        Route::post('/device/{device}/identify', [UdieDeviceController::class, 'manualIdentify'])->name('identify');
        Route::post('/device/{device}/identify', [UdieDeviceController::class, 'manualIdentify'])->name('devices.identify');
        Route::post('/device/{device}/reidentify', [UdieDeviceController::class, 'reidentify'])->name('reidentify');
        Route::get('/models', [UdieDeviceController::class, 'modelRegistry'])->name('models');
        Route::post('/models/tenant', [UdieDeviceController::class, 'storeTenantModel'])->name('models.tenant.store');
        Route::post('/discover', [UdieDeviceController::class, 'triggerDiscovery'])->name('discover');
        Route::post('/event/{event}/resolve', [UdieDeviceController::class, 'resolveEvent'])->name('events.resolve');
        Route::post('/event/{event}/resolve', [UdieDeviceController::class, 'resolveEvent'])->name('event.resolve');
    });

    // GIS Fiber & Field Operations
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/map', [GisController::class, 'map'])->name('map');
        Route::get('/jobs', [GisController::class, 'jobs'])->name('jobs');
        Route::post('/jobs', [GisController::class, 'storeJob'])->name('jobs.store');
        Route::post('/jobs/{job}/complete', [GisController::class, 'completeJob'])->name('jobs.complete');
    });

    // Finance & Multi-Site Accounting
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::post('/account', [FinanceController::class, 'storeAccount'])->name('account.store');
    });

    // Wholesale Marketplace
    Route::prefix('wholesale')->name('wholesale.')->group(function () {
        Route::get('/', [WholesaleController::class, 'index'])->name('index');
        Route::post('/order', [WholesaleController::class, 'storeOrder'])->name('order.store');
    });

    // Super Admin / Platform Owner Tenants
    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index')->middleware('super_admin');
        Route::post('/', [TenantController::class, 'store'])->name('store')->middleware('super_admin');
        Route::post('/{tenant}/branding', [TenantController::class, 'updateBranding'])->name('branding');
    });

    // Subscriber Self-Service Portal
    Route::prefix('portal')->name('client.')->group(function () {
        Route::get('/', [ClientPortalController::class, 'index'])->name('portal');
        Route::get('/live-status', [ClientPortalController::class, 'liveStatus'])->name('live-status');
        Route::post('/pay-mpesa', [ClientPortalController::class, 'payMpesa'])->name('pay-mpesa');
        Route::post('/submit-claim', [ClientPortalController::class, 'submitManualClaim'])->name('submit-claim');
        Route::post('/redeem-voucher', [ClientPortalController::class, 'redeemVoucher'])->name('redeem-voucher');
        Route::post('/ticket', [ClientPortalController::class, 'createTicket'])->name('create-ticket');
    });

    // 1. Access Management / AAA RADIUS
    Route::prefix('radius')->name('radius.')->group(function () {
        Route::get('/', [RadiusController::class, 'index'])->name('index');
        Route::post('/config', [RadiusController::class, 'updateConfig'])->name('config');
        Route::post('/disconnect', [RadiusController::class, 'disconnectSession'])->name('disconnect');
        Route::post('/session/{session}/disconnect', [RadiusController::class, 'disconnectSession'])->name('session.disconnect');
    });

    // 2. Gateways (API / Daraja STK Push & Manual / Cash / Bank)
    Route::prefix('gateways')->name('gateways.')->group(function () {
        Route::get('/api', [GatewayController::class, 'apiGateways'])->name('api');
        Route::post('/api', [GatewayController::class, 'storeApiGateway'])->name('api.store');
        Route::post('/test', [GatewayController::class, 'testGateway'])->name('test');
        Route::post('/mpesa', [GatewayController::class, 'storeMpesa'])->name('mpesa.store');
        Route::get('/manual', [GatewayController::class, 'manualGateways'])->name('manual');
        Route::post('/manual', [GatewayController::class, 'storeManual'])->name('manual.store');
        Route::post('/{gateway}/toggle', [GatewayController::class, 'toggleGateway'])->name('toggle');
        Route::put('/{gateway}', [GatewayController::class, 'updateGateway'])->name('update');
        Route::delete('/{gateway}', [GatewayController::class, 'destroyGateway'])->name('destroy');
    });

    // 3. Communication Hub (WhatsApp, Dunning Reminders, SMS Gateways)
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/whatsapp', [CommunicationController::class, 'whatsapp'])->name('whatsapp');
        Route::post('/whatsapp/send', [CommunicationController::class, 'sendWhatsapp'])->name('whatsapp.send');
        Route::get('/reminders', [CommunicationController::class, 'reminders'])->name('reminders');
        Route::post('/reminders/send', [CommunicationController::class, 'sendReminder'])->name('reminders.send');
        Route::post('/reminders/run', [CommunicationController::class, 'sendReminder'])->name('reminders.run');
        Route::get('/sms', [CommunicationController::class, 'smsGateways'])->name('sms');
        Route::post('/sms/test', [CommunicationController::class, 'testSmsGateway'])->name('sms.test');
        Route::post('/sms', [CommunicationController::class, 'storeSmsGateway'])->name('sms.store');
    });
    // Direct global named routes used by views
    Route::post('/whatsapp/send', [CommunicationController::class, 'sendWhatsapp'])->name('whatsapp.send');
    Route::post('/reminders/send', [CommunicationController::class, 'sendReminder'])->name('reminders.send');
    Route::post('/sms/test', [CommunicationController::class, 'testSmsGateway'])->name('sms.test');

    // 4. NOC Operations Hub (Predictive Maintenance, Routing Config, Rogue Detection)
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('/predictive', [OperationsHubController::class, 'predictiveMaintenance'])->name('predictive');
        Route::get('/routing', [OperationsHubController::class, 'routingConfig'])->name('routing');
        Route::post('/routing', [OperationsHubController::class, 'storeRouting'])->name('routing.store');
        Route::post('/routing/ospf', [OperationsHubController::class, 'storeOspf'])->name('routing.ospf.store');
        Route::post('/routing/bgp', [OperationsHubController::class, 'storeBgp'])->name('routing.bgp.store');
        Route::get('/detection', [OperationsHubController::class, 'routerDetection'])->name('detection');
        Route::post('/detection/action', [OperationsHubController::class, 'authorizeDetectedRouter'])->name('router.detection.action');
        Route::post('/quarantine', [OperationsHubController::class, 'authorizeDetectedRouter'])->name('quarantine');
    });
    // Direct global route aliases for routing & router detection
    Route::post('/routing/ospf', [OperationsHubController::class, 'storeOspf'])->name('routing.ospf.store');
    Route::post('/routing/bgp', [OperationsHubController::class, 'storeBgp'])->name('routing.bgp.store');
    Route::post('/router/detection/action', [OperationsHubController::class, 'authorizeDetectedRouter'])->name('router.detection.action');

    // 5. CRM & Inventory
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [CrmInventoryController::class, 'crm'])->name('index');
        Route::post('/lead', [CrmInventoryController::class, 'storeLead'])->name('lead.store');
    });
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [CrmInventoryController::class, 'inventory'])->name('index');
        Route::post('/store', [CrmInventoryController::class, 'storeInventory'])->name('store');
        Route::post('/stock', [CrmInventoryController::class, 'storeInventory'])->name('stock.store');
    });
    Route::get('/reports', [CrmInventoryController::class, 'reports'])->name('reports.index');

    // 6. Platform Administration & Security (Tenant-scoped administration)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/isp-approvals', [AdminPlatformController::class, 'ispApprovals'])->name('isp-approvals')->middleware('super_admin');
        Route::post('/isp-approvals/{tenant}/approve', [AdminPlatformController::class, 'approveIsp'])->name('isp.approve')->middleware('super_admin');
        Route::get('/user-approvals', [AdminPlatformController::class, 'userApprovals'])->name('user-approvals');
        Route::post('/user-approvals/{user}/approve', [AdminPlatformController::class, 'approveUser'])->name('user.approve');
        Route::get('/tenant-branding', [AdminPlatformController::class, 'tenantBranding'])->name('tenant-branding');
        Route::get('/system-settings', [AdminPlatformController::class, 'systemSettings'])->name('system-settings');
        Route::get('/audit-logs', [AdminPlatformController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/proration', [AdminPlatformController::class, 'proration'])->name('proration');
        Route::post('/proration/calculate', [AdminPlatformController::class, 'calculateProration'])->name('proration.calculate');
        Route::get('/system-plan', [AdminPlatformController::class, 'systemPlan'])->name('system-plan');

        // Restricted Super Admin Paths under /admin
        Route::any('/tenants/{any?}', fn() => abort(403, 'Restricted to Platform Super Administrators.'))->where('any', '.*');
        Route::any('/approvals/{any?}', fn() => abort(403, 'Restricted to Platform Super Administrators.'))->where('any', '.*');
    });

    // Base44 Exact URL Aliases (for 1:1 parity with smartohtechnologies.base44.app routes)
    Route::get('/Radius', [RadiusController::class, 'index']);
    Route::get('/APIGateways', [GatewayController::class, 'apiGateways']);
    Route::get('/PaymentGateways', [GatewayController::class, 'manualGateways']);
    Route::get('/WhatsApp', [CommunicationController::class, 'whatsapp']);
    Route::get('/Reminders', [CommunicationController::class, 'reminders']);
    Route::get('/SMSGateways', [CommunicationController::class, 'smsGateways']);
    Route::get('/RoutingConfig', [OperationsHubController::class, 'routingConfig']);
    Route::get('/RouterDetection', [OperationsHubController::class, 'routerDetection']);
    Route::get('/CRM', [CrmInventoryController::class, 'crm']);
    Route::get('/Inventory', [CrmInventoryController::class, 'inventory']);
    Route::get('/AdvancedReports', [CrmInventoryController::class, 'reports']);
    Route::get('/PredictiveMaintenance', [OperationsHubController::class, 'predictiveMaintenance']);
    Route::get('/ISPApprovals', [AdminPlatformController::class, 'ispApprovals'])->middleware('super_admin');
    Route::get('/UserApprovals', [AdminPlatformController::class, 'userApprovals']);
    Route::get('/TenantBrandingSettings', [AdminPlatformController::class, 'tenantBranding']);
    Route::get('/Proration', [AdminPlatformController::class, 'proration']);
    Route::get('/SystemPlan', [AdminPlatformController::class, 'systemPlan']);
});

// =========================================================================
// SUPER ADMIN CONTROL PANEL (Exclusively guarded by auth & super_admin)
// =========================================================================
Route::prefix('platform')->name('platform.')->middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');

    // 1. ISP Applications & Onboarding Workflow
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [PlatformApplicationController::class, 'index'])->name('index');
        Route::get('/{application}', [PlatformApplicationController::class, 'show'])->name('show');
        Route::post('/{application}/approve', [PlatformApplicationController::class, 'approve'])->name('approve');
        Route::post('/{application}/reject', [PlatformApplicationController::class, 'reject'])->name('reject');
        Route::post('/{application}/request-info', [PlatformApplicationController::class, 'requestInfo'])->name('request-info');
    });

    // 2. Tenant Management & Administrative Controls
    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', [PlatformTenantController::class, 'index'])->name('index');
        Route::get('/{tenant}', [PlatformTenantController::class, 'show'])->name('show');
        Route::post('/{tenant}/suspend', [PlatformTenantController::class, 'suspend'])->name('suspend');
        Route::post('/{tenant}/reinstate', [PlatformTenantController::class, 'reinstate'])->name('reinstate');
        Route::get('/{tenant}/impersonate', [PlatformTenantController::class, 'impersonate'])->name('impersonate');
    });

    // 3. SaaS Plans, Pricing & Versioning
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlatformPlanController::class, 'index'])->name('index');
        Route::post('/', [PlatformPlanController::class, 'store'])->name('store');
        Route::put('/{plan}', [PlatformPlanController::class, 'update'])->name('update');
        Route::post('/{plan}/toggle-status', [PlatformPlanController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 4. Subscriptions & 7-Day Trial Engine
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [PlatformSubscriptionController::class, 'index'])->name('index');
        Route::get('/{subscription}', [PlatformSubscriptionController::class, 'show'])->name('show');
        Route::post('/{subscription}/update-status', [PlatformSubscriptionController::class, 'updateStatus'])->name('update-status');
        Route::post('/{subscription}/extend-trial', [PlatformSubscriptionController::class, 'extendTrial'])->name('extend-trial');
        Route::post('/{subscription}/invoice', [PlatformSubscriptionController::class, 'generateInvoice'])->name('invoice');
    });

    // 5. Invoices & Payment Gateway Reconciliation
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [PlatformInvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [PlatformInvoiceController::class, 'show'])->name('show');
        Route::post('/{invoice}/record-payment', [PlatformInvoiceController::class, 'recordPayment'])->name('record-payment');
    });

    // 6. Security, Audit Logs & Governance
    Route::get('/audit', [PlatformAuditController::class, 'index'])->name('audit.index');
});

