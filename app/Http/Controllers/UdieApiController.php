<?php

namespace App\Http\Controllers;

use App\Jobs\RunDeviceDiscoveryJob;
use App\Models\Device;
use App\Models\DeviceEvidence;
use App\Models\DeviceModel;
use App\Models\DiscoveryJob;
use App\Services\UDIE\ConfidenceEngine;
use App\Services\UDIE\Data\DeviceEvidenceData;
use App\Services\UDIE\DeviceImageResolver;
use App\Services\UDIE\DiscoveryCoordinator;
use App\Services\UDIE\IdentificationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UdieApiController extends Controller
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
     * GET /api/udie/devices
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantId();

        $devices = Device::where('tenant_id', $tenantId)
            ->with(['deviceModel', 'tenantDeviceModel', 'parentDevice', 'childDevices', 'vrf', 'networkSegment'])
            ->when($request->search, function($q, $s) {
                $q->where('ip_address', 'LIKE', "%{$s}%")
                  ->orWhere('mac_address', 'LIKE', "%{$s}%")
                  ->orWhere('serial_number', 'LIKE', "%{$s}%")
                  ->orWhere('model', 'LIKE', "%{$s}%");
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->ident_status, fn($q, $s) => $q->where('identification_status', $s))
            ->when($request->vrf_id, fn($q, $v) => $q->where('vrf_id', $v))
            ->latest('last_seen')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $devices->items(),
            'pagination' => [
                'current_page' => $devices->currentPage(),
                'total' => $devices->total(),
                'per_page' => $devices->perPage(),
                'last_page' => $devices->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/udie/devices/{id}
     */
    public function show(int $id): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $device = Device::where('tenant_id', $tenantId)
            ->with(['deviceModel', 'tenantDeviceModel', 'parentDevice', 'childDevices', 'vrf', 'networkSegment', 'customer', 'site', 'olt'])
            ->findOrFail($id);

        $imageUrl = $this->imageResolver->resolveImage($device, 'full');
        $confidenceData = $this->confidenceEngine->calculate($device);

        return response()->json([
            'success' => true,
            'data' => $device,
            'device' => $device,
            'resolved_image_url' => $imageUrl,
            'confidence_evaluation' => $confidenceData,
        ]);
    }

    /**
     * POST /api/udie/discover
     */
    public function discover(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantId();

        $job = DiscoveryJob::create([
            'tenant_id' => $tenantId,
            'site_id' => $request->site_id,
            'vrf_id' => $request->vrf_id,
            'source' => $request->input('source', 'all'),
            'target_scope' => $request->input('target_scope', 'Enrolled Network Segments'),
            'status' => 'pending',
        ]);

        RunDeviceDiscoveryJob::dispatchSync($job->id);
        $job->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Discovery sweep finished',
            'job' => $job,
        ]);
    }

    /**
     * POST /api/udie/evidence/ingest
     */
    public function ingestEvidence(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantId();

        $validated = $request->validate([
            'source' => 'required|string',
            'mac_address' => 'nullable|string',
            'ip_address' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'model' => 'nullable|string',
            'device_type' => 'nullable|string',
            'hostname' => 'nullable|string',
            'vrf_id' => 'nullable|integer',
            'vlan_id' => 'nullable|integer',
            'site_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer',
            'olt_id' => 'nullable|integer',
            'pon_id' => 'nullable|string',
            'onu_id' => 'nullable|string',
            'raw_metadata' => 'nullable|array',
        ]);

        $evidence = new DeviceEvidenceData($tenantId, $validated['source'], [
            'macAddress' => $validated['mac_address'] ?? null,
            'ipAddress' => $validated['ip_address'] ?? null,
            'serialNumber' => $validated['serial_number'] ?? null,
            'manufacturer' => $validated['manufacturer'] ?? null,
            'model' => $validated['model'] ?? null,
            'deviceType' => $validated['device_type'] ?? null,
            'hostname' => $validated['hostname'] ?? null,
            'vrfId' => $validated['vrf_id'] ?? null,
            'vlanId' => $validated['vlan_id'] ?? null,
            'siteId' => $validated['site_id'] ?? null,
            'customerId' => $validated['customer_id'] ?? null,
            'oltId' => $validated['olt_id'] ?? null,
            'ponId' => $validated['pon_id'] ?? null,
            'onuId' => $validated['onu_id'] ?? null,
            'rawMetadata' => $validated['raw_metadata'] ?? [],
        ]);

        $device = $this->coordinator->ingestEvidence($evidence);

        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'device' => $device,
            'data' => $device,
            'model' => $device->model,
            'identification_status' => $device->identification_status,
            'confidence_score' => $device->confidence_score,
            'parent_device_id' => $device->parent_device_id,
            'primary_management_protocol' => $device->primary_management_protocol,
        ]);
    }

    /**
     * GET /api/udie/devices/{id}/evidence
     */
    public function evidence(int $id): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $device = Device::where('tenant_id', $tenantId)->findOrFail($id);

        $evidences = $device->evidences()->latest('observed_at')->get();
        $calc = $this->confidenceEngine->calculate($device, $evidences);

        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'confidence_score' => $calc['confidence_score'],
            'identification_status' => $calc['identification_status'],
            'supporting_evidence' => $calc['supporting_evidence'],
            'contradictory_evidence' => $calc['contradictory_evidence'],
            'explanation' => $calc['explanation'],
            'raw_evidences' => $evidences,
        ]);
    }

    /**
     * GET /api/udie/devices/{id}/topology
     */
    public function topology(int $id): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $device = Device::where('tenant_id', $tenantId)
            ->with(['parentDevice', 'childDevices', 'topologiesAsParent', 'topologiesAsChild'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'parent' => $device->parentDevice,
            'children' => $device->childDevices,
            'relationships' => [
                'as_parent' => $device->topologiesAsParent,
                'as_child' => $device->topologiesAsChild,
            ],
        ]);
    }

    /**
     * GET /api/udie/devices/{id}/capabilities
     */
    public function capabilities(int $id): JsonResponse
    {
        $tenantId = $this->getTenantId();
        $device = Device::where('tenant_id', $tenantId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'device_id' => $device->id,
            'capabilities' => $device->capabilities,
            'primary_management_protocol' => $device->primary_management_protocol,
            'fallback_management_protocol' => $device->fallback_management_protocol,
        ]);
    }

    /**
     * GET /api/udie/device-models
     */
    public function deviceModels(): JsonResponse
    {
        $models = DeviceModel::withCount('devices')->orderBy('manufacturer')->get();
        return response()->json([
            'success' => true,
            'models' => $models,
        ]);
    }
}
