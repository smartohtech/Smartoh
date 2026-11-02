<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\FiberInfrastructure;
use App\Models\InstallationJob;
use App\Models\MikroTikDevice;
use App\Models\NOCIncident;
use App\Models\OLTDevice;
use App\Models\Site;
use App\Models\Technician;
use App\Models\Tenant;
use App\Models\Device;
use App\Services\Gis\KenyaGeospatialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GisController extends Controller
{
    public function __construct(protected KenyaGeospatialService $geoService)
    {
    }

    /**
     * Resolve tenant access context and RBAC permissions for the current user.
     */
    protected function resolveAccessContext(Request $request): array
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->role === 'client') {
            abort(403, 'Subscribers are not permitted to access internal ISP GIS infrastructure maps.');
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $supportTenantId = session('gis_support_tenant_id') ?: session('impersonated_tenant_id');
        $supportReason = session('gis_support_reason');
        $supportGrantedAt = session('gis_support_granted_at');

        if ($isSuperAdmin) {
            // Super Admin only accesses private tenant network details when an explicit support workflow is active
            $activeTenantId = $supportTenantId ? (int) $supportTenantId : null;
            $mode = $activeTenantId ? 'tenant_support' : 'platform_overview';
            $canEdit = $activeTenantId !== null;
            $canDeleteOrExport = $activeTenantId !== null;
        } else {
            if (!$user->tenant_id) {
                abort(403, 'Your account is not associated with an active ISP tenant.');
            }
            $activeTenantId = (int) $user->tenant_id;
            $mode = 'tenant_workspace';
            $canEdit = in_array($user->role, ['isp_admin', 'technician'], true);
            $canDeleteOrExport = $user->role === 'isp_admin';
        }

        return [
            'user' => $user,
            'is_super_admin' => $isSuperAdmin,
            'mode' => $mode,
            'active_tenant_id' => $activeTenantId,
            'support_reason' => $supportReason,
            'support_granted_at' => $supportGrantedAt,
            'can_edit' => $canEdit,
            'can_delete_export' => $canDeleteOrExport,
        ];
    }

    /**
     * Display the interactive Kenya Network Map & GIS Fiber Map.
     */
    public function map(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        $activeTenantId = $context['active_tenant_id'];

        $countiesGeoJson = $this->geoService->getCountiesGeoJson();
        $countiesList = $this->geoService->getCounties();
        $nationalCorridors = $this->geoService->getNationalHighwayCorridors();
        $majorTowns = $this->geoService->getMajorTownsGazetteer();

        $tenants = $context['is_super_admin']
            ? Tenant::orderBy('name')->get(['id', 'name', 'subdomain', 'subscription_tier', 'status'])
            : Tenant::where('id', $activeTenantId)->get(['id', 'name', 'subdomain', 'subscription_tier', 'status']);

        $activeTenant = $activeTenantId ? Tenant::find($activeTenantId) : null;

        // Platform-level summary for Super Admin when no support tenant session is active
        $platformSummary = [];
        if ($context['mode'] === 'platform_overview') {
            $platformSummary = [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('status', 'active')->count(),
                'total_sites' => Site::withoutGlobalScopes()->count(),
                'total_infrastructure_nodes' => FiberInfrastructure::withoutGlobalScopes()->count(),
                'counties_covered' => FiberInfrastructure::withoutGlobalScopes()
                    ->whereNotNull('county')
                    ->distinct('county')
                    ->count('county'),
                'tenant_summaries' => Tenant::withCount([
                    'sites',
                    'clients',
                ])->orderBy('name')->get()->map(function (Tenant $t) {
                    $infraCount = FiberInfrastructure::withoutGlobalScopes()->where('tenant_id', $t->id)->count();
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'plan_tier' => $t->plan_tier,
                        'status' => $t->status,
                        'sites_count' => $t->sites_count,
                        'clients_count' => $t->clients_count,
                        'infrastructure_count' => $infraCount,
                    ];
                }),
            ];

            $infrastructure = collect();
            $clients = collect();
            $jobs = collect();
            $sites = collect();
            $mikrotikDevices = collect();
            $oltDevices = collect();
            $nocEvents = collect();
            $udieDevicesCount = 0;
        } else {
            // Ensure demo tenant has complete representative Kenyan asset types without overwriting existing data
            $this->ensureRepresentativeKenyaAssetsForDemoTenant($activeTenantId);
            $this->backfillMissingCountyTagsForTenant($activeTenantId);

            $infraQuery = FiberInfrastructure::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId);

            if ($request->filled('county')) {
                $infraQuery->where('county', $request->query('county'));
            }
            if ($request->filled('site_id')) {
                $infraQuery->where('site_id', (int) $request->query('site_id'));
            }
            if ($request->filled('status')) {
                $infraQuery->where('status', FiberInfrastructure::mapToBaseEnumStatus($request->query('status')));
            }
            if ($request->filled('asset_type')) {
                $requestedType = $request->query('asset_type');
                $infraQuery->where(function ($q) use ($requestedType) {
                    $q->where('asset_subtype', $requestedType)
                      ->orWhere('type', $requestedType);
                });
            }

            $infrastructure = $infraQuery->orderBy('name')->get();

            $clientsQuery = Client::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($request->filled('county')) {
                $clientsQuery->where('county', $request->query('county'));
            }
            if ($request->filled('site_id')) {
                $clientsQuery->where('site_id', (int) $request->query('site_id'));
            }

            $clients = $clientsQuery->get([
                'id', 'tenant_id', 'site_id', 'account_number', 'name',
                'service_type', 'status', 'county', 'town', 'address',
                'latitude', 'longitude', 'pppoe_username', 'assigned_ip', 'mac_address',
            ]);

            $jobs = InstallationJob::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->with(['client', 'technician.user'])
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->get();

            $sites = Site::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->orderBy('name')
                ->get();

            $mikrotikDevices = MikroTikDevice::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->with('site')
                ->get();

            $oltDevices = OLTDevice::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->with('site')
                ->get();

            $nocEvents = NOCIncident::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->whereIn('status', ['open', 'investigating', 'identified', 'monitoring'])
                ->latest('started_at')
                ->take(25)
                ->get();

            $udieDevicesCount = Device::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->count();
        }

        $gisConfig = [
            'default_center' => [
                (float) config('services.gis_map.default_lat', 0.0236),
                (float) config('services.gis_map.default_lng', 37.9062),
            ],
            'default_zoom' => (int) config('services.gis_map.default_zoom', 6),
            'min_zoom' => (int) config('services.gis_map.min_zoom', 5),
            'max_zoom' => (int) config('services.gis_map.max_zoom', 19),
            'kenya_bounds' => [
                [-4.80, 33.85],
                [5.20, 42.00],
            ],
            'tiles' => [
                'dark' => config('services.gis_map.tile_dark_url', 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'),
                'street' => config('services.gis_map.tile_street_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
                'topo' => config('services.gis_map.tile_topo_url', 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png'),
                'attribution' => config('services.gis_map.tile_attribution', '&copy; OpenStreetMap contributors &copy; CARTO'),
            ],
            'crs' => 'EPSG:4326',
            'mode' => $context['mode'],
            'can_edit' => $context['can_edit'],
            'can_delete_export' => $context['can_delete_export'],
            'active_tenant_id' => $activeTenantId,
            'active_tenant_name' => $activeTenant?->name,
        ];

        $assetTypes = FiberInfrastructure::SUPPORTED_SUBTYPES;

        return view('gis.map', compact(
            'infrastructure',
            'clients',
            'jobs',
            'sites',
            'mikrotikDevices',
            'oltDevices',
            'nocEvents',
            'udieDevicesCount',
            'countiesGeoJson',
            'countiesList',
            'nationalCorridors',
            'majorTowns',
            'gisConfig',
            'assetTypes',
            'context',
            'tenants',
            'activeTenant',
            'platformSummary'
        ));
    }

    /**
     * Return authoritative Kenya 47 Counties GeoJSON & National Boundary (EPSG:4326).
     */
    public function kenyaGeoJson(Request $request)
    {
        $this->resolveAccessContext($request);

        return response()->json([
            'crs' => 'EPSG:4326',
            'counties_geojson' => $this->geoService->getCountiesGeoJson(),
            'national_corridors' => $this->geoService->getNationalHighwayCorridors(),
            'major_towns' => $this->geoService->getMajorTownsGazetteer(),
        ]);
    }

    /**
     * Search across Kenya counties, towns, estates, street names, coordinates, and authorized tenant network assets.
     */
    public function search(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        $query = trim((string) $request->query('q', ''));
        $countyFilter = $request->query('county');

        if ($query === '') {
            return response()->json([
                'query' => '',
                'results' => [],
                'meta' => [
                    'crs' => 'EPSG:4326',
                    'notice' => 'Enter a Kenya county, town, estate, road, coordinate pair, or network asset name.',
                ],
            ]);
        }

        $assetResults = [];
        $activeTenantId = $context['active_tenant_id'];

        // Search authorized tenant network assets first if in tenant workspace/support mode
        if ($activeTenantId !== null) {
            $matchingInfra = FiberInfrastructure::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%")
                      ->orWhere('county', 'like', "%{$query}%")
                      ->orWhere('town', 'like', "%{$query}%")
                      ->orWhere('estate_or_road', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();

            foreach ($matchingInfra as $asset) {
                if ($countyFilter && $asset->county && strcasecmp($asset->county, $countyFilter) !== 0) {
                    continue;
                }

                $lat = $asset->latitude !== null ? (float) $asset->latitude : null;
                $lng = $asset->longitude !== null ? (float) $asset->longitude : null;

                if (($lat === null || $lng === null) && is_array($asset->coordinates_json) && !empty($asset->coordinates_json[0])) {
                    $lat = (float) $asset->coordinates_json[0][0];
                    $lng = (float) $asset->coordinates_json[0][1];
                }

                if ($lat !== null && $lng !== null) {
                    $assetResults[] = [
                        'category' => 'network_asset',
                        'asset_id' => $asset->id,
                        'asset_subtype' => $asset->effective_type,
                        'name' => "{$asset->name} ({$asset->code})",
                        'subtitle' => strtoupper($asset->effective_type) . ' • ' . ($asset->county ?? 'Kenya') . ' • Status: ' . strtoupper($asset->status),
                        'county' => $asset->county,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'zoom' => 16,
                        'verified_exact' => true,
                        'precision' => 'verified_asset_coordinates',
                        'source' => 'Smartoh Tenant GIS Inventory (' . ($asset->coordinate_source ?? 'manual_pin') . ')',
                    ];
                }
            }

            // Search Sites
            $matchingSites = Site::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%")
                      ->orWhere('location', 'like', "%{$query}%");
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->limit(5)
                ->get();

            foreach ($matchingSites as $site) {
                $assetResults[] = [
                    'category' => 'network_site',
                    'site_id' => $site->id,
                    'name' => "{$site->name} ({$site->code})",
                    'subtitle' => 'ISP Network Site • ' . ($site->location ?? $site->county ?? 'Kenya'),
                    'county' => $site->county,
                    'latitude' => (float) $site->latitude,
                    'longitude' => (float) $site->longitude,
                    'zoom' => 14,
                    'verified_exact' => true,
                    'precision' => 'verified_site_coordinates',
                    'source' => 'Smartoh Tenant Site Registry',
                ];
            }

            // Search Client Installations with verified coordinates
            $matchingClients = Client::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('account_number', 'like', "%{$query}%")
                      ->orWhere('pppoe_username', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($matchingClients as $client) {
                $assetResults[] = [
                    'category' => 'customer_location',
                    'client_id' => $client->id,
                    'name' => "{$client->name} ({$client->account_number})",
                    'subtitle' => 'Subscriber Installation • ' . strtoupper($client->service_type ?? 'FTTH') . ' • ' . ($client->address ?? 'Kenya'),
                    'county' => $client->county,
                    'latitude' => (float) $client->latitude,
                    'longitude' => (float) $client->longitude,
                    'zoom' => 17,
                    'verified_exact' => true,
                    'precision' => 'verified_subscriber_gps',
                    'source' => 'Smartoh Subscriber Installation Record',
                ];
            }
        }

        // Search Kenya Gazetteer & External Geocoding Proxy
        $geoSearch = $this->geoService->searchLocations($query, true);
        $locationResults = $geoSearch['results'] ?? [];

        if ($countyFilter) {
            $locationResults = array_values(array_filter($locationResults, function ($item) use ($countyFilter) {
                return empty($item['county']) || strcasecmp($item['county'], $countyFilter) === 0;
            }));
        }

        return response()->json([
            'query' => $query,
            'results' => array_merge($assetResults, $locationResults),
            'asset_matches_count' => count($assetResults),
            'location_matches_count' => count($locationResults),
            'external_geocoder_status' => $geoSearch['external_status'] ?? 'not_needed',
            'attribution' => $geoSearch['attribution'] ?? config('services.gis_map.tile_attribution'),
            'crs' => 'EPSG:4326',
        ]);
    }

    /**
     * Store a newly created ISP GIS network asset or fiber route/wireless link.
     */
    public function storeAsset(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_edit'] || !$context['active_tenant_id']) {
            abort(403, 'You do not have permission to create GIS network assets for this tenant.');
        }

        $activeTenantId = $context['active_tenant_id'];
        $allowedSubtypes = implode(',', array_keys(FiberInfrastructure::SUPPORTED_SUBTYPES));

        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'code' => 'nullable|string|max:64',
            'asset_subtype' => "required|string|in:{$allowedSubtypes}",
            'site_id' => 'nullable|integer|exists:sites,id',
            'county' => 'nullable|string|max:80',
            'sub_county' => 'nullable|string|max:80',
            'town' => 'nullable|string|max:80',
            'estate_or_road' => 'nullable|string|max:160',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'coordinates_json' => 'nullable',
            'total_ports' => 'nullable|integer|min:0|max:4096',
            'used_ports' => 'nullable|integer|min:0|max:4096',
            'elevation_m' => 'nullable|numeric|min:-50|max:6000',
            'azimuth_deg' => 'nullable|numeric|min:0|max:360',
            'frequency_ghz' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string|in:active,operational,maintenance,faulty,damaged,planned',
            'coordinate_source' => 'nullable|string|in:gps_survey,gps_surveyed,manual_pin,geocoded_approx,imported_kml',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Verify site belongs to this tenant if provided
        if (!empty($validated['site_id'])) {
            $siteExists = Site::withoutGlobalScopes()
                ->where('tenant_id', $activeTenantId)
                ->where('id', $validated['site_id'])
                ->exists();
            if (!$siteExists) {
                return $this->errorResponse($request, 'Selected site does not belong to your ISP tenant.', 422);
            }
        }

        $subtype = $validated['asset_subtype'];
        $isLineAsset = in_array($subtype, ['fiber_route', 'wireless_link'], true);

        $coordinatesArray = null;
        $lat = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $lng = isset($validated['longitude']) ? (float) $validated['longitude'] : null;
        $lengthMeters = null;

        if ($isLineAsset) {
            $rawCoords = $validated['coordinates_json'] ?? null;
            if (is_string($rawCoords)) {
                $rawCoords = json_decode($rawCoords, true);
            }
            if (!is_array($rawCoords) || count($rawCoords) < 2) {
                return $this->errorResponse($request, 'A fiber route or wireless link requires at least 2 coordinate vertices [[lat, lng], ...].', 422);
            }

            $normalizedVertices = [];
            foreach ($rawCoords as $idx => $vertex) {
                if (!is_array($vertex) || count($vertex) < 2) {
                    return $this->errorResponse($request, "Invalid vertex format at position {$idx}.", 422);
                }
                $vLat = (float) $vertex[0];
                $vLng = (float) $vertex[1];
                $validation = $this->geoService->validateKenyaCoordinates($vLat, $vLng);
                if (!$validation['valid']) {
                    return $this->errorResponse($request, "Route vertex #{$idx} failed validation: {$validation['message']}", 422);
                }
                $normalizedVertices[] = [$validation['latitude'], $validation['longitude']];
            }

            $coordinatesArray = $normalizedVertices;
            $lat = $normalizedVertices[0][0];
            $lng = $normalizedVertices[0][1];
            $lengthMeters = $this->geoService->calculateRouteLengthMeters($normalizedVertices);
        } else {
            if ($lat === null || $lng === null) {
                return $this->errorResponse($request, 'Latitude and Longitude are required for point network assets.', 422);
            }
            $validation = $this->geoService->validateKenyaCoordinates($lat, $lng);
            if (!$validation['valid']) {
                return $this->errorResponse($request, $validation['message'], 422);
            }
            $lat = $validation['latitude'];
            $lng = $validation['longitude'];
        }

        $countyName = $validated['county'] ?? null;
        if (empty($countyName) && $lat !== null && $lng !== null) {
            $nearestCounty = $this->geoService->resolveNearestCounty($lat, $lng);
            $countyName = $nearestCounty['name'] ?? null;
        }

        $code = !empty($validated['code'])
            ? strtoupper(trim($validated['code']))
            : strtoupper(substr($subtype, 0, 3)) . '-' . strtoupper(Str::random(5));

        $asset = FiberInfrastructure::withoutGlobalScopes()->create([
            'tenant_id' => $activeTenantId,
            'site_id' => $validated['site_id'] ?? null,
            'name' => $validated['name'],
            'code' => $code,
            'type' => FiberInfrastructure::normalizeBaseType($subtype),
            'asset_subtype' => $subtype,
            'county' => $countyName,
            'sub_county' => $validated['sub_county'] ?? null,
            'town' => $validated['town'] ?? null,
            'estate_or_road' => $validated['estate_or_road'] ?? null,
            'coordinate_source' => $validated['coordinate_source'] ?? 'manual_pin',
            'latitude' => $lat,
            'longitude' => $lng,
            'elevation_m' => $validated['elevation_m'] ?? null,
            'route_coordinates_json' => $coordinatesArray,
            'length_meters' => $lengthMeters,
            'capacity_cores' => $validated['total_ports'] ?? ($isLineAsset ? 0 : 8),
            'used_cores' => $validated['used_ports'] ?? 0,
            'azimuth_deg' => $validated['azimuth_deg'] ?? null,
            'frequency_ghz' => $validated['frequency_ghz'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'created_by_user_id' => $context['user']->id,
            'updated_by_user_id' => $context['user']->id,
        ]);

        AuditLog::create([
            'tenant_id' => $activeTenantId,
            'user_id' => $context['user']->id,
            'action' => 'gis.asset.created',
            'entity_type' => 'FiberInfrastructure',
            'entity_id' => $asset->id,
            'new_values' => [
                'name' => $asset->name,
                'code' => $asset->code,
                'asset_subtype' => $asset->asset_subtype,
                'county' => $asset->county,
                'latitude' => $asset->latitude,
                'longitude' => $asset->longitude,
                'length_meters' => $asset->length_meters,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "GIS Asset {$asset->name} ({$asset->code}) saved in {$asset->county} County.",
                'asset' => $asset,
            ], 201);
        }

        return redirect()->route('gis.map')->with('success', "GIS Asset {$asset->name} ({$asset->code}) saved at [{$asset->latitude}, {$asset->longitude}] ({$asset->county} County).");
    }

    /**
     * Update or move an existing ISP GIS network asset.
     */
    public function updateAsset(Request $request, int $assetId)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_edit'] || !$context['active_tenant_id']) {
            abort(403, 'You do not have permission to edit or move GIS network assets.');
        }

        $activeTenantId = $context['active_tenant_id'];
        $asset = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $activeTenantId)
            ->where('id', $assetId)
            ->first();

        if (!$asset) {
            abort(404, 'GIS network asset not found in your ISP tenant workspace.');
        }

        $allowedSubtypes = implode(',', array_keys(FiberInfrastructure::SUPPORTED_SUBTYPES));

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:160',
            'code' => 'nullable|string|max:64',
            'asset_subtype' => "sometimes|required|string|in:{$allowedSubtypes}",
            'site_id' => 'nullable|integer|exists:sites,id',
            'county' => 'nullable|string|max:80',
            'sub_county' => 'nullable|string|max:80',
            'town' => 'nullable|string|max:80',
            'estate_or_road' => 'nullable|string|max:160',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'coordinates_json' => 'nullable',
            'total_ports' => 'nullable|integer|min:0|max:4096',
            'used_ports' => 'nullable|integer|min:0|max:4096',
            'elevation_m' => 'nullable|numeric|min:-50|max:6000',
            'azimuth_deg' => 'nullable|numeric|min:0|max:360',
            'frequency_ghz' => 'nullable|numeric|min:0|max:100',
            'status' => 'sometimes|required|string|in:active,operational,maintenance,faulty,damaged,planned',
            'coordinate_source' => 'nullable|string|in:gps_survey,gps_surveyed,manual_pin,geocoded_approx,imported_kml',
            'notes' => 'nullable|string|max:2000',
        ]);

        $oldValues = [
            'name' => $asset->name,
            'asset_subtype' => $asset->effective_type,
            'county' => $asset->county,
            'latitude' => $asset->latitude,
            'longitude' => $asset->longitude,
            'status' => $asset->status,
        ];

        $subtype = $validated['asset_subtype'] ?? $asset->effective_type;
        $isLineAsset = in_array($subtype, ['fiber_route', 'wireless_link'], true);

        if ($isLineAsset && array_key_exists('coordinates_json', $validated) && $validated['coordinates_json'] !== null) {
            $rawCoords = $validated['coordinates_json'];
            if (is_string($rawCoords)) {
                $rawCoords = json_decode($rawCoords, true);
            }
            if (!is_array($rawCoords) || count($rawCoords) < 2) {
                return $this->errorResponse($request, 'A fiber route or wireless link requires at least 2 coordinate vertices.', 422);
            }
            $normalizedVertices = [];
            foreach ($rawCoords as $idx => $vertex) {
                $vLat = (float) ($vertex[0] ?? 0);
                $vLng = (float) ($vertex[1] ?? 0);
                $validation = $this->geoService->validateKenyaCoordinates($vLat, $vLng);
                if (!$validation['valid']) {
                    return $this->errorResponse($request, "Route vertex #{$idx} failed validation: {$validation['message']}", 422);
                }
                $normalizedVertices[] = [$validation['latitude'], $validation['longitude']];
            }
            $asset->route_coordinates_json = $normalizedVertices;
            $asset->latitude = $normalizedVertices[0][0];
            $asset->longitude = $normalizedVertices[0][1];
            $asset->length_meters = $this->geoService->calculateRouteLengthMeters($normalizedVertices);
        } elseif ($request->filled('latitude') || $request->filled('longitude')) {
            $lat = (float) $request->input('latitude', $asset->latitude);
            $lng = (float) $request->input('longitude', $asset->longitude);
            $validation = $this->geoService->validateKenyaCoordinates($lat, $lng);
            if (!$validation['valid']) {
                return $this->errorResponse($request, $validation['message'], 422);
            }
            $asset->latitude = $validation['latitude'];
            $asset->longitude = $validation['longitude'];

            if (empty($validated['county'])) {
                $nearestCounty = $this->geoService->resolveNearestCounty($asset->latitude, $asset->longitude);
                $asset->county = $nearestCounty['name'] ?? $asset->county;
            }
        }

        if (isset($validated['name'])) {
            $asset->name = $validated['name'];
        }
        if (!empty($validated['code'])) {
            $asset->code = strtoupper(trim($validated['code']));
        }
        if (isset($validated['asset_subtype'])) {
            $asset->asset_subtype = $validated['asset_subtype'];
            $asset->type = FiberInfrastructure::normalizeBaseType($validated['asset_subtype']);
        }
        foreach (['site_id', 'county', 'sub_county', 'town', 'estate_or_road', 'total_ports', 'used_ports', 'elevation_m', 'azimuth_deg', 'frequency_ghz', 'status', 'coordinate_source', 'notes'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $asset->{$field} = $validated[$field];
            }
        }
        $asset->updated_by_user_id = $context['user']->id;
        $asset->save();

        AuditLog::create([
            'tenant_id' => $activeTenantId,
            'user_id' => $context['user']->id,
            'action' => 'gis.asset.updated',
            'entity_type' => 'FiberInfrastructure',
            'entity_id' => $asset->id,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $asset->name,
                'asset_subtype' => $asset->asset_subtype,
                'county' => $asset->county,
                'latitude' => $asset->latitude,
                'longitude' => $asset->longitude,
                'status' => $asset->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "GIS Asset {$asset->name} updated.",
                'asset' => $asset->fresh(),
            ]);
        }

        return redirect()->route('gis.map')->with('success', "GIS Asset {$asset->name} updated at [{$asset->latitude}, {$asset->longitude}].");
    }

    /**
     * Delete an ISP GIS network asset (restricted to ISP Admin or audited Super Admin support session).
     */
    public function destroyAsset(Request $request, int $assetId)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_delete_export'] || !$context['active_tenant_id']) {
            abort(403, 'Only ISP Administrators can delete GIS network infrastructure assets.');
        }

        $activeTenantId = $context['active_tenant_id'];
        $asset = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $activeTenantId)
            ->where('id', $assetId)
            ->first();

        if (!$asset) {
            abort(404, 'GIS network asset not found in your ISP tenant workspace.');
        }

        $snapshot = [
            'name' => $asset->name,
            'code' => $asset->code,
            'asset_subtype' => $asset->effective_type,
            'county' => $asset->county,
            'latitude' => $asset->latitude,
            'longitude' => $asset->longitude,
        ];

        $asset->delete();

        AuditLog::create([
            'tenant_id' => $activeTenantId,
            'user_id' => $context['user']->id,
            'action' => 'gis.asset.deleted',
            'entity_type' => 'FiberInfrastructure',
            'entity_id' => $assetId,
            'old_values' => $snapshot,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "GIS Asset {$snapshot['name']} ({$snapshot['code']}) deleted.",
            ]);
        }

        return redirect()->route('gis.map')->with('success', "GIS Asset {$snapshot['name']} ({$snapshot['code']}) deleted.");
    }

    /**
     * Export authorized tenant GIS assets as a standards-compliant EPSG:4326 GeoJSON FeatureCollection.
     */
    public function exportAssets(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_delete_export'] || !$context['active_tenant_id']) {
            abort(403, 'You do not have permission to export sensitive GIS network infrastructure locations.');
        }

        $activeTenantId = $context['active_tenant_id'];
        $tenant = Tenant::findOrFail($activeTenantId);
        $assets = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $activeTenantId)
            ->orderBy('id')
            ->get();

        $features = [];
        foreach ($assets as $asset) {
            $isLine = in_array($asset->effective_type, ['fiber_route', 'wireless_link'], true)
                && is_array($asset->coordinates_json)
                && count($asset->coordinates_json) >= 2;

            if ($isLine) {
                $coords = array_map(fn ($pt) => [(float) $pt[1], (float) $pt[0]], $asset->coordinates_json);
                $geometry = [
                    'type' => 'LineString',
                    'coordinates' => $coords,
                ];
            } elseif ($asset->latitude !== null && $asset->longitude !== null) {
                $geometry = [
                    'type' => 'Point',
                    'coordinates' => [(float) $asset->longitude, (float) $asset->latitude],
                ];
            } else {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $asset->id,
                    'code' => $asset->code,
                    'name' => $asset->name,
                    'asset_subtype' => $asset->effective_type,
                    'county' => $asset->county,
                    'town' => $asset->town,
                    'status' => $asset->status,
                    'total_ports' => $asset->total_ports,
                    'used_ports' => $asset->used_ports,
                    'length_meters' => $asset->length_meters,
                    'coordinate_source' => $asset->coordinate_source,
                ],
                'geometry' => $geometry,
            ];
        }

        AuditLog::create([
            'tenant_id' => $activeTenantId,
            'user_id' => $context['user']->id,
            'action' => 'gis.assets.exported',
            'entity_type' => 'Tenant',
            'entity_id' => $activeTenantId,
            'new_values' => [
                'exported_features_count' => count($features),
                'crs' => 'EPSG:4326',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'metadata' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'crs' => 'EPSG:4326',
                'exported_at' => now()->toIso8601String(),
                'features_count' => count($features),
            ],
            'features' => $features,
        ]);
    }

    /**
     * Super Admin explicitly authorized & audited support workflow to inspect a tenant's GIS map.
     */
    public function grantSupportAccess(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Only Platform Super Administrators can initiate audited GIS support sessions.');
        }

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'support_reason' => 'required|string|min:8|max:500',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);

        session([
            'gis_support_tenant_id' => $tenant->id,
            'gis_support_reason' => $validated['support_reason'],
            'gis_support_granted_at' => now()->toIso8601String(),
        ]);

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'event' => 'gis.support_access.granted',
            'auditable_type' => 'Tenant',
            'auditable_id' => $tenant->id,
            'support_access_reason' => $validated['support_reason'],
            'new_values' => [
                'tenant_name' => $tenant->name,
                'support_reason' => $validated['support_reason'],
                'granted_at' => now()->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return redirect()->route('gis.map')->with('success', "Audited GIS Support Session started for {$tenant->name}. All actions are logged.");
    }

    /**
     * Revoke Super Admin GIS support session and return to platform-level Kenya overview.
     */
    public function revokeSupportAccess(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        $previousTenantId = session('gis_support_tenant_id');
        session()->forget(['gis_support_tenant_id', 'gis_support_reason', 'gis_support_granted_at']);

        if ($previousTenantId) {
            AuditLog::create([
                'tenant_id' => (int) $previousTenantId,
                'user_id' => $user->id,
                'action' => 'gis.support_access.revoked',
                'entity_type' => 'Tenant',
                'entity_id' => (int) $previousTenantId,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        return redirect()->route('gis.map')->with('success', 'Returned to Platform-Level Kenya Overview. Private tenant GIS session closed.');
    }

    /**
     * Field Operations & Installation Work Orders page (preserved and tenant-scoped).
     */
    public function jobs(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        $activeTenantId = $context['active_tenant_id'];

        $jobsQuery = InstallationJob::withoutGlobalScopes()->with(['client', 'technician.user'])->latest('scheduled_at');
        $techQuery = Technician::withoutGlobalScopes()->with('user')->where('is_available', true);
        $fatQuery = FiberInfrastructure::withoutGlobalScopes()->where(function ($q) {
            $q->where('type', 'fat_box')->orWhereIn('asset_subtype', ['fat_box', 'distribution_point', 'cabinet']);
        });
        $clientQuery = Client::withoutGlobalScopes()->whereIn('status', ['pending_installation', 'active']);

        if ($activeTenantId) {
            $jobsQuery->where('tenant_id', $activeTenantId);
            $techQuery->where('tenant_id', $activeTenantId);
            $fatQuery->where('tenant_id', $activeTenantId);
            $clientQuery->where('tenant_id', $activeTenantId);
        } else {
            $jobsQuery->whereRaw('1 = 0');
            $techQuery->whereRaw('1 = 0');
            $fatQuery->whereRaw('1 = 0');
            $clientQuery->whereRaw('1 = 0');
        }

        $jobs = $jobsQuery->paginate(15);
        $technicians = $techQuery->get();
        $fatBoxes = $fatQuery->get();
        $pendingClients = $clientQuery->get();

        return view('gis.jobs', compact('jobs', 'technicians', 'fatBoxes', 'pendingClients'));
    }

    public function storeJob(Request $request)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_edit'] || !$context['active_tenant_id']) {
            abort(403, 'You do not have permission to dispatch installation jobs.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'fiber_box_id' => 'nullable|exists:fiber_infrastructure,id',
            'scheduled_at' => 'required|date',
            'fiber_cable_meters' => 'nullable|integer',
            'technician_notes' => 'nullable|string',
        ]);

        $client = Client::withoutGlobalScopes()
            ->where('tenant_id', $context['active_tenant_id'])
            ->findOrFail($validated['client_id']);

        $job = InstallationJob::withoutGlobalScopes()->create(array_merge($validated, [
            'tenant_id' => $context['active_tenant_id'],
            'job_number' => 'JOB-' . strtoupper(Str::random(6)),
            'status' => 'scheduled',
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
        ]));

        return back()->with('success', "Installation Work Order {$job->job_number} dispatched.");
    }

    public function completeJob(InstallationJob $job, Request $request)
    {
        $context = $this->resolveAccessContext($request);
        if (!$context['can_edit'] || ($context['active_tenant_id'] && (int) $job->tenant_id !== (int) $context['active_tenant_id'])) {
            abort(403, 'You do not have permission to complete this work order.');
        }

        $validated = $request->validate([
            'optical_power_rx_dbm' => 'required|numeric',
            'installed_ont_serial' => 'nullable|string',
            'installed_cpe_serial' => 'nullable|string',
            'technician_notes' => 'nullable|string',
        ]);

        $job->update(array_merge($validated, [
            'status' => 'completed',
            'completed_at' => now(),
        ]));

        if ($job->client) {
            $job->client->update(['status' => 'active']);
        }

        return back()->with('success', "Work Order {$job->job_number} completed. Optical power verified: {$job->optical_power_rx_dbm} dBm.");
    }

    /**
     * Backfill missing county & asset_subtype fields on existing records using verified coordinates.
     * Never alters or overwrites existing coordinates.
     */
    protected function backfillMissingCountyTagsForTenant(int $tenantId): void
    {
        $untaggedAssets = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereNull('county')->orWhereNull('asset_subtype');
            })
            ->get();

        foreach ($untaggedAssets as $asset) {
            $lat = $asset->latitude !== null ? (float) $asset->latitude : null;
            $lng = $asset->longitude !== null ? (float) $asset->longitude : null;
            if (($lat === null || $lng === null) && is_array($asset->coordinates_json) && !empty($asset->coordinates_json[0])) {
                $lat = (float) $asset->coordinates_json[0][0];
                $lng = (float) $asset->coordinates_json[0][1];
                $asset->latitude = $lat;
                $asset->longitude = $lng;
                $asset->length_meters = $asset->length_meters ?: $this->geoService->calculateRouteLengthMeters($asset->coordinates_json);
            }

            if (!$asset->county && $lat !== null && $lng !== null) {
                $nearest = $this->geoService->resolveNearestCounty($lat, $lng);
                $asset->county = $nearest['name'] ?? 'Nairobi';
            }

            if (!$asset->asset_subtype) {
                $asset->asset_subtype = $asset->type ?: 'fat_box';
            }

            $asset->save();
        }

        $untaggedClients = Client::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('county')
            ->get();

        foreach ($untaggedClients as $client) {
            $nearest = $this->geoService->resolveNearestCounty((float) $client->latitude, (float) $client->longitude);
            $client->update(['county' => $nearest['name'] ?? 'Nairobi']);
        }

        $untaggedSites = Site::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('county')
            ->get();

        foreach ($untaggedSites as $site) {
            $nearest = $this->geoService->resolveNearestCounty((float) $site->latitude, (float) $site->longitude);
            $site->update(['county' => $nearest['name'] ?? 'Nairobi']);
        }
    }

    /**
     * Non-destructively enrich the primary demo tenant (which has POP-NBO-01 from DatabaseSeeder)
     * with representative multi-county Kenyan network assets so all 12+ asset layers are populated.
     */
    protected function ensureRepresentativeKenyaAssetsForDemoTenant(int $tenantId): void
    {
        $hasDemoPop = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', 'POP-NBO-01')
            ->exists();

        if (!$hasDemoPop) {
            return;
        }

        $alreadyEnriched = FiberInfrastructure::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', 'POP-MSA-01')
            ->exists();

        if ($alreadyEnriched) {
            return;
        }

        $primarySite = Site::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();
        $siteId = $primarySite?->id;

        $representativeAssets = [
            [
                'name' => 'Mombasa Nyali Core PoP',
                'code' => 'POP-MSA-01',
                'asset_subtype' => 'pop',
                'county' => 'Mombasa',
                'town' => 'Nyali',
                'estate_or_road' => 'Links Road Nyali',
                'latitude' => -4.0435000,
                'longitude' => 39.6682000,
                'capacity_cores' => 96,
                'used_cores' => 64,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Coastal regional aggregation PoP & subsea landing interconnect',
            ],
            [
                'name' => 'Kisumu Milimani Regional PoP',
                'code' => 'POP-KSM-01',
                'asset_subtype' => 'pop',
                'county' => 'Kisumu',
                'town' => 'Kisumu City',
                'estate_or_road' => 'Oginga Odinga Street',
                'latitude' => -0.0917000,
                'longitude' => 34.7680000,
                'capacity_cores' => 64,
                'used_cores' => 42,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Lake Basin core aggregation PoP',
            ],
            [
                'name' => 'Nakuru CBD Metro PoP',
                'code' => 'POP-NKR-01',
                'asset_subtype' => 'pop',
                'county' => 'Nakuru',
                'town' => 'Nakuru City',
                'estate_or_road' => 'Kenyatta Avenue',
                'latitude' => -0.3031000,
                'longitude' => 36.0800000,
                'capacity_cores' => 64,
                'used_cores' => 48,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Rift Valley regional distribution node',
            ],
            [
                'name' => 'Westlands Waiyaki Way Splice Closure',
                'code' => 'SPC-NBO-01',
                'asset_subtype' => 'splice_closure',
                'county' => 'Nairobi',
                'town' => 'Westlands',
                'estate_or_road' => 'Waiyaki Way',
                'latitude' => -1.2648000,
                'longitude' => 36.8015000,
                'capacity_cores' => 48,
                'used_cores' => 36,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '48-Core dome optical splice closure',
            ],
            [
                'name' => 'Upper Hill Hospital Rd Utility Manhole',
                'code' => 'MNH-NBO-01',
                'asset_subtype' => 'manhole',
                'county' => 'Nairobi',
                'town' => 'Upper Hill',
                'estate_or_road' => 'Hospital Road',
                'latitude' => -1.2985000,
                'longitude' => 36.8125000,
                'capacity_cores' => 24,
                'used_cores' => 18,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Underground duct chamber MH-01 with Slack Loop',
            ],
            [
                'name' => 'Kilimani Street Optical Cross-Connect Cabinet',
                'code' => 'CAB-NBO-01',
                'asset_subtype' => 'cabinet',
                'county' => 'Nairobi',
                'town' => 'Kilimani',
                'estate_or_road' => 'Argwings Kodhek Rd',
                'latitude' => -1.2921000,
                'longitude' => 36.7885000,
                'capacity_cores' => 144,
                'used_cores' => 112,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Outdoor FDT 144-core street cabinet',
            ],
            [
                'name' => 'Ruiru Thika Rd Distribution Point (FDP)',
                'code' => 'FDP-KBU-01',
                'asset_subtype' => 'distribution_point',
                'county' => 'Kiambu',
                'town' => 'Ruiru',
                'estate_or_road' => 'Thika Superhighway A2',
                'latitude' => -1.1486000,
                'longitude' => 36.9606000,
                'capacity_cores' => 24,
                'used_cores' => 19,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '24-port aerial distribution point serving Ruiru Bypass estate',
            ],
            [
                'name' => 'Huawei MA5800-X7 GPON OLT Chassis (Westlands)',
                'code' => 'OLT-NBO-01',
                'asset_subtype' => 'olt',
                'county' => 'Nairobi',
                'town' => 'Westlands',
                'estate_or_road' => 'Parklands Rd',
                'latitude' => -1.2632000,
                'longitude' => 36.8051000,
                'capacity_cores' => 16,
                'used_cores' => 12,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '16-PON GPON OLT Chassis',
            ],
            [
                'name' => 'Enterprise ONU/ONT - Lavington Mall',
                'code' => 'ONU-NBO-01',
                'asset_subtype' => 'onu_ont',
                'county' => 'Nairobi',
                'town' => 'Lavington',
                'estate_or_road' => 'James Gichuru Rd',
                'latitude' => -1.2788000,
                'longitude' => 36.7712000,
                'capacity_cores' => 4,
                'used_cores' => 4,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Enterprise GPON ONT -18.4 dBm Rx',
            ],
            [
                'name' => 'Eldoret Kapsoya Wireless Mast Tower',
                'code' => 'TWR-ELD-01',
                'asset_subtype' => 'tower',
                'county' => 'Uasin Gishu',
                'town' => 'Eldoret',
                'estate_or_road' => 'Uganda Road A104',
                'latitude' => 0.5143000,
                'longitude' => 35.2698000,
                'capacity_cores' => 12,
                'used_cores' => 8,
                'elevation_m' => 2095,
                'azimuth_deg' => 120,
                'frequency_ghz' => 5.8,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '45m Lattice Tower with 3x Cambium ePMP sectors',
            ],
            [
                'name' => 'MikroTik CCR2116 Core Edge Router (CBD)',
                'code' => 'RTR-NBO-01',
                'asset_subtype' => 'router',
                'county' => 'Nairobi',
                'town' => 'Nairobi CBD',
                'estate_or_road' => 'Kenyatta Avenue',
                'latitude' => -1.2864000,
                'longitude' => 36.8172000,
                'capacity_cores' => 16,
                'used_cores' => 11,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => 'Core BGP/PPPoE BNG Router',
            ],
            [
                'name' => 'Ngong Road Concrete Utility Pole #14',
                'code' => 'POL-NBO-14',
                'asset_subtype' => 'pole',
                'county' => 'Nairobi',
                'town' => 'Kilimani',
                'estate_or_road' => 'Ngong Road',
                'latitude' => -1.2992000,
                'longitude' => 36.7755000,
                'capacity_cores' => 4,
                'used_cores' => 2,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '12m concrete tension pole with ADSS suspension clamp',
            ],
            [
                'name' => 'Nairobi - Kiambu Thika Highway 96-Core Trunk Fiber',
                'code' => 'FBR-NBO-KBU-96C',
                'asset_subtype' => 'fiber_route',
                'county' => 'Nairobi',
                'town' => 'Nairobi - Thika Corridor',
                'estate_or_road' => 'Thika Superhighway (A2)',
                'latitude' => -1.2635000,
                'longitude' => 36.8032000,
                'route_coordinates_json' => [
                    [-1.2635000, 36.8032000],
                    [-1.2550000, 36.8380000],
                    [-1.2195000, 36.8885000],
                    [-1.1486000, 36.9606000],
                    [-1.0333000, 37.0693000],
                ],
                'capacity_cores' => 96,
                'used_cores' => 72,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '96-Core ADSS/Duct Inter-County Backbone along A2',
            ],
            [
                'name' => 'Limuru Uplands - Nakuru PtP 11GHz Wireless Backhaul',
                'code' => 'WLK-KBU-NKR-01',
                'asset_subtype' => 'wireless_link',
                'county' => 'Nakuru',
                'town' => 'Naivasha - Nakuru',
                'estate_or_road' => 'Rift Escarpment Microwave Hop',
                'latitude' => -1.1120000,
                'longitude' => 36.6425000,
                'route_coordinates_json' => [
                    [-1.1120000, 36.6425000],
                    [-0.7172000, 36.4310000],
                    [-0.3031000, 36.0800000],
                ],
                'capacity_cores' => 2,
                'used_cores' => 2,
                'frequency_ghz' => 11.0,
                'status' => 'operational',
                'coordinate_source' => 'gps_survey',
                'notes' => '1.5 Gbps Full-Duplex Licensed 11GHz PtP Link',
            ],
        ];

        foreach ($representativeAssets as $item) {
            $coords = $item['route_coordinates_json'] ?? null;
            $lengthMeters = is_array($coords) ? $this->geoService->calculateRouteLengthMeters($coords) : null;

            FiberInfrastructure::withoutGlobalScopes()->create(array_merge($item, [
                'tenant_id' => $tenantId,
                'site_id' => $siteId,
                'type' => FiberInfrastructure::normalizeBaseType($item['asset_subtype']),
                'length_meters' => $lengthMeters,
            ]));
        }
    }

    protected function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'errors' => ['coordinates' => [$message]],
            ], $status);
        }

        return back()->withErrors(['gis' => $message])->withInput();
    }
}
