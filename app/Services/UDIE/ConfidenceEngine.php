<?php

namespace App\Services\UDIE;

use App\Models\Device;
use App\Models\DeviceEvidence;
use Illuminate\Support\Collection;

class ConfidenceEngine
{
    // Default weights for positive evidence sources
    protected array $positiveSourceWeights = [
        'tr181_usp' => 38.0,
        'tr069' => 35.0,
        'mikrotik_api' => 35.0,
        'olt' => 32.0,
        'snmp' => 25.0,
        'radius' => 20.0,
        'pppoe' => 18.0,
        'dhcp_fingerprint' => 15.0,
        'lldp' => 15.0,
        'dhcp_hostname' => 10.0,
        'mac_oui' => 10.0,
        'arp' => 5.0,
        'vpn' => 15.0,
        'manual' => 50.0,
    ];

    // Contradiction penalties
    protected array $contradictionPenalties = [
        'manufacturer_mismatch' => 60.0,
        'model_conflict' => 45.0,
        'serial_mismatch' => 55.0,
        'mac_conflict' => 50.0,
        'device_type_conflict' => 40.0,
        'scope_ambiguity' => 25.0,
    ];

    /**
     * Calculate confidence score and analyze contradictions for a device
     */
    public function calculate(Device $device, ?Collection $evidences = null): array
    {
        $evidences = $evidences ?: $device->evidences()->get();

        $baseScore = 5.0;
        $positiveTotal = 0.0;
        $contradictionTotal = 0.0;

        $supportingItems = [];
        $contradictoryItems = [];

        // Track distinct observations to detect contradictions
        $observedManufacturers = [];
        $observedModels = [];
        $observedSerials = [];
        $observedTypes = [];

        foreach ($evidences as $ev) {
            $source = strtolower($ev->source);
            $weight = $ev->confidence_weight ?: ($this->positiveSourceWeights[$source] ?? 5.0);

            // Scoped evidence check: if evidence lacks VRF or VLAN in a multi-tenant network, adjust contribution
            $scope = $ev->evidence_scope ?? [];
            if (empty($scope['vrf_id']) && empty($scope['vlan_id']) && in_array($source, ['arp', 'dhcp'])) {
                $weight *= 0.6; // reduce weight due to weak scope
            }

            // Group by attribute
            if ($ev->attribute_name === 'manufacturer' && $ev->attribute_value) {
                $observedManufacturers[$ev->attribute_value][] = ['source' => $source, 'weight' => $weight];
            }
            if ($ev->attribute_name === 'model' && $ev->attribute_value) {
                $observedModels[$ev->attribute_value][] = ['source' => $source, 'weight' => $weight];
            }
            if ($ev->attribute_name === 'serial_number' && $ev->attribute_value) {
                $observedSerials[$ev->attribute_value][] = ['source' => $source, 'weight' => $weight];
            }
            if ($ev->attribute_name === 'device_type' && $ev->attribute_value) {
                $observedTypes[$ev->attribute_value][] = ['source' => $source, 'weight' => $weight];
            }

            if (!$ev->is_contradictory) {
                $positiveTotal += $weight;
                $supportingItems[] = [
                    'source' => $ev->source,
                    'attribute' => $ev->attribute_name,
                    'value' => $ev->attribute_value,
                    'weight' => $weight,
                    'observed_at' => $ev->observed_at?->toIso8601String() ?? now()->toIso8601String(),
                ];
            } else {
                $contradictionTotal += $weight;
                $contradictoryItems[] = [
                    'type' => 'explicit_contradiction',
                    'description' => "Contradictory {$ev->attribute_name} observation from {$source}: {$ev->attribute_value}",
                    'penalty' => $weight,
                    'details' => [$ev->attribute_name => $ev->attribute_value],
                ];
            }
        }

        // Detect Manufacturer Contradictions (e.g. Huawei vs ZTE)
        if (count($observedManufacturers) > 1) {
            $penalty = $this->contradictionPenalties['manufacturer_mismatch'];
            $contradictionTotal += $penalty;
            $contradictoryItems[] = [
                'type' => 'manufacturer_mismatch',
                'description' => 'Multiple conflicting manufacturers detected: ' . implode(' vs ', array_keys($observedManufacturers)),
                'penalty' => $penalty,
                'details' => $observedManufacturers,
            ];
        }

        // Detect Model Contradictions (e.g. RB4011 vs CCR2004)
        if (count($observedModels) > 1) {
            $penalty = $this->contradictionPenalties['model_conflict'];
            $contradictionTotal += $penalty;
            $contradictoryItems[] = [
                'type' => 'model_conflict',
                'description' => 'Multiple conflicting models observed: ' . implode(' vs ', array_keys($observedModels)),
                'penalty' => $penalty,
                'details' => $observedModels,
            ];
        }

        // Detect Serial Number Contradictions (e.g. OLT reports ABC123 but TR-069 reports XYZ456)
        if (count($observedSerials) > 1) {
            $penalty = $this->contradictionPenalties['serial_mismatch'];
            $contradictionTotal += $penalty;
            $contradictoryItems[] = [
                'type' => 'serial_mismatch',
                'description' => 'Conflicting serial numbers reported by management sources: ' . implode(' vs ', array_keys($observedSerials)),
                'penalty' => $penalty,
                'details' => $observedSerials,
            ];
        }

        // Mathematical Formula:
        // FinalScore = BaseScore + PositiveTotal - ContradictionTotal
        $rawScore = $baseScore + $positiveTotal - $contradictionTotal;
        $normalizedScore = (float) max(0.0, min(100.0, $rawScore));

        // Assign status according to configured thresholds
        $status = $this->resolveStatus($normalizedScore, count($contradictoryItems) > 0);

        return [
            'confidence_score' => round($normalizedScore, 2),
            'identification_status' => $status,
            'positive_score' => round($positiveTotal, 2),
            'contradiction_penalty' => round($contradictionTotal, 2),
            'has_contradictions' => count($contradictoryItems) > 0,
            'supporting_evidence' => $supportingItems,
            'contradictory_evidence' => $contradictoryItems,
            'explanation' => $this->buildExplanation($device, $normalizedScore, $status, $supportingItems, $contradictoryItems),
        ];
    }

    /**
     * Resolve verification status from score
     */
    protected function resolveStatus(float $score, bool $hasContradictions): string
    {
        if ($hasContradictions && $score < 75.0) {
            return 'unknown'; // Flag for review
        }
        if ($score >= 90.0) return 'verified';
        if ($score >= 75.0) return 'high_confidence';
        if ($score >= 50.0) return 'partially_identified';
        return 'unknown';
    }

    /**
     * Build plain human explanation for NOC
     */
    protected function buildExplanation(Device $device, float $score, string $status, array $pos, array $contra): string
    {
        $text = "Confidence: {$score}% (" . strtoupper($status) . "). ";
        $text .= "Supported by " . count($pos) . " verified observation(s). ";
        if (!empty($contra)) {
            $text .= "WARNING: " . count($contra) . " contradiction(s) detected! Manual review recommended.";
        }
        return $text;
    }
}
