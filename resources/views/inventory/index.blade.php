@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addItemModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Operations Supply</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">Stock Audit Sync</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">Inventory &amp; Stock Tracking</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track routers, ONTs, drop cables, wireless antennas, SFP modules, and technician van inventories.</p>
        </div>

        <button @click="addItemModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Add Inventory Item</span>
        </button>
    </div>

    <!-- Inventory KPI Counters (Matching Base44 Inventory.js) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Units in Stock</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalUnits }}</div>
            <div class="text-xs text-slate-400 mt-1">Across central stores &amp; field vans</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Low Stock Thresholds</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $lowStockCount }} Items</div>
            <div class="text-xs text-rose-500/80 mt-1">Below minimum reorder level</div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Stock Valuation</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 font-mono">KES {{ number_format($totalStockValue, 2) }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Capital asset value</div>
        </div>

    </div>

    <!-- Inventory Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Stock Catalog</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $items->count() }} Stock Items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4">Item Name</th>
                        <th class="p-4">SKU / Code</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Quantity in Stock</th>
                        <th class="p-4">Unit Cost</th>
                        <th class="p-4">Retail Price</th>
                        <th class="p-4">Store Location</th>
                        <th class="p-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @foreach($items as $it)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="p-4 font-sans font-bold text-slate-900 dark:text-white">{{ $it->name }}</td>
                        <td class="p-4 text-blue-600 dark:text-blue-400">{{ $it->sku }}</td>
                        <td class="p-4 font-sans text-slate-600 dark:text-slate-300">{{ $it->category }}</td>
                        <td class="p-4 font-bold {{ $it->quantity <= 5 ? 'text-rose-500' : 'text-slate-900 dark:text-white' }}">{{ $it->quantity }} units</td>
                        <td class="p-4">KES {{ number_format($it->unit_cost) }}</td>
                        <td class="p-4 text-emerald-600 dark:text-emerald-400 font-bold">KES {{ number_format($it->unit_price) }}</td>
                        <td class="p-4 font-sans text-slate-400">{{ $it->location }}</td>
                        <td class="p-4 text-right">
                            @if($it->quantity <= 5)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 uppercase">Low Stock</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="addItemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="addItemModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Add Equipment to Inventory</h3>
            <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Item Name *</label>
                    <input type="text" name="name" required placeholder="e.g. MikroTik hAP ac³" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">SKU / Part Number *</label>
                        <input type="text" name="sku" required placeholder="e.g. MT-HAP-AC3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                        <select name="category" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                            <option value="Router">Router</option>
                            <option value="ONT">ONT / ONU</option>
                            <option value="Cable">Fiber Cable</option>
                            <option value="Antenna">Wireless Antenna</option>
                            <option value="Accessories">Patch Cords / SFP</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Quantity *</label>
                        <input type="number" name="quantity" value="10" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Unit Cost (KES) *</label>
                        <input type="number" name="unit_cost" value="9500" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Retail Price (KES) *</label>
                        <input type="number" name="unit_price" value="12500" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="addItemModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl">Save to Stock</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
