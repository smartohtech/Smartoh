<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\MikroTikDevice;
use App\Models\Site;
use App\Services\Billing\BillingEngine;
use App\Services\MikroTik\MikroTikService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['billingPlan', 'site', 'mikrotikDevice'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('pppoe_username', 'like', "%{$search}%")
                  ->orWhere('assigned_ip', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'expiring_soon') {
                $query->where('status', 'expired');
            } else {
                $query->where('status', $status);
            }
        }

        if ($serviceType = $request->get('service_type')) {
            if ($serviceType === 'static') {
                $query->whereIn('service_type', ['static', 'dhcp']);
            } else {
                $query->where('service_type', $serviceType);
            }
        }

        if ($siteId = $request->get('site_id')) {
            $query->where('site_id', $siteId);
        }

        if ($routerId = $request->get('mikrotik_device_id')) {
            $query->where('mikrotik_device_id', $routerId);
        }

        $clients = $query->paginate(15)->withQueryString();
        $sites = Site::all();
        $plans = BillingPlan::where('is_active', true)->get();
        $routers = MikroTikDevice::orderByDesc('is_online')->get();

        $statusCounts = [
            'all'           => Client::count(),
            'active'        => Client::where('status', 'active')->count(),
            'suspended'     => Client::where('status', 'suspended')->count(),
            'expired'       => Client::where('status', 'expired')->count(),
            'fup_throttled' => Client::where('status', 'fup_throttled')->count(),
            'overdue'       => Client::where('status', 'overdue')->count(),
        ];

        $serviceCounts = [
            'pppoe'              => Client::where('service_type', 'pppoe')->count(),
            'hotspot'            => Client::where('service_type', 'hotspot')->count(),
            'static'             => Client::whereIn('service_type', ['static', 'dhcp'])->count(),
            'connected_verified' => Client::where('connection_status', 'connected_verified')->count(),
        ];

        return view('clients.index', compact('clients', 'sites', 'plans', 'routers', 'statusCounts', 'serviceCounts'));
    }

    public function store(Request $request, MikroTikService $mikrotik)
    {
        if (!$request->filled('name') && ($request->filled('first_name') || $request->filled('last_name'))) {
            $request->merge([
                'name' => trim($request->input('first_name', '') . ' ' . $request->input('last_name', '')),
            ]);
        }

        if ($request->input('service_type') === 'static_ip') {
            $request->merge(['service_type' => 'static']);
        }

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string|max:50',
            'email'              => 'nullable|email',
            'site_id'            => 'nullable|exists:sites,id',
            'mikrotik_device_id' => 'nullable|exists:mikrotik_devices,id',
            'billing_plan_id'    => 'required|exists:billing_plans,id',
            'service_type'       => 'required|in:pppoe,hotspot,static,dhcp',
            'pppoe_username'     => 'nullable|string|max:120',
            'pppoe_password'     => 'nullable|string|max:120',
            'assigned_ip'        => 'nullable|string|max:64',
            'mac_address'        => 'nullable|string|max:64',
            'address'            => 'nullable|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
        ]);

        $site = !empty($validated['site_id']) ? Site::find($validated['site_id']) : Site::first();
        $accountNumber = 'CLI-' . strtoupper(Str::random(6));
        $plan = BillingPlan::findOrFail($validated['billing_plan_id']);
        $router = !empty($validated['mikrotik_device_id'])
            ? MikroTikDevice::find($validated['mikrotik_device_id'])
            : MikroTikDevice::where('is_online', true)->first();

        $defaultUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $validated['name'])) . rand(10, 99);

        $client = Client::create(array_merge($validated, [
            'site_id'             => $site?->id,
            'mikrotik_device_id'  => $router?->id,
            'account_number'      => $accountNumber,
            'status'              => 'active',
            'pppoe_username'      => !empty($validated['pppoe_username']) ? $validated['pppoe_username'] : $defaultUsername,
            'pppoe_password'      => !empty($validated['pppoe_password']) ? $validated['pppoe_password'] : Str::random(8),
            'current_cycle_start' => now(),
            'current_cycle_end'   => now()->addDays($plan->validity_days ?: 30),
        ]));

        if ($router) {
            $client->setRelation('billingPlan', $plan);
            $mikrotik->batchMigrateAndRestoreClients($router, [$client]);
        }

        AuditLog::create([
            'tenant_id'      => $client->tenant_id,
            'user_id'        => auth()->id(),
            'event'          => 'client.created',
            'auditable_type' => Client::class,
            'auditable_id'   => $client->id,
            'new_values'     => [
                'name'         => $client->name,
                'account'      => $client->account_number,
                'service_type' => $client->service_type,
            ],
            'ip_address'     => request()->ip(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', "Subscriber {$client->name} registered and provisioned on router!");
    }

    /**
     * Export subscribers as CSV, sample CSV template, or ready-to-import MikroTik RouterOS (.rsc) script.
     */
    public function export(Request $request, MikroTikService $mikrotik)
    {
        $format = strtolower($request->query('format', 'csv'));

        if ($format === 'template') {
            $headers = [
                'account_number', 'name', 'phone', 'email', 'service_type',
                'pppoe_username', 'pppoe_password', 'assigned_ip', 'mac_address',
                'plan_code', 'status', 'expiry_date', 'address'
            ];
            $sampleRows = [
                ['CLI-PPP001', 'John Kamau', '254712345601', 'john@example.com', 'pppoe', 'john_fiber', 'Pass@123', '10.200.10.15', '48:8F:5A:10:01:01', 'HF-10M', 'active', now()->addDays(30)->toDateString(), 'Apt 2A, Kilimani'],
                ['CLI-HSP002', 'Mary Wambui', '254712345602', 'mary@example.com', 'hotspot', 'mary_wifi', 'Wifi@2026', '10.100.20.44', '48:8F:5A:10:02:02', 'HF-10M', 'active', now()->addDays(14)->toDateString(), 'Shop 5, Westlands'],
                ['CLI-STA003', 'Apex Logistics Ltd', '254712345603', 'it@apex.co.ke', 'static', 'apex_static', 'Static@1', '192.168.88.205', '48:8F:5A:10:03:03', 'BIZ-50M', 'active', now()->addDays(30)->toDateString(), '4th Floor, Upper Hill'],
            ];

            $csv = implode(',', $headers) . "\n";
            foreach ($sampleRows as $row) {
                $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row)) . "\n";
            }

            return response($csv, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="smartoh_clients_migration_template.csv"',
            ]);
        }

        $query = Client::with(['billingPlan', 'site', 'mikrotikDevice'])->orderBy('id');

        if ($serviceType = $request->query('service_type')) {
            if ($serviceType === 'static') {
                $query->whereIn('service_type', ['static', 'dhcp']);
            } elseif ($serviceType !== 'all') {
                $query->where('service_type', $serviceType);
            }
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $clients = $query->get();

        AuditLog::create([
            'tenant_id'  => app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id,
            'user_id'    => auth()->id(),
            'event'      => 'clients.exported',
            'new_values' => [
                'format' => $format,
                'count'  => $clients->count(),
            ],
            'ip_address' => $request->ip(),
        ]);

        if ($format === 'rsc') {
            $plans = BillingPlan::where('is_active', true)->get();
            $script = $mikrotik->generateRouterOsMigrationScript($clients, $plans);
            $filename = 'smartoh_mikrotik_migration_' . now()->format('Ymd_His') . '.rsc';

            return response($script, 200, [
                'Content-Type'        => 'text/plain; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $headers = [
            'account_number', 'name', 'phone', 'email', 'service_type',
            'pppoe_username', 'pppoe_password', 'assigned_ip', 'mac_address',
            'plan_code', 'plan_name', 'upload_speed_kbps', 'download_speed_kbps',
            'status', 'connection_status', 'wallet_balance', 'expiry_date',
            'site_code', 'router_name', 'address'
        ];

        $lines = [implode(',', $headers)];

        foreach ($clients as $client) {
            $row = [
                $client->account_number,
                $client->name,
                $client->phone,
                $client->email ?? '',
                $client->service_type,
                $client->pppoe_username ?? '',
                $client->pppoe_password ?? '',
                $client->assigned_ip ?? '',
                $client->mac_address ?? '',
                $client->billingPlan?->code ?? '',
                $client->billingPlan?->name ?? '',
                $client->billingPlan?->upload_speed_kbps ?? '',
                $client->billingPlan?->download_speed_kbps ?? '',
                $client->status,
                $client->connection_status ?? 'unknown',
                number_format((float)$client->wallet_balance, 2, '.', ''),
                $client->current_cycle_end ? $client->current_cycle_end->toDateString() : '',
                $client->site?->code ?? '',
                $client->mikrotikDevice?->name ?? '',
                $client->address ?? '',
            ];

            $lines[] = implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row));
        }

        $filename = 'smartoh_clients_export_' . now()->format('Ymd_His') . '.csv';

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * High-speed self-service Client Import & MikroTik Router Migration.
     * Supports:
     * 1. CSV/Spreadsheet upload or raw CSV paste (PPPoE, Hotspot, and Static IP clients)
     * 2. Direct MikroTik router discovery or RouterOS (.rsc) script import
     * Automatically provisions and restores client connections on the target MikroTik router.
     */
    public function import(Request $request, MikroTikService $mikrotik)
    {
        $startTime = microtime(true);

        $validated = $request->validate([
            'import_mode'             => 'nullable|in:csv,router_pull',
            'clients_file'            => 'nullable|file|max:10240',
            'csv_payload'             => 'nullable|string',
            'rsc_file'                => 'nullable|file|max:10240',
            'rsc_payload'             => 'nullable|string',
            'mikrotik_device_id'      => 'nullable|exists:mikrotik_devices,id',
            'default_billing_plan_id' => 'nullable|exists:billing_plans,id',
            'site_id'                 => 'nullable|exists:sites,id',
            'duplicate_mode'          => 'nullable|in:update,skip',
            'restore_connections'     => 'nullable|boolean',
        ]);

        $importMode = $validated['import_mode'] ?? 'csv';
        $duplicateMode = $validated['duplicate_mode'] ?? 'update';
        $restoreConnections = $request->boolean('restore_connections', true);

        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id;
        $targetRouter = !empty($validated['mikrotik_device_id'])
            ? MikroTikDevice::find($validated['mikrotik_device_id'])
            : MikroTikDevice::where('is_online', true)->first();

        $defaultSite = !empty($validated['site_id'])
            ? Site::find($validated['site_id'])
            : ($targetRouter?->site_id ? Site::find($targetRouter->site_id) : Site::first());

        $plans = BillingPlan::where('is_active', true)->get();
        $defaultPlan = !empty($validated['default_billing_plan_id'])
            ? BillingPlan::find($validated['default_billing_plan_id'])
            : $plans->first();

        if (!$defaultPlan) {
            $defaultPlan = BillingPlan::create([
                'tenant_id'             => $tenantId,
                'name'                  => 'Migrated Standard Plan 10M',
                'code'                  => 'MIG-10M',
                'type'                  => 'monthly',
                'price'                 => 2500,
                'currency'              => 'KES',
                'download_speed_kbps'   => 10240,
                'upload_speed_kbps'     => 10240,
                'validity_days'         => 30,
                'mikrotik_profile_name' => 'plan_10M',
                'is_active'             => true,
            ]);
            $plans->push($defaultPlan);
        }

        // Build fast lookup maps for plans
        $plansByCode = [];
        $plansByName = [];
        $plansByProfile = [];
        foreach ($plans as $p) {
            if ($p->code) {
                $plansByCode[strtolower(trim($p->code))] = $p;
            }
            if ($p->name) {
                $plansByName[strtolower(trim($p->name))] = $p;
            }
            if ($p->mikrotik_profile_name) {
                $plansByProfile[strtolower(trim($p->mikrotik_profile_name))] = $p;
            }
        }

        // Parse rows based on mode
        $rawRecords = [];
        if ($importMode === 'router_pull') {
            $rscText = $validated['rsc_payload'] ?? '';
            if ($request->hasFile('rsc_file')) {
                $rscText = (string) file_get_contents($request->file('rsc_file')->getRealPath());
            }
            if (!$targetRouter && trim($rscText) === '') {
                return back()->with('error', 'Please select a target MikroTik router or provide a RouterOS (.rsc) export file.');
            }
            $routerForDiscovery = $targetRouter ?? new MikroTikDevice(['name' => 'Imported-Router', 'identity' => 'MK']);
            $rawRecords = $mikrotik->discoverRouterSubscribers($routerForDiscovery, $rscText);
        } else {
            $csvText = $validated['csv_payload'] ?? '';
            if ($request->hasFile('clients_file')) {
                $csvText = (string) file_get_contents($request->file('clients_file')->getRealPath());
            }
            if (trim($csvText) === '') {
                return back()->with('error', 'Please upload a CSV file or paste subscriber rows to import.');
            }
            $rawRecords = $this->parseClientsCsv($csvText);
        }

        if (empty($rawRecords)) {
            return back()->with('error', 'No valid subscriber rows were found in the provided import source.');
        }

        // Preload existing tenant clients for O(1) duplicate resolution
        $existingClients = Client::all();
        $byAccount = [];
        $byUsername = [];
        $byIp = [];
        foreach ($existingClients as $c) {
            if ($c->account_number) {
                $byAccount[strtolower($c->account_number)] = $c;
            }
            if ($c->pppoe_username) {
                $byUsername[strtolower($c->pppoe_username)] = $c;
            }
            if ($c->assigned_ip) {
                $byIp[$c->assigned_ip] = $c;
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $typeBreakdown = ['pppoe' => 0, 'hotspot' => 0, 'static' => 0];
        $migratedClients = [];

        DB::transaction(function () use (
            $rawRecords, $tenantId, $defaultSite, $targetRouter, $defaultPlan,
            $plansByCode, $plansByName, $plansByProfile,
            &$byAccount, &$byUsername, &$byIp,
            $duplicateMode, &$createdCount, &$updatedCount, &$skippedCount,
            &$typeBreakdown, &$migratedClients
        ) {
            foreach ($rawRecords as $idx => $row) {
                $rawServiceType = strtolower(trim((string)($row['service_type'] ?? $row['type'] ?? 'pppoe')));
                if (in_array($rawServiceType, ['static', 'static_ip', 'static-ip', 'dhcp', 'dedicated'], true)) {
                    $serviceType = 'static';
                } elseif (in_array($rawServiceType, ['hotspot', 'captive', 'voucher', 'wifi'], true)) {
                    $serviceType = 'hotspot';
                } else {
                    $serviceType = 'pppoe';
                }

                $name = trim((string)($row['name'] ?? ''));
                if ($name === '' && (!empty($row['first_name']) || !empty($row['last_name']))) {
                    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                }

                $username = trim((string)($row['pppoe_username'] ?? $row['username'] ?? ''));
                $assignedIp = trim((string)($row['assigned_ip'] ?? $row['ip_address'] ?? $row['ip'] ?? ''));
                $accountNum = trim((string)($row['account_number'] ?? $row['account'] ?? ''));

                if ($name === '' && $username !== '') {
                    $name = ucwords(str_replace(['_', '.', '-'], ' ', $username));
                } elseif ($name === '' && $assignedIp !== '') {
                    $name = 'Static Subscriber ' . $assignedIp;
                }

                if ($name === '' && $username === '' && $assignedIp === '') {
                    $skippedCount++;
                    continue;
                }

                if ($username === '') {
                    $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $name ?: 'user')) . '_' . ($idx + 1);
                }

                // Resolve existing client by account_number, pppoe_username, or assigned_ip
                $existing = null;
                if ($accountNum !== '' && isset($byAccount[strtolower($accountNum)])) {
                    $existing = $byAccount[strtolower($accountNum)];
                } elseif ($username !== '' && isset($byUsername[strtolower($username)])) {
                    $existing = $byUsername[strtolower($username)];
                } elseif ($assignedIp !== '' && isset($byIp[$assignedIp])) {
                    $existing = $byIp[$assignedIp];
                }

                if ($existing && $duplicateMode === 'skip') {
                    $skippedCount++;
                    continue;
                }

                // Match BillingPlan by plan_code, plan_name, or router profile
                $planKeyCode = strtolower(trim((string)($row['plan_code'] ?? '')));
                $planKeyName = strtolower(trim((string)($row['plan_name'] ?? $row['plan'] ?? '')));
                $planKeyProf = strtolower(trim((string)($row['profile'] ?? '')));
                $matchedPlan = ($planKeyCode !== '' ? ($plansByCode[$planKeyCode] ?? null) : null)
                    ?? ($planKeyName !== '' ? ($plansByName[$planKeyName] ?? null) : null)
                    ?? ($planKeyProf !== '' ? ($plansByProfile[$planKeyProf] ?? null) : null)
                    ?? $defaultPlan;

                $statusRaw = strtolower(trim((string)($row['status'] ?? 'active')));
                $status = in_array($statusRaw, ['active', 'suspended', 'expired', 'fup_throttled', 'overdue'], true)
                    ? $statusRaw
                    : (in_array($statusRaw, ['disabled', 'inactive', 'blocked'], true) ? 'suspended' : 'active');

                $expiryDate = null;
                $expiryInput = trim((string)($row['expiry_date'] ?? $row['current_cycle_end'] ?? ''));
                if ($expiryInput !== '') {
                    try {
                        $expiryDate = Carbon::parse($expiryInput);
                    } catch (\Throwable) {
                        $expiryDate = now()->addDays($matchedPlan->validity_days ?: 30);
                    }
                } else {
                    $expiryDate = now()->addDays($matchedPlan->validity_days ?: 30);
                }

                $phone = trim((string)($row['phone'] ?? $row['phone_number'] ?? ''));
                if ($phone === '') {
                    $phone = $existing?->phone ?: '254700000000';
                }

                $payload = [
                    'tenant_id'           => $tenantId,
                    'site_id'             => $defaultSite?->id ?? $existing?->site_id,
                    'mikrotik_device_id'  => $targetRouter?->id ?? $existing?->mikrotik_device_id,
                    'billing_plan_id'     => $matchedPlan->id,
                    'name'                => $name,
                    'phone'               => $phone,
                    'email'               => !empty($row['email']) ? trim((string)$row['email']) : $existing?->email,
                    'service_type'        => $serviceType,
                    'pppoe_username'      => $username,
                    'pppoe_password'      => !empty($row['pppoe_password'] ?? $row['password'] ?? null)
                        ? trim((string)($row['pppoe_password'] ?? $row['password']))
                        : ($existing?->pppoe_password ?: 'Smartoh@123'),
                    'assigned_ip'         => $assignedIp !== '' ? $assignedIp : $existing?->assigned_ip,
                    'mac_address'         => !empty($row['mac_address'] ?? $row['mac'] ?? null)
                        ? strtoupper(trim((string)($row['mac_address'] ?? $row['mac'])))
                        : $existing?->mac_address,
                    'status'              => $status,
                    'wallet_balance'      => isset($row['wallet_balance']) && is_numeric($row['wallet_balance'])
                        ? (float)$row['wallet_balance']
                        : ($existing?->wallet_balance ?? 0.00),
                    'current_cycle_start' => $existing?->current_cycle_start ?? now(),
                    'current_cycle_end'   => $expiryDate,
                    'address'             => !empty($row['address']) ? trim((string)$row['address']) : $existing?->address,
                ];

                if ($existing) {
                    $existing->update($payload);
                    $existing->setRelation('billingPlan', $matchedPlan);
                    $clientModel = $existing;
                    $updatedCount++;
                } else {
                    $finalAccount = $accountNum !== '' ? $accountNum : ('MIG-' . strtoupper(Str::random(6)));
                    while (isset($byAccount[strtolower($finalAccount)])) {
                        $finalAccount = 'MIG-' . strtoupper(Str::random(6));
                    }
                    $payload['account_number'] = $finalAccount;
                    $clientModel = Client::create($payload);
                    $clientModel->setRelation('billingPlan', $matchedPlan);
                    $byAccount[strtolower($finalAccount)] = $clientModel;
                    $byUsername[strtolower($username)] = $clientModel;
                    if ($assignedIp !== '') {
                        $byIp[$assignedIp] = $clientModel;
                    }
                    $createdCount++;
                }

                $typeBreakdown[$serviceType]++;
                $migratedClients[] = $clientModel;
            }
        });

        // Immediately provision & restore connections on target MikroTik router if requested
        $restoreSummary = null;
        if ($restoreConnections && $targetRouter && !empty($migratedClients)) {
            $restoreSummary = $mikrotik->batchMigrateAndRestoreClients($targetRouter, $migratedClients);
        }

        $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

        AuditLog::create([
            'tenant_id'  => $tenantId,
            'user_id'    => auth()->id(),
            'event'      => 'clients.bulk_migrated',
            'new_values' => [
                'import_mode'      => $importMode,
                'created'          => $createdCount,
                'updated'          => $updatedCount,
                'skipped'          => $skippedCount,
                'by_service_type'  => $typeBreakdown,
                'router_restored'  => $restoreSummary['restored_count'] ?? 0,
                'target_router_id' => $targetRouter?->id,
                'elapsed_ms'       => $elapsedMs,
            ],
            'ip_address' => $request->ip(),
        ]);

        $restoredMsg = $restoreSummary
            ? " Restored {$restoreSummary['restored_count']} connections on {$targetRouter->name} ({$elapsedMs}ms)."
            : " Completed in {$elapsedMs}ms.";

        $message = "Migration complete: {$createdCount} imported, {$updatedCount} updated, {$skippedCount} skipped "
            . "(PPPoE: {$typeBreakdown['pppoe']}, Hotspot: {$typeBreakdown['hotspot']}, Static IP: {$typeBreakdown['static']})."
            . $restoredMsg;

        if ($request->expectsJson()) {
            return response()->json([
                'status'          => 'ok',
                'message'         => $message,
                'created'         => $createdCount,
                'updated'         => $updatedCount,
                'skipped'         => $skippedCount,
                'by_service_type' => $typeBreakdown,
                'restore_summary' => $restoreSummary,
                'elapsed_ms'      => $elapsedMs,
            ]);
        }

        return redirect()->route('clients.index')->with('success', $message);
    }

    /**
     * 1-Click Bulk Connection Restoration for Static IP, PPPoE, and Hotspot clients when integrating a new MikroTik router.
     */
    public function restoreConnections(Request $request, MikroTikService $mikrotik)
    {
        $validated = $request->validate([
            'mikrotik_device_id'   => 'nullable|exists:mikrotik_devices,id',
            'service_type'         => 'nullable|in:all,pppoe,hotspot,static',
            'reactivate_suspended' => 'nullable|boolean',
        ]);

        $router = !empty($validated['mikrotik_device_id'])
            ? MikroTikDevice::find($validated['mikrotik_device_id'])
            : MikroTikDevice::where('is_online', true)->first();

        if (!$router) {
            $router = MikroTikDevice::first();
        }

        if (!$router) {
            return back()->with('error', 'No MikroTik router found. Please add a MikroTik router first.');
        }

        $serviceType = $validated['service_type'] ?? 'all';
        $reactivateSuspended = $request->boolean('reactivate_suspended', false);

        $query = Client::with('billingPlan');

        if ($serviceType === 'static') {
            $query->whereIn('service_type', ['static', 'dhcp']);
        } elseif (in_array($serviceType, ['pppoe', 'hotspot'], true)) {
            $query->where('service_type', $serviceType);
        }

        $clients = $query->get();

        if ($clients->isEmpty()) {
            return back()->with('error', 'No matching subscribers found to restore connections.');
        }

        if ($reactivateSuspended) {
            Client::whereIn('id', $clients->pluck('id'))->update([
                'status'               => 'active',
                'service_suspended_at' => null,
            ]);
            foreach ($clients as $c) {
                $c->status = 'active';
            }
        }

        $summary = $mikrotik->batchMigrateAndRestoreClients($router, $clients);

        AuditLog::create([
            'tenant_id'  => app()->bound('current_tenant_id') ? app('current_tenant_id') : auth()->user()?->tenant_id,
            'user_id'    => auth()->id(),
            'event'      => 'clients.connections_restored',
            'new_values' => $summary,
            'ip_address' => $request->ip(),
        ]);

        $msg = $summary['success']
            ? "Connections restored on {$router->name} in {$summary['elapsed_ms']}ms: {$summary['restored_count']} active connections verified (PPPoE: {$summary['by_service_type']['pppoe']}, Hotspot: {$summary['by_service_type']['hotspot']}, Static IP: {$summary['by_service_type']['static']})."
            : "Router {$router->name} is currently offline: {$summary['queued_offline']} subscriber connections bound and queued for automatic restoration.";

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'ok',
                'message' => $msg,
                'summary' => $summary,
            ]);
        }

        return redirect()->route('clients.index')->with('success', $msg);
    }

    public function show(Client $client)
    {
        $client->load(['billingPlan', 'site', 'invoices.items', 'payments', 'supportTickets', 'clientRouter', 'ontDevice.olt']);
        return view('clients.show', compact('client'));
    }

    public function toggleStatus(Client $client, Request $request, MikroTikService $mikrotik)
    {
        $newStatus = $client->status === 'active' ? 'suspended' : 'active';
        $client->update([
            'status' => $newStatus,
            'service_suspended_at' => $newStatus === 'suspended' ? now() : null,
        ]);

        $router = $client->mikrotikDevice ?? MikroTikDevice::where('is_online', true)->first();
        if ($router) {
            $mikrotik->setClientStatus($router, $client, $newStatus);
        }

        AuditLog::create([
            'tenant_id' => $client->tenant_id,
            'user_id' => auth()->id(),
            'event' => "client.{$newStatus}",
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'new_values' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', "Subscriber account {$client->account_number} changed to {$newStatus}.");
    }

    public function generateBill(Client $client, BillingEngine $engine)
    {
        $invoice = $engine->generateInvoice($client);
        return back()->with('success', "Invoice {$invoice->invoice_number} generated for KES " . number_format($invoice->total_amount, 2));
    }

    /**
     * Parse CSV text into associative rows with flexible header matching.
     */
    private function parseClientsCsv(string $csvText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvText));
        $rows = [];
        $headers = null;

        $knownHeaderAliases = [
            'account_number', 'account', 'name', 'full_name', 'first_name', 'last_name',
            'phone', 'phone_number', 'email', 'service_type', 'type',
            'pppoe_username', 'username', 'pppoe_password', 'password',
            'assigned_ip', 'ip_address', 'ip', 'mac_address', 'mac',
            'plan_code', 'plan_name', 'plan', 'status', 'expiry_date', 'current_cycle_end', 'address'
        ];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if (empty($cols)) {
                continue;
            }

            if ($headers === null) {
                $normalizedCols = array_map(fn($c) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string)$c))), $cols);
                $intersect = array_intersect($normalizedCols, $knownHeaderAliases);
                if (count($intersect) >= 2) {
                    $headers = $normalizedCols;
                    continue;
                }
                // Positional fallback if no header row was provided
                $headers = [
                    'account_number', 'name', 'phone', 'email', 'service_type',
                    'pppoe_username', 'pppoe_password', 'assigned_ip', 'mac_address',
                    'plan_code', 'status', 'expiry_date', 'address'
                ];
            }

            $assoc = [];
            foreach ($headers as $i => $key) {
                if ($key !== '' && isset($cols[$i])) {
                    $assoc[$key] = trim((string)$cols[$i]);
                }
            }
            if (!empty($assoc)) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }
}
