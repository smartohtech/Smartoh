@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">GIS Fiber Infrastructure &amp; Subscriber Network Map</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-1">Geospatial fiber routes, Fiber Access Terminals (FAT boxes), and customer drops.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('gis.jobs') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                <span>View Field Work Orders</span>
            </a>
        </div>
    </div>

    <!-- Map Container Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden p-4 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-mono font-semibold text-slate-700 dark:text-slate-200">
            <div class="flex items-center space-x-4">
                <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span><span>POP / Core Site</span></span>
                <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span>FAT Distribution Box</span></span>
                <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-cyan-400"></span><span>Subscriber Drop</span></span>
                <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span><span>Scheduled Installation</span></span>
            </div>
            <div class="text-slate-600 dark:text-slate-400">Geospatial CRS: EPSG:4326 (WGS 84)</div>
        </div>

        <div id="gisMap" class="w-full h-[580px] rounded-xl border border-slate-200 dark:border-slate-800 z-10"></div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.L) return;

        // Initialize map centered at Nairobi Upper Hill
        const map = L.map('gisMap').setView([-1.2985, 36.8142], 16);

        // Dark-mode themed CartoDB tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            maxZoom: 19
        }).addTo(map);

        // 1. Plot POPs & FAT Boxes
        @foreach($infrastructure as $infra)
            @if($infra->latitude && $infra->longitude)
                L.circleMarker([{{ $infra->latitude }}, {{ $infra->longitude }}], {
                    radius: 9,
                    fillColor: '{{ $infra->type === "pop" ? "#3b82f6" : "#10b981" }}',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map).bindPopup(`
                    <div style="font-family: monospace; font-size: 12px; color: #0f172a;">
                        <strong>{{ $infra->name }}</strong><br>
                        Type: {{ strtoupper($infra->type) }} ({{ $infra->code }})<br>
                        Cores: {{ $infra->used_cores }} / {{ $infra->capacity_cores }} Used<br>
                        Status: <span style="color: green; font-weight: bold;">{{ strtoupper($infra->status) }}</span>
                    </div>
                `);
            @endif
        @endforeach

        // 2. Plot Clients (Subscribers)
        @foreach($clients as $cli)
            L.circleMarker([{{ $cli->latitude }}, {{ $cli->longitude }}], {
                radius: 6,
                fillColor: '{{ $cli->status === "active" ? "#06b6d4" : "#ef4444" }}',
                color: '#ffffff',
                weight: 1.5,
                opacity: 1,
                fillOpacity: 0.85
            }).addTo(map).bindPopup(`
                <div style="font-family: monospace; font-size: 12px; color: #0f172a;">
                    <strong>{{ $cli->name }}</strong><br>
                    Account: {{ $cli->account_number }}<br>
                    Status: {{ strtoupper($cli->status) }}<br>
                    PPPoE: {{ $cli->pppoe_username }}<br>
                    Address: {{ $cli->address }}
                </div>
            `);
        @endforeach

        // 3. Connect FAT-04 to Client 1 with simulated fiber cable line
        const latlngs = [
            [-1.2982, 36.8145], // FAT-04
            [-1.2983, 36.8146]  // Dr. Kariuki
        ];
        L.polyline(latlngs, { color: '#06b6d4', weight: 3, opacity: 0.8, dashArray: '4, 4' }).addTo(map)
            .bindPopup("Drop Cable: 85 meters (Attenuation: 0.18 dB)");
    });
</script>
@endsection
