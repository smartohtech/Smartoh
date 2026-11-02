@extends('layouts.app')

@section('content')
<div class="space-y-5">

    <!-- Top Header & Multitenant / Support Context Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Kenya National GIS • EPSG:4326 (WGS 84)
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                    47 Constitutional Counties
                </span>
                @if($context['mode'] === 'platform_overview')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        Super Admin Platform Overview (Tenant Private Assets Isolated)
                    </span>
                @elseif($context['mode'] === 'tenant_support')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                        Audited Support Session: {{ $activeTenant?->name }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                        Tenant Workspace: {{ $activeTenant?->name ?? 'ISP Network' }}
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-1.5">
                Kenya Network Map &amp; GIS Fiber Infrastructure
            </h1>
            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 mt-0.5">
                Interactive national geospatial topology across all 47 Kenyan counties — PoPs, fiber routes, splice closures, manholes, cabinets, OLTs, ONUs/ONTs, towers, wireless links, routers &amp; customer drops.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($context['can_edit'])
                <button type="button" id="btnOpenAddAssetModal"
                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add GIS Asset / Route</span>
                </button>
            @endif

            @if($context['can_delete_export'])
                <a href="{{ route('gis.export') }}" target="_blank"
                    class="px-3.5 py-2 bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export GeoJSON</span>
                </a>
            @endif

            <a href="{{ route('gis.jobs') }}"
                class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                <span>View Field Work Orders ({{ $jobs->count() }})</span>
            </a>

            <a href="{{ route('noc.index') }}"
                class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 transition">
                <span>NOC Center ({{ $nocEvents->count() }} Alerts)</span>
            </a>
        </div>
    </div>

    <!-- Super Admin Audited Support Workflow Banner -->
    @if($context['is_super_admin'])
        <div class="bg-amber-50/90 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800/70 rounded-2xl p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            @if($context['mode'] === 'platform_overview')
                <div class="space-y-1">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                        Strict Multitenant GIS Isolation Active — Super Admin Platform Mode
                    </div>
                    <p class="text-xs text-amber-900/90 dark:text-amber-200/90">
                        Public Kenya basemap, 47 county boundaries, and trunk corridors are displayed. Private ISP infrastructure coordinates, fiber routes, and subscriber drops remain isolated until you initiate an explicitly authorized &amp; audited support session below.
                    </p>
                </div>
                <form action="{{ route('gis.support.grant') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                    @csrf
                    <select name="tenant_id" required class="px-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 text-slate-900 dark:text-white">
                        <option value="">Select ISP Tenant to Inspect...</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ strtoupper($t->plan_tier ?? 'starter') }})</option>
                        @endforeach
                    </select>
                    <input type="text" name="support_reason" required minlength="8" placeholder="Audit Ticket / Reason (e.g. NOC-402 Fiber Outage)"
                        class="px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 text-slate-900 dark:text-white min-w-[240px]">
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                        Authorize Audited Support View
                    </button>
                </form>
            @else
                <div class="space-y-1">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-rose-700 dark:text-rose-300">
                        Audited Tenant Support Session Active — {{ $activeTenant?->name }}
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300">
                        Reason logged in Audit Trail: <span class="font-mono font-bold">"{{ $context['support_reason'] }}"</span> • Granted at {{ $context['support_granted_at'] }}
                    </p>
                </div>
                <form action="{{ route('gis.support.revoke') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Close Support Session &amp; Return to Platform View
                    </button>
                </form>
            @endif
        </div>
    @endif

    <!-- KPI Summary Strip -->
    @if($context['mode'] === 'platform_overview')
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Kenya Counties</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">47 / 47</div>
                <div class="text-[11px] text-emerald-500 font-semibold">IEBC / KNBS WGS 84</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">ISP Tenants</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $platformSummary['total_tenants'] ?? 0 }}</div>
                <div class="text-[11px] text-blue-500 font-semibold">{{ $platformSummary['active_tenants'] ?? 0 }} Active</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Regional Sites</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $platformSummary['total_sites'] ?? 0 }}</div>
                <div class="text-[11px] text-cyan-500 font-semibold">Across Kenya</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Total GIS Assets</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $platformSummary['total_infrastructure_nodes'] ?? 0 }}</div>
                <div class="text-[11px] text-purple-500 font-semibold">Tenant-Isolated</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Counties with PoPs</div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $platformSummary['counties_covered'] ?? 0 }}</div>
                <div class="text-[11px] text-amber-500 font-semibold">National Footprint</div>
            </div>
        </div>
    @else
        @php
            $popCount = $infrastructure->filter(fn($i) => in_array($i->effective_type, ['pop']))->count();
            $routeCount = $infrastructure->filter(fn($i) => in_array($i->effective_type, ['fiber_route', 'wireless_link']))->count();
            $accessNodeCount = $infrastructure->filter(fn($i) => in_array($i->effective_type, ['fat_box', 'distribution_point', 'splice_closure', 'closure', 'manhole', 'cabinet', 'pole']))->count();
            $activeEquipCount = $infrastructure->filter(fn($i) => in_array($i->effective_type, ['olt', 'onu_ont', 'tower', 'router']))->count() + $mikrotikDevices->count() + $oltDevices->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">PoPs &amp; Sites</div>
                <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400 mt-0.5">{{ $popCount + $sites->count() }}</div>
                <div class="text-[11px] text-slate-500">{{ $popCount }} PoPs • {{ $sites->count() }} Sites</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Fiber &amp; PtP Links</div>
                <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $routeCount }}</div>
                <div class="text-[11px] text-slate-500">Backbone &amp; Backhaul</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">OSP Access Nodes</div>
                <div class="text-xl font-extrabold text-teal-600 dark:text-teal-400 mt-0.5">{{ $accessNodeCount }}</div>
                <div class="text-[11px] text-slate-500">FAT, Closures, Manholes</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">OLTs, Towers &amp; Routers</div>
                <div class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $activeEquipCount }}</div>
                <div class="text-[11px] text-slate-500">+ {{ $udieDevicesCount }} UDIE Devices</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">Customer GPS Drops</div>
                <div class="text-xl font-extrabold text-cyan-600 dark:text-cyan-400 mt-0.5">{{ $clients->count() }}</div>
                <div class="text-[11px] text-slate-500">Verified Subscriber Pins</div>
            </div>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3.5">
                <div class="text-[11px] font-bold uppercase text-slate-400">NOC &amp; Field Jobs</div>
                <div class="text-xl font-extrabold {{ $nocEvents->count() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }} mt-0.5">
                    {{ $nocEvents->count() }} / {{ $jobs->count() }}
                </div>
                <div class="text-[11px] text-slate-500">Alerts / Work Orders</div>
            </div>
        </div>
    @endif

    <!-- Search, Geocoding & Multi-Criteria Filter Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <!-- Unified Search Bar -->
            <div class="lg:col-span-5 relative">
                <label for="gisSearchInput" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    Search Kenya Counties, Towns, Estates, Roads, Coordinates or Assets
                </label>
                <div class="flex items-center gap-1.5">
                    <div class="relative flex-1">
                        <input type="text" id="gisSearchInput"
                            placeholder="e.g. Nairobi, Mombasa, Westlands, -1.2864, 36.8172, POP-NBO-01..."
                            autocomplete="off"
                            class="w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="button" id="btnRunGisSearch"
                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shrink-0">
                        Search
                    </button>
                </div>
                <!-- Search Results Dropdown -->
                <div id="gisSearchResultsDropdown" class="hidden absolute left-0 right-0 mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"></div>
            </div>

            <!-- County Filter (47 Counties) -->
            <div class="lg:col-span-2">
                <label for="filterCounty" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    Kenya County (47)
                </label>
                <select id="filterCounty" class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">All 47 Counties (Full Kenya)</option>
                    @foreach($countiesList as $c)
                        <option value="{{ $c['name'] }}" data-lat="{{ $c['lat'] }}" data-lng="{{ $c['lng'] }}" {{ request('county') === $c['name'] ? 'selected' : '' }}>
                            {{ $c['code'] }} - {{ $c['name'] }} ({{ $c['capital'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Site Filter -->
            <div class="lg:col-span-2">
                <label for="filterSite" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    Network Site / Hub
                </label>
                <select id="filterSite" class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">All Network Sites</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}" data-lat="{{ $s->latitude }}" data-lng="{{ $s->longitude }}">
                            {{ $s->name }} ({{ $s->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Asset Subtype Filter -->
            <div class="lg:col-span-2">
                <label for="filterAssetType" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    Device / Asset Type
                </label>
                <select id="filterAssetType" class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">All Asset &amp; Device Types</option>
                    @foreach($assetTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                    <option value="customer">Customer Installation Drop</option>
                </select>
            </div>

            <!-- Operational Status Filter -->
            <div class="lg:col-span-1">
                <label for="filterStatus" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    Status
                </label>
                <select id="filterStatus" class="w-full px-2.5 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="faulty">Faulty</option>
                    <option value="planned">Planned</option>
                </select>
            </div>
        </div>

        <!-- Zoom Level Presets, Interactive Tools & Coordinate Display Strip -->
        <div class="pt-2 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
            <!-- Zoom Level Buttons -->
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold uppercase text-slate-400 mr-1">Zoom Presets:</span>
                <button type="button" data-zoom-preset="country" class="gis-zoom-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-sm transition">
                    Kenya Full View (Z6)
                </button>
                <button type="button" data-zoom-preset="county" class="gis-zoom-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                    County Level (Z9)
                </button>
                <button type="button" data-zoom-preset="town" class="gis-zoom-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                    Town / Estate (Z13)
                </button>
                <button type="button" data-zoom-preset="site" class="gis-zoom-btn px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                    Network Site / Drop (Z17)
                </button>
            </div>

            <!-- Interactive GIS Tools -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" id="btnToolMeasure" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 transition">
                    Measure Distance / Optical Loss
                </button>
                @if($context['can_edit'])
                    <button type="button" id="btnToolPinAsset" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600/10 hover:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 transition">
                        Click Map to Pin Asset
                    </button>
                    <button type="button" id="btnToolDrawRoute" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 transition">
                        Draw Fiber / Wireless Route
                    </button>
                @endif
                <select id="basemapSelector" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                    <option value="street">Basemap: OpenStreetMap Kenya Street</option>
                    <option value="dark">Basemap: CARTO Dark NOC</option>
                    <option value="topo">Basemap: OpenTopoMap Terrain</option>
                    <option value="offline_vector">Basemap: Offline Kenya 47-County Vector</option>
                </select>
            </div>

            <!-- Live Coordinates & Copy Button -->
            <div class="flex items-center gap-2 text-xs font-mono bg-slate-100 dark:bg-slate-800/90 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-700">
                <span class="text-slate-400">Lat/Lng:</span>
                <span id="liveCursorCoords" class="font-bold text-slate-800 dark:text-emerald-400">0.023600, 37.906200</span>
                <span id="liveCursorZoom" class="text-[11px] text-slate-500">(Z6)</span>
                <button type="button" id="btnCopyCoords" title="Copy pinned/current coordinates"
                    class="px-2 py-0.5 rounded bg-blue-600/10 hover:bg-blue-600/20 text-blue-600 dark:text-blue-400 font-sans font-bold text-[11px] transition">
                    Copy
                </button>
            </div>
        </div>
    </div>

    <!-- Offline / Tile Service Unavailable Warning Banner (Hidden unless tile errors occur or offline mode selected) -->
    <div id="tileFallbackNotice" class="hidden bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between gap-3">
        <div>
            <strong>Offline / Resilient Vector Basemap Active:</strong> External raster map tiles are currently unreachable or disabled. Rendering authoritative built-in Kenya 47-County GeoJSON boundaries, national highway corridors, and all ISP network assets locally.
        </div>
        <button type="button" id="btnRetryTiles" class="px-3 py-1 rounded-lg bg-amber-600 text-white font-bold shrink-0">Retry Tile Server</button>
    </div>

    <!-- Active Tool Status Bar (Measure / Draw Route / Pin Mode) -->
    <div id="activeToolBanner" class="hidden bg-blue-600/10 border border-blue-500/30 rounded-xl p-3 text-xs text-blue-800 dark:text-blue-200 flex flex-wrap items-center justify-between gap-3">
        <div id="activeToolMessage" class="font-semibold">Tool active.</div>
        <div class="flex items-center gap-2">
            <button type="button" id="btnFinishRouteDraw" class="hidden px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
                Save Drawn Route as Asset
            </button>
            <button type="button" id="btnClearActiveTool" class="px-3 py-1 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold">
                Exit Tool
            </button>
        </div>
    </div>

    <!-- Main Map Workspace Grid: Layer Controls + Interactive Map + Asset Inspection Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        <!-- Left Column: Layer Toggles & Map Legend (3 cols on desktop) -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-4 max-h-[680px] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                    GIS Map Layers &amp; Legend
                </h2>
                <label class="inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-600 dark:text-blue-400 cursor-pointer">
                    <input type="checkbox" id="toggleClusterMode" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Cluster Markers</span>
                </label>
            </div>

            <!-- Public Kenya Basemap Overlays -->
            <div class="space-y-2">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    Public Kenya Administrative &amp; Roads
                </div>
                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="counties" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded border-2 border-emerald-500 bg-emerald-500/20 inline-block"></span>
                        <span>Kenya 47 Counties (GeoJSON)</span>
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">47</span>
                </label>
                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="corridors" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-4 h-0.5 bg-amber-500 inline-block"></span>
                        <span>National Trunk Highways (A104/A2)</span>
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">{{ count($nationalCorridors) }}</span>
                </label>
                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="towns" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block"></span>
                        <span>Major Cities, Towns &amp; Estates</span>
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">{{ count($majorTowns) }}</span>
                </label>
            </div>

            <!-- Private ISP Network Infrastructure Layers -->
            <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    Authorized ISP Network Layers
                </div>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="pop" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded-full bg-blue-600 ring-2 ring-blue-300 inline-block"></span>
                        <span>PoPs &amp; Regional Sites</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="fiber_route" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-4 h-1 bg-cyan-500 inline-block rounded"></span>
                        <span>Fiber Backbone &amp; Feeder Routes</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="splice_closure" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span>
                        <span>Splice Closures (Dome / Inline)</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="manhole" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded bg-amber-600 inline-block"></span>
                        <span>Utility Manholes &amp; Chambers</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="cabinet" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded bg-teal-500 inline-block"></span>
                        <span>Street Cabinets (FDT)</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="fat_box" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span>FAT Boxes &amp; Distribution Points</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="olt" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded bg-indigo-600 inline-block"></span>
                        <span>GPON / EPON OLTs</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="onu_ont" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded-full bg-sky-400 inline-block"></span>
                        <span>ONUs / ONTs</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="tower" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span>Wireless Masts, Towers &amp; Poles</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="wireless_link" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-4 h-0.5 border-b-2 border-dashed border-fuchsia-500 inline-block"></span>
                        <span>Wireless PtP / Backhaul Links</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="router" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-3 h-3 rounded bg-orange-500 inline-block"></span>
                        <span>MikroTik Core / Edge Routers</span>
                    </span>
                </label>

                <label class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" data-layer-toggle="customer" checked class="gis-layer-checkbox rounded text-blue-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 inline-block"></span>
                        <span>Customer Installations &amp; Jobs</span>
                    </span>
                </label>
            </div>

            <!-- Operational Status Indicator Legend -->
            <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px]">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                    Operational Status Indicators
                </div>
                <div class="grid grid-cols-2 gap-1.5 text-slate-600 dark:text-slate-300">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Active / Online</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Maintenance</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span>Faulty / LOS</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span>Planned</span>
                </div>
            </div>

            <!-- Selected Asset Detail Inspector Panel -->
            <div id="assetInspectorPanel" class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                    Selected Asset / Location Details
                </div>
                <div id="assetInspectorContent" class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-200/80 dark:border-slate-800">
                    Click any county, PoP, fiber route, splice closure, OLT, tower, router, or customer marker on the Kenya map to inspect coordinates, port capacity, and optical telemetry.
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Leaflet Map of Kenya (9 cols on desktop) -->
        <div class="lg:col-span-9 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden p-3 flex flex-col">
            <div id="gisMap" class="w-full h-[540px] sm:h-[640px] rounded-xl border border-slate-200 dark:border-slate-800 z-10 relative"></div>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400 px-1">
                <div>
                    <strong>Geospatial Attribution:</strong> Map tiles &copy; OpenStreetMap contributors &amp; CARTO. Kenya 47 County Boundaries derived from IEBC / KNBS / Humanitarian Data Exchange (`COD-AB-KEN`, `EPSG:4326`).
                </div>
                <div>
                    Coordinates verified inside Kenya extent (`-4.80°S..5.20°N`, `33.85°E..42.00°E`).
                </div>
            </div>
        </div>
    </div>

    <!-- Add / Edit GIS Asset Modal -->
    @if($context['can_edit'])
    <div id="gisAssetModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl space-y-4 my-8">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h3 id="gisAssetModalTitle" class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">
                        Add Kenya GIS Network Asset
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Coordinates are validated against Kenya's geographic boundaries (`EPSG:4326`) and saved to your ISP tenant database.
                    </p>
                </div>
                <button type="button" id="btnCloseAssetModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold px-2">✕</button>
            </div>

            <form id="gisAssetForm" method="POST" action="{{ route('gis.assets.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethodInput" value="POST">
                <input type="hidden" name="coordinates_json" id="inputCoordinatesJson" value="">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Asset Name *</label>
                        <input type="text" name="name" id="inputAssetName" required placeholder="e.g. Westlands Core PoP / Waiyaki Way Splice Closure"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Asset Code</label>
                        <input type="text" name="code" id="inputAssetCode" placeholder="e.g. POP-NBO-02"
                            class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Asset / Device Subtype *</label>
                        <select name="asset_subtype" id="inputAssetSubtype" required
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                            @foreach($assetTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kenya County *</label>
                        <select name="county" id="inputAssetCounty"
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="">Auto-detect from Coordinates</option>
                            @foreach($countiesList as $c)
                                <option value="{{ $c['name'] }}">{{ $c['code'] }} - {{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Operational Status *</label>
                        <select name="status" id="inputAssetStatus" required
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="active">Active / Online</option>
                            <option value="maintenance">Under Maintenance</option>
                            <option value="faulty">Faulty / Outage</option>
                            <option value="planned">Planned Deployment</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Latitude (Kenya: -4.80 to 5.20) *</label>
                        <input type="number" step="0.0000001" min="-4.80" max="5.20" name="latitude" id="inputAssetLat" required
                            placeholder="-1.286389"
                            class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Longitude (Kenya: 33.85 to 42.00) *</label>
                        <input type="number" step="0.0000001" min="33.85" max="42.00" name="longitude" id="inputAssetLng" required
                            placeholder="36.817223"
                            class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Coordinate Source</label>
                        <select name="coordinate_source" id="inputCoordinateSource"
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="manual_pin">Map Pin / Verified Entry</option>
                            <option value="gps_survey">Field GPS Survey</option>
                            <option value="imported_kml">Imported GIS / KML</option>
                            <option value="geocoded_approx">Geocoded Reference</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Town / Sub-County</label>
                        <input type="text" name="town" id="inputAssetTown" placeholder="e.g. Westlands / Nyali"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estate / Road</label>
                        <input type="text" name="estate_or_road" id="inputAssetRoad" placeholder="e.g. Waiyaki Way"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Total Ports / Cores</label>
                        <input type="number" name="total_ports" id="inputTotalPorts" value="24" min="0" max="4096"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Used Ports / Cores</label>
                        <input type="number" name="used_ports" id="inputUsedPorts" value="0" min="0" max="4096"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Engineering &amp; Splice Notes</label>
                    <textarea name="notes" id="inputAssetNotes" rows="2" placeholder="Optical budget, duct depth, pole tag, or splice tray assignments..."
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" id="btnCancelAssetModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-sm">
                        Save Verified Kenya GIS Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@php
    $serializedInfra = $infrastructure->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'type' => $item->type,
            'asset_subtype' => $item->effective_type,
            'county' => $item->county ?? 'Nairobi',
            'town' => $item->town,
            'estate_or_road' => $item->estate_or_road,
            'site_id' => $item->site_id,
            'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
            'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
            'coordinates_json' => $item->coordinates_json,
            'length_meters' => $item->length_meters !== null ? (float) $item->length_meters : null,
            'total_ports' => (int) ($item->total_ports ?? 0),
            'used_ports' => (int) ($item->used_ports ?? 0),
            'cable_loss_db_per_km' => (float) ($item->cable_Loss_db_per_km ?? 0.35),
            'status' => $item->status ?? 'active',
            'coordinate_source' => $item->coordinate_source ?? 'manual_pin',
            'notes' => $item->notes,
        ];
    })->values();

    $serializedClients = $clients->map(function ($cli) {
        return [
            'id' => $cli->id,
            'name' => $cli->name,
            'account_number' => $cli->account_number,
            'connection_type' => $cli->service_type ?? 'ftth',
            'status' => $cli->status,
            'county' => $cli->county ?? 'Nairobi',
            'town' => $cli->town,
            'address' => $cli->address,
            'site_id' => $cli->site_id,
            'latitude' => (float) $cli->latitude,
            'longitude' => (float) $cli->longitude,
            'onu_serial' => $cli->pppoe_username ?? $cli->mac_address,
            'signal_rx_dbm' => null,
        ];
    })->values();

    $serializedSites = $sites->map(function ($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'county' => $s->county ?? 'Nairobi',
            'location_name' => $s->location,
            'latitude' => $s->latitude !== null ? (float) $s->latitude : null,
            'longitude' => $s->longitude !== null ? (float) $s->longitude : null,
            'is_active' => true,
        ];
    })->values();

    $serializedJobs = $jobs->map(function ($j) {
        return [
            'id' => $j->id,
            'job_number' => $j->job_number,
            'status' => $j->status,
            'client_name' => $j->client?->name ?? 'Subscriber',
            'technician_name' => $j->technician?->user?->name ?? 'Unassigned',
            'latitude' => $j->latitude !== null ? (float) $j->latitude : null,
            'longitude' => $j->longitude !== null ? (float) $j->longitude : null,
        ];
    })->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.L) return;

    const gisConfig = @json($gisConfig);
    const countiesGeoJson = @json($countiesGeoJson);
    const nationalCorridors = @json($nationalCorridors);
    const majorTowns = @json($majorTowns);
    const infraAssets = @json($serializedInfra);
    const clientLocations = @json($serializedClients);
    const networkSites = @json($serializedSites);
    const activeJobs = @json($serializedJobs);
    const searchEndpoint = @json(route('gis.search'));
    const storeEndpoint = @json(route('gis.assets.store'));
    const updateEndpointTemplate = @json(route('gis.assets.update', ['asset' => '__ID__']));
    const deleteEndpointTemplate = @json(route('gis.assets.destroy', ['asset' => '__ID__']));
    const csrfToken = @json(csrf_token());

    // Initialize Leaflet Map starting with a full-country view of Kenya [0.0236, 37.9062], zoom 6
    const map = L.map('gisMap', {
        center: gisConfig.default_center,
        zoom: gisConfig.default_zoom,
        minZoom: gisConfig.min_zoom,
        maxZoom: gisConfig.max_zoom,
        zoomControl: true,
        preferCanvas: true
    });

    // Basemap Tile Layers (Configurable via environment variables)
    const tileLayers = {
        street: L.tileLayer(gisConfig.tiles.street, {
            attribution: gisConfig.tiles.attribution,
            maxZoom: 19
        }),
        dark: L.tileLayer(gisConfig.tiles.dark, {
            attribution: gisConfig.tiles.attribution,
            maxZoom: 19
        }),
        topo: L.tileLayer(gisConfig.tiles.topo, {
            attribution: gisConfig.tiles.attribution,
            maxZoom: 17
        })
    };

    let currentBasemapKey = 'street';
    let tileErrorCount = 0;
    const tileFallbackNotice = document.getElementById('tileFallbackNotice');

    function attachTileErrorHandlers(layer) {
        layer.on('tileerror', function () {
            tileErrorCount++;
            if (tileErrorCount >= 3 && tileFallbackNotice) {
                tileFallbackNotice.classList.remove('hidden');
                document.getElementById('gisMap').style.backgroundColor = '#0f172a';
            }
        });
    }

    Object.values(tileLayers).forEach(attachTileErrorHandlers);
    tileLayers.street.addTo(map);

    // Layer Groups
    const layerGroups = {
        counties: L.layerGroup().addTo(map),
        corridors: L.layerGroup().addTo(map),
        towns: L.layerGroup().addTo(map),
        pop: L.layerGroup().addTo(map),
        fiber_route: L.layerGroup().addTo(map),
        splice_closure: L.layerGroup().addTo(map),
        manhole: L.layerGroup().addTo(map),
        cabinet: L.layerGroup().addTo(map),
        fat_box: L.layerGroup().addTo(map),
        olt: L.layerGroup().addTo(map),
        onu_ont: L.layerGroup().addTo(map),
        tower: L.layerGroup().addTo(map),
        wireless_link: L.layerGroup().addTo(map),
        router: L.layerGroup().addTo(map),
        customer: L.layerGroup().addTo(map),
        interactiveTools: L.layerGroup().addTo(map)
    };

    // Styling Palettes per Asset Subtype
    const subtypeStyles = {
        pop: { color: '#2563eb', radius: 9, label: 'PoP / Core Aggregation Site' },
        fat_box: { color: '#10b981', radius: 7, label: 'FAT Distribution Box' },
        distribution_point: { color: '#059669', radius: 7, label: 'Fiber Distribution Point (FDP)' },
        splice_closure: { color: '#a855f7', radius: 7, label: 'Optical Splice Closure' },
        closure: { color: '#a855f7', radius: 7, label: 'Optical Splice Closure' },
        manhole: { color: '#d97706', radius: 7, label: 'Underground Utility Manhole' },
        cabinet: { color: '#14b8a6', radius: 8, label: 'Street Optical Cabinet (FDT)' },
        pole: { color: '#f43f5e', radius: 6, label: 'Utility Pole' },
        tower: { color: '#e11d48', radius: 9, label: 'Wireless Transmission Tower / Mast' },
        olt: { color: '#4f46e5', radius: 8, label: 'GPON / EPON OLT Chassis' },
        onu_ont: { color: '#38bdf8', radius: 6, label: 'Subscriber / Enterprise ONU/ONT' },
        router: { color: '#f97316', radius: 8, label: 'MikroTik Core / Edge Router' }
    };

    const statusColors = {
        active: '#10b981',
        maintenance: '#f59e0b',
        faulty: '#ef4444',
        planned: '#94a3b8'
    };

    // 1. Render Kenya National Outline & 47 Constitutional Counties GeoJSON
    function renderKenyaCountiesGeoJson(selectedCountyName = '') {
        layerGroups.counties.clearLayers();
        if (!countiesGeoJson || !countiesGeoJson.features) return;

        L.geoJSON(countiesGeoJson, {
            style: function (feature) {
                const isNational = feature.properties.feature_type === 'national_boundary';
                const isSelected = selectedCountyName && feature.properties.county_name &&
                    feature.properties.county_name.toLowerCase() === selectedCountyName.toLowerCase();

                if (isNational) {
                    return {
                        color: '#10b981',
                        weight: 2.5,
                        opacity: 0.85,
                        fillColor: '#064e3b',
                        fillOpacity: 0.03,
                        dashArray: '6, 4'
                    };
                }

                return {
                    color: isSelected ? '#2563eb' : '#334155',
                    weight: isSelected ? 2.5 : 1.2,
                    opacity: isSelected ? 0.95 : 0.55,
                    fillColor: isSelected ? '#3b82f6' : '#1e293b',
                    fillOpacity: isSelected ? 0.14 : 0.04
                };
            },
            onEachFeature: function (feature, layer) {
                if (feature.properties.feature_type === 'county_boundary') {
                    const p = feature.properties;
                    layer.bindTooltip(`County ${p.county_code}: ${p.county_name} (${p.capital})`, { sticky: true });
                    layer.on('click', function () {
                        inspectFeature({
                            title: `${p.county_name} County (Code ${p.county_code})`,
                            subtitle: `Region: ${p.region} • Headquarters: ${p.capital}`,
                            coords: [p.centroid_lat, p.centroid_lng],
                            detailsHtml: `
                                <div class="space-y-1">
                                    <div><strong>Constitutional Code:</strong> ${p.county_code} / 047</div>
                                    <div><strong>County Headquarters:</strong> ${p.capital}</div>
                                    <div><strong>Region:</strong> ${p.region}</div>
                                    <div><strong>CRS:</strong> ${p.crs}</div>
                                    <div class="text-[11px] text-slate-400 pt-1">Source: ${p.source}</div>
                                </div>
                            `
                        });
                    });
                }
            }
        }).addTo(layerGroups.counties);
    }

    // 2. Render National Highway Trunk Corridors
    function renderNationalCorridors() {
        layerGroups.corridors.clearLayers();
        nationalCorridors.forEach(function (corridor) {
            const line = L.polyline(corridor.coordinates, {
                color: '#f59e0b',
                weight: 2,
                opacity: 0.55,
                dashArray: '8, 6'
            }).addTo(layerGroups.corridors);

            line.bindTooltip(`Highway Corridor: ${corridor.name}`, { sticky: true });
        });
    }

    // 3. Render Major Kenyan Cities, Towns & Estates
    function renderMajorTowns(selectedCounty = '') {
        layerGroups.towns.clearLayers();
        majorTowns.forEach(function (town) {
            if (selectedCounty && town.county.toLowerCase() !== selectedCounty.toLowerCase()) return;
            const marker = L.circleMarker([town.lat, town.lng], {
                radius: town.type === 'capital_city' || town.type === 'major_city' ? 5 : 3.5,
                fillColor: town.type === 'capital_city' ? '#facc15' : '#94a3b8',
                color: '#0f172a',
                weight: 1,
                opacity: 0.8,
                fillOpacity: 0.75
            }).addTo(layerGroups.towns);

            marker.bindTooltip(`${town.name} (${town.county} County)`);
        });
    }

    // Haversine Geodesic Distance helper (meters)
    function haversineMeters(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function mapTargetLayerGroup(subtype) {
        if (subtype === 'pop') return layerGroups.pop;
        if (subtype === 'fiber_route') return layerGroups.fiber_route;
        if (subtype === 'splice_closure' || subtype === 'closure') return layerGroups.splice_closure;
        if (subtype === 'manhole') return layerGroups.manhole;
        if (subtype === 'cabinet') return layerGroups.cabinet;
        if (subtype === 'fat_box' || subtype === 'distribution_point') return layerGroups.fat_box;
        if (subtype === 'olt') return layerGroups.olt;
        if (subtype === 'onu_ont') return layerGroups.onu_ont;
        if (subtype === 'tower' || subtype === 'pole') return layerGroups.tower;
        if (subtype === 'wireless_link') return layerGroups.wireless_link;
        if (subtype === 'router') return layerGroups.router;
        return layerGroups.fat_box;
    }

    // 4. Render Authorized ISP Network Infrastructure, Sites, Routes & Customer Drops
    function renderNetworkAssets() {
        ['pop', 'fiber_route', 'splice_closure', 'manhole', 'cabinet', 'fat_box', 'olt', 'onu_ont', 'tower', 'wireless_link', 'router', 'customer'].forEach(k => {
            layerGroups[k].clearLayers();
        });

        const countyFilter = document.getElementById('filterCounty').value;
        const siteFilter = document.getElementById('filterSite').value;
        const typeFilter = document.getElementById('filterAssetType').value;
        const statusFilter = document.getElementById('filterStatus').value;
        const clusterEnabled = document.getElementById('toggleClusterMode').checked;
        const currentZoom = map.getZoom();

        // Proximity clustering at country/county zoom levels (Z <= 10) when enabled
        const shouldCluster = clusterEnabled && currentZoom <= 10;
        const countyBuckets = {};

        // Render Network Sites
        networkSites.forEach(function (site) {
            if (!site.latitude || !site.longitude) return;
            if (countyFilter && site.county && site.county.toLowerCase() !== countyFilter.toLowerCase()) return;
            if (siteFilter && String(site.id) !== String(siteFilter)) return;
            if (typeFilter && typeFilter !== 'pop') return;

            const marker = L.circleMarker([site.latitude, site.longitude], {
                radius: 10,
                fillColor: '#1d4ed8',
                color: '#ffffff',
                weight: 2.5,
                fillOpacity: 0.9
            }).addTo(layerGroups.pop);

            marker.bindTooltip(`ISP Site: ${site.name} (${site.code})`);
            marker.on('click', function () {
                inspectFeature({
                    title: `${site.name} (${site.code})`,
                    subtitle: `Regional Network Site • ${site.county} County`,
                    coords: [site.latitude, site.longitude],
                    detailsHtml: `
                        <div class="space-y-1">
                            <div><strong>Location:</strong> ${site.location_name || site.county}</div>
                            <div><strong>Coordinates:</strong> <span class="font-mono">${site.latitude.toFixed(6)}, ${site.longitude.toFixed(6)}</span></div>
                            <div><strong>Status:</strong> <span class="text-emerald-500 font-bold">${site.is_active ? 'ACTIVE' : 'INACTIVE'}</span></div>
                        </div>
                    `
                });
            });
        });

        // Render FiberInfrastructure Assets (Point & Line Assets)
        infraAssets.forEach(function (asset) {
            if (countyFilter && asset.county && asset.county.toLowerCase() !== countyFilter.toLowerCase()) return;
            if (siteFilter && String(asset.site_id) !== String(siteFilter)) return;
            if (typeFilter && asset.asset_subtype !== typeFilter && asset.type !== typeFilter) return;
            if (statusFilter && asset.status !== statusFilter) return;

            const targetGroup = mapTargetLayerGroup(asset.asset_subtype);
            const isLine = (asset.asset_subtype === 'fiber_route' || asset.asset_subtype === 'wireless_link') &&
                Array.isArray(asset.coordinates_json) && asset.coordinates_json.length >= 2;

            if (isLine) {
                const isWireless = asset.asset_subtype === 'wireless_link';
                const polyline = L.polyline(asset.coordinates_json, {
                    color: asset.status === 'faulty' ? '#ef4444' : (isWireless ? '#d946ef' : '#06b6d4'),
                    weight: isWireless ? 3 : 4,
                    opacity: 0.9,
                    dashArray: isWireless ? '6, 6' : null
                }).addTo(targetGroup);

                const km = ((asset.length_meters || 0) / 1000).toFixed(2);
                const estLossDb = (km * (asset.cable_loss_db_per_km || 0.35)).toFixed(2);
                polyline.bindTooltip(`${asset.name} (${km} km)`);
                polyline.on('click', function () {
                    inspectAssetRecord(asset, km, estLossDb);
                });
                return;
            }

            if (asset.latitude === null || asset.longitude === null) return;

            if (shouldCluster) {
                const key = `${asset.county || 'Kenya'}`;
                if (!countyBuckets[key]) {
                    countyBuckets[key] = { count: 0, latSum: 0, lngSum: 0, county: key, items: [] };
                }
                countyBuckets[key].count++;
                countyBuckets[key].latSum += asset.latitude;
                countyBuckets[key].lngSum += asset.longitude;
                countyBuckets[key].items.push(asset);
                return;
            }

            const style = subtypeStyles[asset.asset_subtype] || subtypeStyles.fat_box;
            const ringColor = statusColors[asset.status] || '#ffffff';

            const marker = L.circleMarker([asset.latitude, asset.longitude], {
                radius: style.radius,
                fillColor: style.color,
                color: ringColor,
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.92
            }).addTo(targetGroup);

            marker.bindTooltip(`${asset.name} [${asset.code}] (${asset.county})`);
            marker.on('click', function () {
                inspectAssetRecord(asset);
            });
        });

        // Render Cluster Badges when zoomed out
        if (shouldCluster) {
            Object.values(countyBuckets).forEach(function (bucket) {
                const cLat = bucket.latSum / bucket.count;
                const cLng = bucket.lngSum / bucket.count;
                const clusterIcon = L.divIcon({
                    className: 'gis-cluster-icon',
                    html: `<div style="background:#2563eb;color:#fff;border:2px solid #fff;border-radius:9999px;width:34px;height:34px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px;box-shadow:0 4px 12px rgba(0,0,0,0.35);">${bucket.count}</div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17]
                });
                const cm = L.marker([cLat, cLng], { icon: clusterIcon }).addTo(layerGroups.pop);
                cm.bindTooltip(`${bucket.county} County Cluster: ${bucket.count} Network Assets (Click to zoom in)`);
                cm.on('click', function () {
                    map.flyTo([cLat, cLng], 13, { duration: 0.8 });
                });
            });
        }

        // Render Customer Installation Locations
        if (!typeFilter || typeFilter === 'customer') {
            clientLocations.forEach(function (cli) {
                if (countyFilter && cli.county && cli.county.toLowerCase() !== countyFilter.toLowerCase()) return;
                if (siteFilter && String(cli.site_id) !== String(siteFilter)) return;
                if (statusFilter && cli.status !== statusFilter) return;

                const marker = L.circleMarker([cli.latitude, cli.longitude], {
                    radius: 5.5,
                    fillColor: cli.status === 'active' ? '#06b6d4' : '#ef4444',
                    color: '#ffffff',
                    weight: 1.5,
                    fillOpacity: 0.88
                }).addTo(layerGroups.customer);

                marker.bindTooltip(`Subscriber: ${cli.name} (${cli.account_number})`);
                marker.on('click', function () {
                    inspectFeature({
                        title: `${cli.name} (${cli.account_number})`,
                        subtitle: `Verified Subscriber Installation • ${cli.county} County`,
                        coords: [cli.latitude, cli.longitude],
                        detailsHtml: `
                            <div class="space-y-1">
                                <div><strong>Service Type:</strong> ${String(cli.connection_type || 'FTTH').toUpperCase()}</div>
                                <div><strong>Status:</strong> <span class="font-bold">${String(cli.status).toUpperCase()}</span></div>
                                <div><strong>ONU/ONT Serial:</strong> <span class="font-mono">${cli.onu_serial || 'N/A'}</span></div>
                                <div><strong>Optical Rx Signal:</strong> <span class="font-mono">${cli.signal_rx_dbm ? cli.signal_rx_dbm + ' dBm' : 'Verified'}</span></div>
                                <div><strong>Address:</strong> ${cli.address || cli.county}</div>
                                <div><strong>GPS Coordinates:</strong> <span class="font-mono">${cli.latitude.toFixed(6)}, ${cli.longitude.toFixed(6)}</span></div>
                            </div>
                        `
                    });
                });
            });
        }
    }

    // Asset Inspector Panel Renderer
    function inspectFeature(data) {
        const container = document.getElementById('assetInspectorContent');
        if (!container) return;

        const lat = Number(data.coords[0]).toFixed(6);
        const lng = Number(data.coords[1]).toFixed(6);
        document.getElementById('liveCursorCoords').textContent = `${lat}, ${lng}`;

        container.innerHTML = `
            <div class="space-y-2">
                <div>
                    <div class="font-extrabold text-slate-900 dark:text-white text-xs">${data.title}</div>
                    <div class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold">${data.subtitle}</div>
                </div>
                ${data.detailsHtml}
                ${data.actionsHtml || ''}
            </div>
        `;
    }

    function inspectAssetRecord(asset, routeKm = null, estLossDb = null) {
        const style = subtypeStyles[asset.asset_subtype] || { label: asset.asset_subtype.toUpperCase() };
        const utilPct = asset.total_ports > 0 ? Math.min(100, Math.round((asset.used_ports / asset.total_ports) * 100)) : 0;

        let actionsHtml = '';
        if (gisConfig.can_edit) {
            actionsHtml += `
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center gap-2">
                    <button type="button" onclick="window.__editGisAsset(${asset.id})"
                        class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-[11px] font-bold">
                        Edit / Move Asset
                    </button>
                    ${gisConfig.can_delete_export ? `
                    <button type="button" onclick="window.__deleteGisAsset(${asset.id})"
                        class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold">
                        Delete
                    </button>` : ''}
                </div>
            `;
        }

        inspectFeature({
            title: `${asset.name} (${asset.code})`,
            subtitle: `${style.label} • ${asset.county} County`,
            coords: [asset.latitude || 0.0236, asset.longitude || 37.9062],
            detailsHtml: `
                <div class="space-y-1 text-[11px]">
                    <div><strong>Operational Status:</strong> <span class="uppercase font-bold">${asset.status}</span></div>
                    <div><strong>Location:</strong> ${asset.town || ''} ${asset.estate_or_road ? '• ' + asset.estate_or_road : ''} (${asset.county})</div>
                    <div><strong>Coordinates:</strong> <span class="font-mono">${Number(asset.latitude).toFixed(6)}, ${Number(asset.longitude).toFixed(6)}</span></div>
                    <div><strong>Coordinate Source:</strong> <span class="font-mono">${asset.coordinate_source}</span></div>
                    ${routeKm ? `<div><strong>Route Geodesic Length:</strong> ${routeKm} km (Est. Attenuation: ${estLossDb} dB)</div>` : ''}
                    ${asset.total_ports > 0 ? `
                        <div>
                            <div class="flex justify-between"><span>Port / Core Utilization:</span><span class="font-mono">${asset.used_ports}/${asset.total_ports} (${utilPct}%)</span></div>
                            <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden mt-0.5">
                                <div class="h-full bg-blue-600" style="width:${utilPct}%"></div>
                            </div>
                        </div>
                    ` : ''}
                    ${asset.notes ? `<div class="text-slate-500 italic pt-1">"${asset.notes}"</div>` : ''}
                </div>
            `,
            actionsHtml: actionsHtml
        });
    }

    // Expose Edit / Delete handlers for inspector buttons
    window.__editGisAsset = function (assetId) {
        const asset = infraAssets.find(a => a.id === assetId);
        if (!asset) return;
        const modal = document.getElementById('gisAssetModal');
        const form = document.getElementById('gisAssetForm');
        document.getElementById('gisAssetModalTitle').textContent = `Edit / Move GIS Asset: ${asset.name} (${asset.code})`;
        form.action = updateEndpointTemplate.replace('__ID__', asset.id);
        document.getElementById('formMethodInput').value = 'PUT';
        document.getElementById('inputAssetName').value = asset.name;
        document.getElementById('inputAssetCode').value = asset.code;
        document.getElementById('inputAssetSubtype').value = asset.asset_subtype;
        document.getElementById('inputAssetCounty').value = asset.county || '';
        document.getElementById('inputAssetStatus').value = asset.status || 'active';
        document.getElementById('inputAssetLat').value = asset.latitude;
        document.getElementById('inputAssetLng').value = asset.longitude;
        document.getElementById('inputCoordinateSource').value = asset.coordinate_source || 'manual_pin';
        document.getElementById('inputAssetTown').value = asset.town || '';
        document.getElementById('inputAssetRoad').value = asset.estate_or_road || '';
        document.getElementById('inputTotalPorts').value = asset.total_ports || 0;
        document.getElementById('inputUsedPorts').value = asset.used_ports || 0;
        document.getElementById('inputAssetNotes').value = asset.notes || '';
        document.getElementById('inputCoordinatesJson').value = asset.coordinates_json ? JSON.stringify(asset.coordinates_json) : '';
        modal.classList.remove('hidden');
    };

    window.__deleteGisAsset = function (assetId) {
        const asset = infraAssets.find(a => a.id === assetId);
        if (!asset || !confirm(`Delete GIS asset "${asset.name} (${asset.code})"? This action is recorded in the audit log.`)) return;

        fetch(deleteEndpointTemplate.replace('__ID__', asset.id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(() => window.location.reload());
    };

    // Initial Render
    renderKenyaCountiesGeoJson();
    renderNationalCorridors();
    renderMajorTowns();
    renderNetworkAssets();

    map.on('zoomend', function () {
        document.getElementById('liveCursorZoom').textContent = `(Z${map.getZoom()})`;
        renderNetworkAssets();
    });

    // Live Latitude/Longitude Cursor Tracker
    map.on('mousemove', function (e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        document.getElementById('liveCursorCoords').textContent = `${lat}, ${lng}`;
    });

    // Copy Coordinates Button
    document.getElementById('btnCopyCoords')?.addEventListener('click', function () {
        const text = document.getElementById('liveCursorCoords').textContent;
        navigator.clipboard?.writeText(text);
        this.textContent = 'Copied!';
        setTimeout(() => { this.textContent = 'Copy'; }, 1500);
    });

    // Layer Toggle Checkboxes
    document.querySelectorAll('.gis-layer-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const key = this.getAttribute('data-layer-toggle');
            const group = layerGroups[key];
            if (!group) return;
            if (this.checked) {
                map.addLayer(group);
            } else {
                map.removeLayer(group);
            }
        });
    });

    document.getElementById('toggleClusterMode')?.addEventListener('change', renderNetworkAssets);

    // Zoom Level Preset Buttons
    document.querySelectorAll('.gis-zoom-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const preset = this.getAttribute('data-zoom-preset');
            if (preset === 'country') {
                map.flyTo([0.0236, 37.9062], 6, { duration: 0.9 });
            } else if (preset === 'county') {
                map.flyTo([-1.286389, 36.817223], 9, { duration: 0.9 });
            } else if (preset === 'town') {
                map.flyTo([-1.2683, 36.8111], 13, { duration: 0.9 });
            } else if (preset === 'site') {
                map.flyTo([-1.2985, 36.8142], 17, { duration: 0.9 });
            }
        });
    });

    // Basemap Switcher & Offline Vector Fallback
    document.getElementById('basemapSelector')?.addEventListener('change', function () {
        const mode = this.value;
        Object.values(tileLayers).forEach(tl => map.removeLayer(tl));
        if (mode === 'offline_vector') {
            tileFallbackNotice.classList.remove('hidden');
            document.getElementById('gisMap').style.backgroundColor = '#0f172a';
        } else {
            tileFallbackNotice.classList.add('hidden');
            tileLayers[mode]?.addTo(map);
            currentBasemapKey = mode;
        }
    });

    document.getElementById('btnRetryTiles')?.addEventListener('click', function () {
        tileErrorCount = 0;
        tileFallbackNotice.classList.add('hidden');
        Object.values(tileLayers).forEach(tl => map.removeLayer(tl));
        tileLayers[currentBasemapKey]?.addTo(map);
    });

    // Filter Controls (County, Site, Type, Status)
    document.getElementById('filterCounty')?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const countyName = this.value;
        renderKenyaCountiesGeoJson(countyName);
        renderMajorTowns(countyName);
        renderNetworkAssets();

        if (countyName && selectedOption.dataset.lat && selectedOption.dataset.lng) {
            map.flyTo([ parseFloat(selectedOption.dataset.lat), parseFloat(selectedOption.dataset.lng) ], 10, { duration: 0.9 });
        } else {
            map.flyTo(gisConfig.default_center, gisConfig.default_zoom, { duration: 0.9 });
        }
    });

    document.getElementById('filterSite')?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        renderNetworkAssets();
        if (this.value && selectedOption.dataset.lat && selectedOption.dataset.lng) {
            map.flyTo([ parseFloat(selectedOption.dataset.lat), parseFloat(selectedOption.dataset.lng) ], 15, { duration: 0.9 });
        }
    });

    document.getElementById('filterAssetType')?.addEventListener('change', renderNetworkAssets);
    document.getElementById('filterStatus')?.addEventListener('change', renderNetworkAssets);

    // Interactive Tools: Distance Measurement, Pin Asset, Draw Fiber Route
    let activeTool = null;
    let toolPoints = [];
    const activeToolBanner = document.getElementById('activeToolBanner');
    const activeToolMessage = document.getElementById('activeToolMessage');
    const btnFinishRouteDraw = document.getElementById('btnFinishRouteDraw');

    function resetInteractiveTool() {
        activeTool = null;
        toolPoints = [];
        layerGroups.interactiveTools.clearLayers();
        activeToolBanner?.classList.add('hidden');
        btnFinishRouteDraw?.classList.add('hidden');
    }

    document.getElementById('btnClearActiveTool')?.addEventListener('click', resetInteractiveTool);

    document.getElementById('btnToolMeasure')?.addEventListener('click', function () {
        resetInteractiveTool();
        activeTool = 'measure';
        activeToolBanner.classList.remove('hidden');
        activeToolMessage.textContent = 'Distance & Optical Attenuation Tool: Click two or more points on the Kenya map to measure geodesic distance and 1310nm fiber loss.';
    });

    document.getElementById('btnToolPinAsset')?.addEventListener('click', function () {
        resetInteractiveTool();
        activeTool = 'pin_asset';
        activeToolBanner.classList.remove('hidden');
        activeToolMessage.textContent = 'Pin Asset Location Mode: Click any location inside Kenya on the map to open the Add Asset modal with verified coordinates.';
    });

    document.getElementById('btnToolDrawRoute')?.addEventListener('click', function () {
        resetInteractiveTool();
        activeTool = 'draw_route';
        activeToolBanner.classList.remove('hidden');
        btnFinishRouteDraw.classList.remove('hidden');
        activeToolMessage.textContent = 'Draw Fiber / Wireless Route Mode: Click along the route path on the map to add vertices, then click "Save Drawn Route as Asset".';
    });

    map.on('click', function (e) {
        const lat = parseFloat(e.latlng.lat.toFixed(7));
        const lng = parseFloat(e.latlng.lng.toFixed(7));
        document.getElementById('liveCursorCoords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

        if (!activeTool) return;

        if (activeTool === 'pin_asset') {
            resetInteractiveTool();
            openCreateAssetModal(lat, lng, null);
            return;
        }

        if (activeTool === 'measure' || activeTool === 'draw_route') {
            toolPoints.push([lat, lng]);
            layerGroups.interactiveTools.clearLayers();

            toolPoints.forEach(function (pt, idx) {
                L.circleMarker(pt, {
                    radius: 5,
                    fillColor: '#facc15',
                    color: '#0f172a',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(layerGroups.interactiveTools).bindTooltip(`Vertex #${idx + 1}`);
            });

            if (toolPoints.length >= 2) {
                L.polyline(toolPoints, {
                    color: activeTool === 'draw_route' ? '#06b6d4' : '#facc15',
                    weight: 3.5,
                    dashArray: '5, 5'
                }).addTo(layerGroups.interactiveTools);

                let totalMeters = 0;
                for (let i = 1; i < toolPoints.length; i++) {
                    totalMeters += haversineMeters(toolPoints[i - 1][0], toolPoints[i - 1][1], toolPoints[i][0], toolPoints[i][1]);
                }
                const km = (totalMeters / 1000).toFixed(3);
                const loss1310 = (km * 0.35).toFixed(2);
                const loss1550 = (km * 0.22).toFixed(2);
                activeToolMessage.textContent = `${toolPoints.length} vertices plotted • Total Geodesic Distance: ${totalMeters.toFixed(1)} m (${km} km) • Est. Fiber Loss: ${loss1310} dB @1310nm / ${loss1550} dB @1550nm`;
            }
        }
    });

    btnFinishRouteDraw?.addEventListener('click', function () {
        if (toolPoints.length < 2) {
            alert('Please click at least 2 points on the map to form a fiber route or wireless link.');
            return;
        }
        const pointsCopy = [...toolPoints];
        resetInteractiveTool();
        openCreateAssetModal(pointsCopy[0][0], pointsCopy[0][1], pointsCopy);
    });

    // Modal Open / Close Logic
    function openCreateAssetModal(lat = -1.286389, lng = 36.817223, routeCoords = null) {
        const modal = document.getElementById('gisAssetModal');
        const form = document.getElementById('gisAssetForm');
        if (!modal || !form) return;

        form.reset();
        form.action = storeEndpoint;
        document.getElementById('formMethodInput').value = 'POST';
        document.getElementById('inputAssetLat').value = lat;
        document.getElementById('inputAssetLng').value = lng;

        if (routeCoords && routeCoords.length >= 2) {
            document.getElementById('gisAssetModalTitle').textContent = `Save Drawn Fiber Route / Wireless Link (${routeCoords.length} vertices)`;
            document.getElementById('inputAssetSubtype').value = 'fiber_route';
            document.getElementById('inputCoordinatesJson').value = JSON.stringify(routeCoords);
        } else {
            document.getElementById('gisAssetModalTitle').textContent = 'Add Kenya GIS Network Asset';
            document.getElementById('inputCoordinatesJson').value = '';
        }

        modal.classList.remove('hidden');
    }

    document.getElementById('btnOpenAddAssetModal')?.addEventListener('click', function () {
        const center = map.getCenter();
        openCreateAssetModal(parseFloat(center.lat.toFixed(6)), parseFloat(center.lng.toFixed(6)), null);
    });

    document.getElementById('btnCloseAssetModal')?.addEventListener('click', () => {
        document.getElementById('gisAssetModal')?.classList.add('hidden');
    });
    document.getElementById('btnCancelAssetModal')?.addEventListener('click', () => {
        document.getElementById('gisAssetModal')?.classList.add('hidden');
    });

    // Unified Search Bar & Geocoding Handler
    const searchInput = document.getElementById('gisSearchInput');
    const searchDropdown = document.getElementById('gisSearchResultsDropdown');
    const btnRunSearch = document.getElementById('btnRunGisSearch');

    function executeGisSearch() {
        const q = (searchInput.value || '').trim();
        if (!q) {
            searchDropdown.classList.add('hidden');
            return;
        }

        const county = document.getElementById('filterCounty').value;
        searchDropdown.innerHTML = `<div class="p-3 text-xs text-slate-500">Searching Kenya Gazetteer, tenant GIS assets &amp; geocoder for "${q}"...</div>`;
        searchDropdown.classList.remove('hidden');

        fetch(`${searchEndpoint}?q=${encodeURIComponent(q)}&county=${encodeURIComponent(county)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.results || data.results.length === 0) {
                searchDropdown.innerHTML = `
                    <div class="p-3 text-xs text-amber-600 dark:text-amber-400">
                        No verified Kenyan county, town, road, coordinate, or network asset matched "${q}". Coordinates are never invented when unverified.
                    </div>
                `;
                return;
            }

            searchDropdown.innerHTML = data.results.map((item, idx) => `
                <button type="button" data-result-idx="${idx}" class="w-full text-left p-2.5 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-start justify-between gap-2">
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">${item.name}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">${item.subtitle || item.category}</div>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">[${Number(item.latitude).toFixed(5)}, ${Number(item.longitude).toFixed(5)}] • ${item.source}</div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 ${item.verified_exact ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'}">
                        ${item.verified_exact ? 'Verified Exact' : 'Centroid / Approx'}
                    </span>
                </button>
            `).join('');

            searchDropdown.querySelectorAll('button[data-result-idx]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const item = data.results[parseInt(this.dataset.resultIdx, 10)];
                    searchDropdown.classList.add('hidden');
                    map.flyTo([item.latitude, item.longitude], item.zoom || 14, { duration: 0.9 });

                    const highlightMarker = L.circleMarker([item.latitude, item.longitude], {
                        radius: 12,
                        fillColor: '#facc15',
                        color: '#0f172a',
                        weight: 2,
                        fillOpacity: 0.5
                    }).addTo(layerGroups.interactiveTools);

                    setTimeout(() => layerGroups.interactiveTools.removeLayer(highlightMarker), 6000);
                });
            });
        })
        .catch(() => {
            searchDropdown.innerHTML = `<div class="p-3 text-xs text-rose-500">Geocoding service temporarily unavailable. Local county &amp; asset filters remain active.</div>`;
        });
    }

    btnRunSearch?.addEventListener('click', executeGisSearch);
    searchInput?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeGisSearch();
        }
    });

    document.addEventListener('click', function (e) {
        if (!searchDropdown.contains(e.target) && e.target !== searchInput && e.target !== btnRunSearch) {
            searchDropdown.classList.add('hidden');
        }
    });
});
</script>
@endsection
