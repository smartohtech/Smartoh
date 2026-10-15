<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\MikroTikDevice;
use App\Models\Site;
use App\Services\Billing\BillingEngine;
use App\Services\MikroTik\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['billingPlan', 'site'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('pppoe_username', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($siteId = $request->get('site_id')) {
            $query->where('site_id', $siteId);
        }

        $clients = $query->paginate(15);
        $sites = Site::all();
        $plans = BillingPlan::where('is_active', true)->get();

        $statusCounts = [
            'all' => Client::count(),
            'active' => Client::where('status', 'active')->count(),
            'suspended' => Client::where('status', 'suspended')->count(),
            'expired' => Client::where('status', 'expired')->count(),
            'fup_throttled' => Client::where('status', 'fup_throttled')->count(),
            'overdue' => Client::where('status', 'overdue')->count(),
        ];

        return view('clients.index', compact('clients', 'sites', 'plans', 'statusCounts'));
    }

    public function store(Request $request, MikroTikService $mikrotik)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'site_id' => 'nullable|exists:sites,id',
            'billing_plan_id' => 'required|exists:billing_plans,id',
            'service_type' => 'required|in:pppoe,hotspot,static,dhcp',
            'pppoe_username' => 'nullable|string|unique:clients,pppoe_username',
            'pppoe_password' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $site = $validated['site_id'] ? Site::find($validated['site_id']) : Site::first();
        $accountNumber = 'CLI-' . strtoupper(Str::random(6));

        $plan = BillingPlan::findOrFail($validated['billing_plan_id']);

        $client = Client::create(array_merge($validated, [
            'account_number' => $accountNumber,
            'status' => 'active',
            'pppoe_username' => $validated['pppoe_username'] ?: strtolower(str_replace(' ', '', $validated['name'])) . rand(10, 99),
            'pppoe_password' => $validated['pppoe_password'] ?: Str::random(8),
            'current_cycle_start' => now(),
            'current_cycle_end' => now()->addDays($plan->validity_days),
        ]));

        // Sync to first online router if available
        $router = MikroTikDevice::where('is_online', true)->first();
        if ($router) {
            $mikrotik->syncPppoeSecret($router, $client);
        }

        AuditLog::create([
            'tenant_id' => $client->tenant_id,
            'user_id' => auth()->id(),
            'event' => 'client.created',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'new_values' => ['name' => $client->name, 'account' => $client->account_number],
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', "Subscriber {$client->name} registered successfully!");
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

        $router = MikroTikDevice::where('is_online', true)->first();
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
}
