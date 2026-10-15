<?php

namespace App\Services\UDIE\Data;

class DeviceEvidenceData
{
    public int $tenantId;
    public ?int $siteId = null;
    public ?int $vrfId = null;
    public ?int $networkSegmentId = null;
    public ?int $vlanId = null;
    public string $source; // dhcp, arp, ipv6_nd, lldp, radius, pppoe, olt, tr069, tr181_usp, mikrotik, snmp, vpn, lan_api, manual
    public ?string $timestamp = null;

    public ?string $ipAddress = null;
    public ?string $macAddress = null;
    public ?string $hostname = null;
    public ?string $manufacturer = null;
    public ?string $model = null;
    public ?string $productClass = null;
    public ?string $serialNumber = null;
    public ?string $firmwareVersion = null;
    public ?string $hardwareVersion = null;
    public ?string $deviceType = null;
    public ?string $interface = null;

    public ?int $oltId = null;
    public ?string $ponId = null;
    public ?string $onuId = null;
    public ?int $customerId = null;
    public ?int $serviceId = null;
    public ?string $pppoeUsername = null;
    public ?string $vpnPeerId = null;
    public ?int $upstreamDeviceId = null;

    public ?string $protocol = null;
    public ?string $protocolStatus = null;
    public array $rawMetadata = [];
    public float $confidenceContribution = 1.0;
    public array $evidenceScope = [];

    public function __construct(
        int $tenantId,
        string $source,
        array $attributes = [],
        ?string $ipAddress = null,
        ?string $macAddress = null,
        ?string $hostname = null,
        ?string $manufacturer = null,
        ?string $model = null,
        ?string $productClass = null,
        ?string $serialNumber = null,
        ?string $firmwareVersion = null,
        ?string $hardwareVersion = null,
        ?string $deviceType = null,
        ?string $interface = null,
        ?int $siteId = null,
        ?int $vrfId = null,
        ?int $networkSegmentId = null,
        ?int $vlanId = null,
        ?int $oltId = null,
        ?string $ponId = null,
        ?string $onuId = null,
        ?int $customerId = null,
        ?int $serviceId = null,
        ?string $pppoeUsername = null,
        ?string $vpnPeerId = null,
        ?string $protocol = null,
        ?string $protocolStatus = null,
        array $rawMetadata = [],
        float $confidenceContribution = 1.0,
        array $evidenceScope = [],
        ?string $timestamp = null,
        ?int $upstreamDeviceId = null
    ) {
        $this->tenantId = $tenantId;
        $this->source = $source;
        $this->timestamp = $timestamp ?? now()->toISOString();

        $this->ipAddress = $ipAddress;
        $this->macAddress = $macAddress;
        $this->hostname = $hostname;
        $this->manufacturer = $manufacturer;
        $this->model = $model;
        $this->productClass = $productClass;
        $this->serialNumber = $serialNumber;
        $this->firmwareVersion = $firmwareVersion;
        $this->hardwareVersion = $hardwareVersion;
        $this->deviceType = $deviceType;
        $this->interface = $interface;

        $this->siteId = $siteId;
        $this->vrfId = $vrfId;
        $this->networkSegmentId = $networkSegmentId;
        $this->vlanId = $vlanId;
        $this->oltId = $oltId;
        $this->ponId = $ponId;
        $this->onuId = $onuId;
        $this->customerId = $customerId;
        $this->serviceId = $serviceId;
        $this->pppoeUsername = $pppoeUsername;
        $this->vpnPeerId = $vpnPeerId;
        $this->upstreamDeviceId = $upstreamDeviceId;

        $this->protocol = $protocol;
        $this->protocolStatus = $protocolStatus;
        $this->rawMetadata = $rawMetadata;
        $this->confidenceContribution = $confidenceContribution;

        // Apply any array attributes passed
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key) && $value !== null) {
                $this->{$key} = $value;
            }
        }

        // Set evidence scope
        $this->evidenceScope = !empty($evidenceScope) ? $evidenceScope : [
            'tenant_id' => $this->tenantId,
            'site_id' => $this->siteId,
            'vrf_id' => $this->vrfId,
            'network_segment_id' => $this->networkSegmentId,
            'vlan_id' => $this->vlanId,
            'interface' => $this->interface,
        ];
    }
}
