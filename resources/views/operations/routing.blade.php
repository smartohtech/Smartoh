@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ ospfModal: false, bgpModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">Routing Protocols</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 font-mono">Dynamic IGP &amp; EGP</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">OSPF &amp; BGP Dynamic Routing Protocols</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">RouterOS v7 dynamic routing instances, upstream transit peering, and internal core area redistribution.</p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="ospfModal = true" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-600/20 flex items-center space-x-1.5 transition">
                <span>+ OSPF Wizard</span>
            </button>
            <button @click="bgpModal = true" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-600/20 flex items-center space-x-1.5 transition">
                <span>+ BGP Peering</span>
            </button>
        </div>
    </div>

    <!-- OSPF & BGP Tables (Matching Base44 RoutingConfig.js) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- OSPF Instances -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">OSPF IGP Instances</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Area 0.0.0.0 backbone &amp; intra-POP routing</p>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-bold font-mono bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">Active</span>
            </div>

            <div class="p-4 space-y-3">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-2 text-xs font-mono">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">ospf-instance-core</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Router ID: 10.0.0.1</span>
                    </div>
                    <div class="flex items-center space-x-4 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Area: 0.0.0.0 (Backbone)</span>
                        <span>•</span>
                        <span>Redistribute: Connected + Static</span>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-2 text-xs font-mono">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">ospf-instance-distribution</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Router ID: 10.0.1.1</span>
                    </div>
                    <div class="flex items-center space-x-4 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Area: 0.0.0.1 (North Tower)</span>
                        <span>•</span>
                        <span>Cost: 10 • Auth: MD5</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BGP Peering -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">BGP Transit &amp; IXP Peers</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">KIXP &amp; Upstream Tier-1 Carriers</p>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-bold font-mono bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">Local AS37021</span>
            </div>

            <div class="p-4 space-y-3">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-2 text-xs font-mono">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">Safaricom Upstream Tier-1</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Remote AS33771</span>
                    </div>
                    <div class="flex items-center space-x-4 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Peer IP: 196.201.214.1</span>
                        <span>•</span>
                        <span>State: Established (Prefixes: 42,100)</span>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-xl border border-slate-100 dark:border-slate-800 space-y-2 text-xs font-mono">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">KIXP Peering Fabric</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Remote AS37100</span>
                    </div>
                    <div class="flex items-center space-x-4 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Peer IP: 196.223.21.12</span>
                        <span>•</span>
                        <span>State: Established (Local routes exchanged)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- OSPF Modal -->
    <div x-show="ospfModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="ospfModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">OSPF Setup Wizard</h3>
            <form method="POST" action="{{ route('routing.ospf.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Instance Name *</label>
                    <input type="text" name="instance_name" value="ospf-instance-pop" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">OSPF Router ID *</label>
                    <input type="text" name="router_id" placeholder="e.g. 10.0.0.1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>
                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="ospfModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl">Deploy OSPF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- BGP Modal -->
    <div x-show="bgpModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div @click.away="bgpModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Add BGP Peering Session</h3>
            <form method="POST" action="{{ route('routing.bgp.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Peer Name *</label>
                    <input type="text" name="peer_name" placeholder="e.g. Liquid Telecom IP Transit" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Remote AS Number *</label>
                    <input type="number" name="remote_as" placeholder="e.g. 30844" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Remote Peer IP *</label>
                    <input type="text" name="remote_ip" placeholder="196.200.x.x" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono">
                </div>
                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" @click="bgpModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl">Establish BGP</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
