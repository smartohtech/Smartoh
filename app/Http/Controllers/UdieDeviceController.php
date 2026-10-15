<?php

namespace App\Http\Controllers;

use App\Jobs\RunDeviceDiscoveryJob;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\DeviceEvidence;
use App\Models\DeviceModel;
use App\Models\DiscoveryJob;
use App\Models\NetworkSegment;
use App\Models\TenantDeviceModel;
use App\Models\Vrf;
use App\Services\UDIE\ConfidenceEngine;
use App\Services\UDIE\DeviceImageResolver;
use App\Services\UDIE\DiscoveryCoordinator;
use App\Services\UDIE\IdentificationEngine;
use Illuminate\Http\Request;

class UdieDeviceController extends Controller
{
    public function __construct(
        protected DiscoveryCoordinator $coordinator,
        protected DeviceImageResolver $imageResolver,
        protected IdentificationEngine $identificationEngine,
        protected ConfidenceEngine $confidenceEngine
    ) {}

    protected function getTenantId(): int
    {
        if (app()->bound('current_tenant_id')) {
            return (int) app('current_tenant_id');
        }
        return (int) (session('current_tenant_id') ?? auth()->user()?->tenant_id ?? 1);
    }

    /**
     * NOC Universal Device Inventory with Real-Time Stats & Filtering
     */
    public function index(Request $request)
    {
        $tenantId = $this->getTenantId();

        // Base query with tenant scope
        $query = Device::where('tenant_id', $tenantId)
            ->with(['deviceModel', 'tenantDeviceModel', 'parentDevice', 'childDevices', 'customer', 'vrf', 'networkSegment']);

        // Filtering
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('mac_address', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('hostname', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($identStatus = $request->input('ident_status')) {
            $query->where('identification_status', $identStatus);
        }

        if ($deviceType = $request->input('device_type')) {
            $query->where('device_type', $deviceType);
        }

        if ($manufacturer = $request->input('manufacturer')) {
            $query->whereRaw('LOWER(manufacturer) = ?', [strtolower($manufacturer)]);
        }

        if ($vrfId = $request->input('vrf_id')) {
            $query->where('vrf_id', $vrfId);
        }

        if ($vlanId = $request->input('vlan_id')) {
            $query->where('vlan_id', $vlanId);
        }

        if ($protocol = $request->input('protocol')) {
            $query->where(function($q) use ($protocol) {
                $q->where('primary_management_protocol', $protocol)
                  ->orWhere('fallback_management_protocol', $protocol);
            });
        }

        if ($role = $request->input('role')) {
            if ($role === 'bridged_onts') {
                $query->whereIn('device_type', ['ont', 'onu'])
                      ->whereHas('childDevices');
            } elseif ($role === 'downstream_routers') {
                $query->whereNotNull('parent_device_id');
            } elseif ($role === 'parents') {
                $query->whereHas('childDevices');
            }
        }

        $devices = $query->latest('last_seen')->paginate(20)->withQueryString();

        // Summary Stats Widgets (Section 43 of Specs)
        $totalDevices = Device::where('tenant_id', $tenantId)->count();
        $online = Device::where('tenant_id', $tenantId)->where('status', 'online')->count();
        $offline = Device::where('tenant_id', $tenantId)->where('status', 'offline')->count();
        $verified = Device::where('tenant_id', $tenantId)->where('identification_status', 'verified')->count();
        $unknown = Device::where('tenant_id', $tenantId)->where('identification_status', 'unknown')->count();
        $partiallyIdentified = Device::where('tenant_id', $tenantId)->where('identification_status', 'partially_identified')->count();

        $tr069Count = Device::where('tenant_id', $tenantId)->where('primary_management_protocol', 'tr069')->count();
        $tr181Count = Device::where('tenant_id', $tenantId)->where('primary_management_protocol', 'tr181_usp')->count();
        $vpnCount = Device::where('tenant_id', $tenantId)->where('primary_management_protocol', 'wireguard_vpn')->count();
        $mikrotikCount = Device::where('tenant_id', $tenantId)->where('primary_management_protocol', 'mikrotik_api')->count();
        $oltCount = Device::where('tenant_id', $tenantId)->whereNotNull('olt_id')->count();

        $bridgedOnts = Device::where('tenant_id', $tenantId)->whereIn('device_type', ['ont', 'onu'])->whereHas('childDevices')->count();
        $downstreamRouters = Device::where('tenant_id', $tenantId)->whereNotNull('parent_device_id')->count();

        $contradictions = DeviceEvent::where('tenant_id', $tenantId)->where('event_type', 'CONTRADICTION_DETECTED')->where('is_resolved', false)->count();
        $replacements = DeviceEvent::where('tenant_id', $tenantId)->where('event_type', 'DEVICE_REPLACEMENT_DETECTED')->where('is_resolved', false)->count();
        $topologyChanges = DeviceEvent::where('tenant_id', $tenantId)->where('event_type', 'DEVICE_TOPOLOGY_CHANGED')->where('is_resolved', false)->count();

        $vrfs = Vrf::where('tenant_id', $tenantId)->get();
        $networkSegments = NetworkSegment::where('tenant_id', $tenantId)->get();
        $manufacturers = Device::where('tenant_id', $tenantId)->whereNotNull('manufacturer')->distinct()->pluck('manufacturer');

        return view('noc.udie.index', compact(
            'devices',
            'totalDevices',
            'online',
            'offline',
            'verified',
            'unknown',
            'partiallyIdentified',
            'tr069Count',
            'tr181Count',
            'vpnCount',
            'mikrotikCount',
            'oltCount',
            'bridgedOnts',
            'downstreamRouters',
            'contradictions',
            'replacements',
            'topologyChanges',
            'vrfs',
            'networkSegments',
            'manufacturers'
        ));
    }

    /**
     * Device Detail View with Tabs: Overview, Identity, Evidence, Topology, Management, History
     */
    public function show(Device $device)
    {
        $device->load([
            'deviceModel',
            'tenantDeviceModel',
            'parentDevice.deviceModel',
            'childDevices.deviceModel',
            'customer',
            'site',
            'vrf',
            'networkSegment',
            'olt',
            'evidences' => fn($q) => $q->latest('observed_at'),
            'events' => fn($q) => $q->latest(),
        ]);

        $resolvedImage = $this->imageResolver->resolveImage($device, 'full');
        $confidenceData = $this->confidenceEngine->calculate($device);

        return view('noc.udie.show', compact('device', 'resolvedImage', 'confidenceData'));
    }

    /**
     * Unknown Device Laboratory
     */
    public function unknownLaboratory(Request $request)
    {
        $tenantId = $this->getTenantId();

        $devices = Device::where('tenant_id', $tenantId)
            ->whereIn('identification_status', ['unknown', 'partially_identified'])
            ->with(['evidences', 'customer', 'site', 'vrf'])
            ->latest('last_seen')
            ->paginate(15);

        $globalModels = DeviceModel::orderBy('manufacturer')->orderBy('model')->get();

        return view('noc.udie.unknown', compact('devices', 'globalModels'));
    }

    /**
     * Manually identify an unknown device
     */
    public function manualIdentify(Request $request, Device $device)
    {
        $validated = $request->validate([
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'device_type' => 'required|string|max:50',
            'catalog_target' => 'required|in:tenant,global',
            'custom_name' => 'nullable|string|max:150',
        ]);

        $tenantId = $device->tenant_id;

        // 1. Check or create Global Model
        $globalModel = DeviceModel::firstOrCreate(
            ['manufacturer' => $validated['manufacturer'], 'model' => $validated['model']],
            [
                'device_type' => $validated['device_type'],
                'image_url' => $this->imageResolver->getGenericIcon($validated['device_type']),
                'thumbnail_url' => $this->imageResolver->getGenericIcon($validated['device_type']),
                'is_global' => true,
            ]
        );

        $tenantModelId = null;
        if ($validated['catalog_target'] === 'tenant' || !empty($validated['custom_name'])) {
            $tenantModel = TenantDeviceModel::updateOrCreate(
                ['tenant_id' => $tenantId, 'global_device_model_id' => $globalModel->id],
                [
                    'custom_name' => $validated['custom_name'] ?: "{$validated['manufacturer']} {$validated['model']}",
                    'is_approved' => true,
                ]
            );
            $tenantModelId = $tenantModel->id;
        }

        // Add manual evidence record with high confidence weight
        DeviceEvidence::create([
            'tenant_id' => $tenantId,
            'device_id' => $device->id,
            'source' => 'manual',
            'attribute_name' => 'model',
            'attribute_value' => $validated['model'],
            'evidence_scope' => ['verified_by_user_id' => auth()->id()],
            'is_contradictory' => false,
            'confidence_weight' => 50.0,
            'observed_at' => now(),
        ]);

        $device->update([
            'manufacturer' => $validated['manufacturer'],
            'model' => $validated['model'],
            'device_type' => $validated['device_type'],
            'device_model_id' => $globalModel->id,
            'tenant_device_model_id' => $tenantModelId,
            'identification_status' => 'verified',
            'confidence_score' => 100.0,
            'last_identified_at' => now(),
        ]);

        DeviceEvent::create([
            'tenant_id' => $tenantId,
            'device_id' => $device->id,
            'event_type' => 'DEVICE_VERIFIED',
            'severity' => 'info',
            'title' => "Device Manually Identified & Verified",
            'description' => "Verified as {$validated['manufacturer']} {$validated['model']} by " . (auth()->user()?->name ?? 'Administrator'),
            'details' => $validated,
        ]);

        return back()->with('success', "Device #{$device->id} successfully identified and verified as {$validated['manufacturer']} {$validated['model']}.");
    }

    /**
     * Device Model Registry View (Global vs Tenant Catalog)
     */
    public function modelRegistry(Request $request)
    {
        $tenantId = $this->getTenantId();

        $globalModels = DeviceModel::withCount('devices')->orderBy('manufacturer')->paginate(15, ['*'], 'global_page');
        $tenantModels = TenantDeviceModel::where('tenant_id', $tenantId)->with(['globalModel', 'devices'])->paginate(15, ['*'], 'tenant_page');

        return view('noc.udie.models', compact('globalModels', 'tenantModels'));
    }

    /**
     * Store Tenant Model Customization
     */
    public function storeTenantModel(Request $request)
    {
        $tenantId = $this->getTenantId();

        $validated = $request->validate([
            'global_device_model_id' => 'nullable|exists:device_models,id',
            'custom_name' => 'required|string|max:150',
            'custom_description' => 'nullable|string',
            'custom_image_url' => 'nullable|url|max:255',
        ]);

        TenantDeviceModel::create([
            'tenant_id' => $tenantId,
            'global_device_model_id' => $validated['global_device_model_id'] ?? null,
            'custom_name' => $validated['custom_name'],
            'custom_description' => $validated['custom_description'] ?? null,
            'custom_image_url' => $validated['custom_image_url'] ?? null,
            'is_approved' => true,
        ]);

        return back()->with('success', "Tenant Device Model '{$validated['custom_name']}' created successfully.");
    }

    /**
     * Trigger asynchronous or immediate network discovery sweep
     */
    public function triggerDiscovery(Request $request)
    {
        $tenantId = $this->getTenantId();

        $job = DiscoveryJob::create([
            'tenant_id' => $tenantId,
            'source' => $request->input('source', 'all'),
            'target_scope' => 'Enrolled MikroTik, OLT, and Network Segments',
            'status' => 'pending',
        ]);

        // Run synchronously for instant user feedback or queue if background requested
        RunDeviceDiscoveryJob::dispatchSync($job->id);

        $job->refresh();

        return back()->with('success', "UDIE Discovery sweep completed! Discovered: {$job->devices_discovered_count} device(s), Identified: {$job->devices_identified_count} device(s).");
    }

    /**
     * Re-identify an individual device on demand
     */
    public function reidentify(Device $device)
    {
        $this->coordinator->reidentifyDevice($device);
        return back()->with('success', "Device #{$device->id} re-identification completed.");
    }

    /**
     * Resolve / acknowledge an anomaly event (Replacement or Topology change)
     */
    public function resolveEvent(DeviceEvent $event)
    {
        $event->update(['is_resolved' => true]);
        return back()->with('success', "Event '{$event->title}' has been confirmed and resolved.");
    }
}
