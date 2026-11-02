<?php

namespace App\Models;

use Database\Seeders\SaasPlanSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaasPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'currency',
        'trial_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price'  => 'decimal:2',
        'trial_days'    => 'integer',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(SaasPlanVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(SaasPlanVersion::class)->where('is_current', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SaasSubscription::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IspApplication::class);
    }

    /**
     * Ensure the 3 default ISP tiers exist with rich differentiated benefits.
     */
    public static function ensureDefaultPlansExist(): void
    {
        if (self::count() === 0) {
            (new SaasPlanSeeder())->run();
            return;
        }

        // Also enrich any existing default plans whose version features lack included_benefits
        foreach (SaasPlanSeeder::defaultPlansData() as $defaultPlan) {
            $plan = self::where('slug', $defaultPlan['slug'])->with('currentVersion')->first();
            if ($plan && $plan->currentVersion) {
                $existingFeatures = $plan->currentVersion->features ?? [];
                if (empty($existingFeatures['included_benefits'])) {
                    $mergedFeatures = array_merge($defaultPlan['features'], $existingFeatures);
                    $mergedFeatures['included_benefits'] = $defaultPlan['features']['included_benefits'];
                    $mergedFeatures['locked_benefits'] = $defaultPlan['features']['locked_benefits'];
                    $mergedFeatures['tier_badge'] = $defaultPlan['features']['tier_badge'];
                    $mergedFeatures['sla_tier'] = $defaultPlan['features']['sla_tier'];
                    $mergedFeatures['support_channel'] = $defaultPlan['features']['support_channel'];
                    $mergedFeatures['max_cpes'] = $existingFeatures['max_cpes'] ?? $defaultPlan['features']['max_cpes'];
                    $mergedFeatures['storage_gb'] = $existingFeatures['storage_gb'] ?? $defaultPlan['features']['storage_gb'];
                    $plan->currentVersion->update(['features' => $mergedFeatures]);
                }
            }
        }
    }

    /**
     * Return structured included benefits for this plan tier.
     */
    public function includedBenefits(): array
    {
        $features = $this->currentVersion?->features ?? [];
        if (!empty($features['included_benefits']) && is_array($features['included_benefits'])) {
            return $features['included_benefits'];
        }

        $benefits = [
            'Up to ' . number_format($features['max_subscribers'] ?? 300) . ' Active Subscribers',
            'Up to ' . ($features['max_routers'] ?? 3) . ' Core MikroTik / OLT Gateways',
            'Up to ' . ($features['max_staff'] ?? 3) . ' Staff & Technician Accounts',
            ($features['max_sites'] ?? 1) . ' Operating POP / Site Branch(es)',
            'Automated M-Pesa Daraja STK Push & Reconciliation',
        ];

        if (!empty($features['has_genieacs_cwmp'])) {
            $benefits[] = 'TR-069 GenieACS Auto-Provisioning & GPON Optical Tests';
        }
        if (!empty($features['has_gis_fiber_maps'])) {
            $benefits[] = 'Interactive GIS Fiber Map & FAT Splicing Planner';
        }
        if (!empty($features['has_advanced_noc'])) {
            $benefits[] = 'AI NOC Copilot, Capacity Telemetry & Rogue Sniffer';
        }
        if (!empty($features['has_white_label'])) {
            $benefits[] = 'White-Label Brand Colors & Subscriber Portal';
        }
        if (!empty($features['has_api_access'])) {
            $benefits[] = 'REST API & Real-Time Webhook Orchestration';
        }
        if (!empty($features['has_bgp_ospf'])) {
            $benefits[] = 'Autonomous BGP & OSPF Core Routing Automation';
        }
        if (!empty($features['has_wholesale_market'])) {
            $benefits[] = 'Wholesale Bandwidth Marketplace & Reseller Pools';
        }
        if (!empty($features['has_multi_site_accounting'])) {
            $benefits[] = 'Multi-Site Double-Entry General Ledger & P&L';
        }
        if (!empty($features['has_dedicated_sla'])) {
            $benefits[] = 'Dedicated 24/7/365 Carrier SLA & Account Engineer';
        }

        return $benefits;
    }

    /**
     * Return structured locked/higher-tier benefits for comparison.
     */
    public function lockedBenefits(): array
    {
        $features = $this->currentVersion?->features ?? [];
        if (isset($features['locked_benefits']) && is_array($features['locked_benefits'])) {
            return $features['locked_benefits'];
        }

        $locked = [];
        if (empty($features['has_genieacs_cwmp'])) {
            $locked[] = 'TR-069 GenieACS & GPON OLT Provisioning';
        }
        if (empty($features['has_gis_fiber_maps'])) {
            $locked[] = 'Interactive GIS Fiber Topology & Splicing Maps';
        }
        if (empty($features['has_advanced_noc'])) {
            $locked[] = 'AI NOC Copilot & Predictive Failure Engine';
        }
        if (empty($features['has_white_label'])) {
            $locked[] = 'White-Label Subscriber Portal & Custom Domain';
        }
        if (empty($features['has_api_access'])) {
            $locked[] = 'REST API & External Webhook Triggers';
        }
        if (empty($features['has_bgp_ospf'])) {
            $locked[] = 'Autonomous BGP / OSPF Routing & Wholesale Market';
        }

        return $locked;
    }
}
