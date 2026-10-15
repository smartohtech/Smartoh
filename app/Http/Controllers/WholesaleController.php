<?php

namespace App\Http\Controllers;

use App\Models\WholesaleCapacityPool;
use App\Models\WholesaleOrder;
use App\Models\WholesaleProduct;
use App\Models\WholesaleWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WholesaleController extends Controller
{
    public function index()
    {
        $pools = WholesaleCapacityPool::with('products')->get();
        $products = WholesaleProduct::with('capacityPool')->where('is_active', true)->get();
        $wallets = WholesaleWallet::with('orders')->get();
        $orders = WholesaleOrder::with(['wallet', 'product'])->latest()->get();

        $totalBandwidthCapacity = WholesaleCapacityPool::sum('total_capacity_mbps');
        $totalAllocatedCapacity = WholesaleCapacityPool::sum('allocated_capacity_mbps');
        $totalAvailableCapacity = max(0, $totalBandwidthCapacity - $totalAllocatedCapacity);

        return view('wholesale.index', compact(
            'pools',
            'products',
            'wallets',
            'orders',
            'totalBandwidthCapacity',
            'totalAllocatedCapacity',
            'totalAvailableCapacity'
        ));
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'wholesale_wallet_id' => 'required|exists:wholesale_wallets,id',
            'wholesale_product_id' => 'required|exists:wholesale_products,id',
            'bandwidth_mbps' => 'required|integer|min:10',
        ]);

        $product = WholesaleProduct::findOrFail($validated['wholesale_product_id']);
        $monthlyCharge = $validated['bandwidth_mbps'] * $product->price_per_mbps;

        $order = WholesaleOrder::create([
            'wholesale_wallet_id' => $validated['wholesale_wallet_id'],
            'wholesale_product_id' => $product->id,
            'order_number' => 'WSO-' . strtoupper(Str::random(6)),
            'bandwidth_mbps' => $validated['bandwidth_mbps'],
            'monthly_charge' => $monthlyCharge,
            'status' => 'approved',
            'vlan_id' => rand(100, 999),
            'allocated_subnet' => '102.215.' . rand(10, 80) . '.0/29',
            'activated_at' => now(),
        ]);

        $product->capacityPool->increment('allocated_capacity_mbps', $validated['bandwidth_mbps']);

        return back()->with('success', "Wholesale Service Order {$order->order_number} provisioned ({$order->bandwidth_mbps} Mbps on VLAN {$order->vlan_id})!");
    }
}
