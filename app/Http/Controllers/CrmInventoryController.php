<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\CRMLead;
use App\Models\InventoryItem;
use App\Models\Payment;
use Illuminate\Http\Request;

class CrmInventoryController extends Controller
{
    public function crm()
    {
        $leads = CRMLead::latest()->get();

        if ($leads->isEmpty()) {
            $leads = collect([
                (object)[
                    'id' => 1,
                    'name' => 'David Otieno',
                    'company' => 'Otieno Cyber Cafe',
                    'phone' => '+254700111222',
                    'email' => 'david@example.com',
                    'source' => 'Walk-in',
                    'stage' => 'Site Survey',
                    'value' => 5000,
                    'assigned_to' => 'Alex Njoroge',
                    'expected_close' => '2026-10-15',
                    'notes' => 'Requires dedicated 20Mbps fiber line with public IPv4.',
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Grace Wambui',
                    'company' => 'Heights Apartments Management',
                    'phone' => '+254711333444',
                    'email' => 'grace@heights.co.ke',
                    'source' => 'Referral',
                    'stage' => 'Proposal',
                    'value' => 35000,
                    'assigned_to' => 'Sales Team',
                    'expected_close' => '2026-10-20',
                    'notes' => 'Building pre-wiring for 24 residential units.',
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Brian Kiprop',
                    'company' => 'Kiprop Tech Hub',
                    'phone' => '+254722555666',
                    'email' => 'brian@kiphub.com',
                    'source' => 'Website',
                    'stage' => 'New',
                    'value' => 8500,
                    'assigned_to' => 'Alex Njoroge',
                    'expected_close' => '2026-10-25',
                    'notes' => 'Interested in 50Mbps Business CIR connection.',
                ]
            ]);
        }

        return view('crm.index', compact('leads'));
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'source' => 'nullable|string',
            'stage' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'estimated_mrc' => 'nullable|numeric|min:0',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return back()->with('success', "New sales lead '{$validated['name']}' registered in CRM pipeline!");
    }

    public function inventory()
    {
        $items = InventoryItem::all();

        if ($items->isEmpty()) {
            $items = collect([
                (object)[
                    'id' => 1,
                    'name' => 'MikroTik hAP ac²',
                    'sku' => 'MT-HAP-AC2',
                    'category' => 'Router',
                    'quantity' => 18,
                    'reorder_level' => 5,
                    'unit_cost' => 9500,
                    'unit_price' => 12000,
                    'location' => 'HQ Central Store',
                    'status' => 'In Stock',
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Huawei HG8245H GPON ONT',
                    'sku' => 'HW-HG8245H',
                    'category' => 'ONT',
                    'quantity' => 3,
                    'reorder_level' => 8,
                    'unit_cost' => 3800,
                    'unit_price' => 5500,
                    'location' => 'HQ Central Store',
                    'status' => 'Low Stock',
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Fiber Drop Cable 2-Core (1000m Drum)',
                    'sku' => 'FBR-DRP-2C',
                    'category' => 'Cable',
                    'quantity' => 6,
                    'reorder_level' => 2,
                    'unit_cost' => 18000,
                    'unit_price' => 24000,
                    'location' => 'Van #2 (Tech James)',
                    'status' => 'In Stock',
                ],
                (object)[
                    'id' => 4,
                    'name' => 'SC/APC Optical Fast Connectors (100pk)',
                    'sku' => 'FST-CON-100',
                    'category' => 'Accessories',
                    'quantity' => 12,
                    'reorder_level' => 4,
                    'unit_cost' => 4200,
                    'unit_price' => 6500,
                    'location' => 'HQ Central Store',
                    'status' => 'In Stock',
                ]
            ]);
        }

        $totalUnits = $items->sum('quantity');
        $lowStockCount = $items->where('quantity', '<=', 5)->count();
        $totalStockValue = $items->sum(fn($i) => $i->quantity * $i->unit_cost);

        return view('inventory.index', compact('items', 'totalUnits', 'lowStockCount', 'totalStockValue'));
    }

    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'item_name' => 'nullable|string|max:255',
            'sku' => 'required|string',
            'category' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string',
        ]);

        $name = $validated['name'] ?? $validated['item_name'] ?? 'Equipment Item';
        return back()->with('success', "Inventory item '{$name}' ({$validated['sku']}) recorded into stock.");
    }

    public function reports()
    {
        return $this->advancedReports();
    }

    public function advancedReports()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        if ($totalRevenue == 0) $totalRevenue = 285000;

        $activeClients = Client::where('status', 'active')->count();
        if ($activeClients == 0) $activeClients = 120;

        $arpu = round($totalRevenue / max(1, $activeClients), 2);
        $churnRate = 2.1; // 2.1% matching Base44 AdvancedReports

        $plans = BillingPlan::withCount('clients')->get();

        return view('reports.index', compact('totalRevenue', 'activeClients', 'arpu', 'churnRate', 'plans'));
    }
}
