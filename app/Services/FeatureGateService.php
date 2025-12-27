<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantOverride;
use Illuminate\Support\Facades\Cache;

class FeatureGateService
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Check if tenant can access a feature
     */
    public function canAccess(Tenant $tenant, string $featureKey): bool
    {
        // Check if tenant has active subscription
        if (! $tenant->hasActiveSubscription()) {
            return false;
        }

        // Check for feature override
        $override = $this->getFeatureOverride($tenant, $featureKey);
        if ($override !== null) {
            return $override;
        }

        // Check plan features
        return $this->planHasFeature($tenant->getCurrentPlan(), $featureKey);
    }

    /**
     * Check if plan has a specific feature
     */
    public function planHasFeature(?Plan $plan, string $featureKey): bool
    {
        if (! $plan) {
            return false;
        }

        $cacheKey = "plan_{$plan->id}_feature_{$featureKey}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($plan, $featureKey) {
            return $plan->planFeatures()
                ->whereHas('feature', fn ($q) => $q->where('key', $featureKey))
                ->where('is_enabled', true)
                ->exists();
        });
    }

    /**
     * Get limit value for tenant
     */
    public function getLimit(Tenant $tenant, string $limitKey): int
    {
        // Check for limit override
        $override = $this->getLimitOverride($tenant, $limitKey);
        if ($override !== null) {
            return $override;
        }

        // Get plan limit
        return $this->getPlanLimit($tenant->getCurrentPlan(), $limitKey);
    }

    /**
     * Get plan limit value
     */
    public function getPlanLimit(?Plan $plan, string $limitKey): int
    {
        if (! $plan || ! $plan->limits) {
            return 0;
        }

        return $plan->limits->{$limitKey} ?? 0;
    }

    /**
     * Get feature override for tenant
     */
    protected function getFeatureOverride(Tenant $tenant, string $featureKey): ?bool
    {
        $override = TenantOverride::where('tenant_id', $tenant->id)
            ->where('override_type', 'feature')
            ->where('key', $featureKey)
            ->valid()
            ->first();

        if (! $override) {
            return null;
        }

        return (bool) $override->value;
    }

    /**
     * Get limit override for tenant
     */
    protected function getLimitOverride(Tenant $tenant, string $limitKey): ?int
    {
        $override = TenantOverride::where('tenant_id', $tenant->id)
            ->where('override_type', 'limit')
            ->where('key', $limitKey)
            ->valid()
            ->first();

        if (! $override) {
            return null;
        }

        return (int) $override->value;
    }

    /**
     * Get all available features for tenant
     */
    public function getAvailableFeatures(Tenant $tenant): array
    {
        if (! $tenant->hasActiveSubscription()) {
            return [];
        }

        $plan = $tenant->getCurrentPlan();
        if (! $plan) {
            return [];
        }

        $cacheKey = "tenant_{$tenant->id}_features";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant, $plan) {
            // Get plan features
            $planFeatures = $plan->planFeatures()
                ->with('feature')
                ->where('is_enabled', true)
                ->get()
                ->pluck('feature.key')
                ->toArray();

            // Get feature overrides (additions)
            $overrideFeatures = TenantOverride::where('tenant_id', $tenant->id)
                ->where('override_type', 'feature')
                ->where('value', '1')
                ->valid()
                ->pluck('key')
                ->toArray();

            // Get feature overrides (removals)
            $removedFeatures = TenantOverride::where('tenant_id', $tenant->id)
                ->where('override_type', 'feature')
                ->where('value', '0')
                ->valid()
                ->pluck('key')
                ->toArray();

            // Merge and filter
            $allFeatures = array_unique(array_merge($planFeatures, $overrideFeatures));

            return array_values(array_diff($allFeatures, $removedFeatures));
        });
    }

    /**
     * Get all limits for tenant
     */
    public function getAllLimits(Tenant $tenant): array
    {
        $plan = $tenant->getCurrentPlan();

        $limits = [
            'max_branches' => 0,
            'max_competitors' => 0,
            'max_users' => 0,
            'max_reviews_sync' => 0,
            'max_ai_replies' => 0,
            'max_ai_tokens' => 0,
            'max_api_calls' => 0,
            'max_analysis_runs' => 0,
            'analysis_retention_days' => 0,
        ];

        if ($plan && $plan->limits) {
            foreach ($limits as $key => $value) {
                $limits[$key] = $plan->limits->{$key} ?? 0;
            }
        }

        // Apply overrides
        $overrides = TenantOverride::where('tenant_id', $tenant->id)
            ->where('override_type', 'limit')
            ->valid()
            ->get();

        foreach ($overrides as $override) {
            if (isset($limits[$override->key])) {
                $limits[$override->key] = (int) $override->value;
            }
        }

        return $limits;
    }

    /**
     * Check multiple features at once
     */
    public function canAccessAny(Tenant $tenant, array $featureKeys): bool
    {
        foreach ($featureKeys as $key) {
            if ($this->canAccess($tenant, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if tenant has all specified features
     */
    public function canAccessAll(Tenant $tenant, array $featureKeys): bool
    {
        foreach ($featureKeys as $key) {
            if (! $this->canAccess($tenant, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear cached features for tenant
     */
    public function clearCache(Tenant $tenant): void
    {
        Cache::forget("tenant_{$tenant->id}_features");

        $plan = $tenant->getCurrentPlan();
        if ($plan) {
            // Clear all feature cache for this plan
            $features = Feature::pluck('key');
            foreach ($features as $featureKey) {
                Cache::forget("plan_{$plan->id}_feature_{$featureKey}");
            }
        }
    }

    /**
     * Clear all feature cache for a plan
     */
    public function clearPlanCache(Plan $plan): void
    {
        $features = Feature::pluck('key');
        foreach ($features as $featureKey) {
            Cache::forget("plan_{$plan->id}_feature_{$featureKey}");
        }

        // Clear tenant caches for all tenants on this plan
        $tenantIds = $plan->subscriptions()
            ->whereColumn('id', 'tenants.current_subscription_id')
            ->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            Cache::forget("tenant_{$tenantId}_features");
        }
    }

    /**
     * Get feature details with access status
     */
    public function getFeatureDetails(Tenant $tenant, string $featureKey): array
    {
        $feature = Feature::where('key', $featureKey)->first();

        if (! $feature) {
            return [
                'exists' => false,
                'has_access' => false,
            ];
        }

        $hasAccess = $this->canAccess($tenant, $featureKey);
        $override = $this->getFeatureOverride($tenant, $featureKey);

        return [
            'exists' => true,
            'key' => $feature->key,
            'name' => $feature->name_ar,
            'category' => $feature->category->value,
            'has_access' => $hasAccess,
            'is_overridden' => $override !== null,
            'override_value' => $override,
        ];
    }
}
