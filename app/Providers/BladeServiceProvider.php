<?php

namespace App\Providers;

use App\Models\Plan;
use App\Models\UsageSummary;
use App\Services\FeatureGateService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * @feature('feature_key')
         * Check if current tenant has access to a feature
         */
        Blade::if('feature', function (string $featureKey) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant) {
                return false;
            }

            return app(FeatureGateService::class)->canAccess($tenant, $featureKey);
        });

        /**
         * @features(['feature1', 'feature2'])
         * Check if tenant has ANY of the features
         */
        Blade::if('features', function (array $featureKeys) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant) {
                return false;
            }

            return app(FeatureGateService::class)->canAccessAny($tenant, $featureKeys);
        });

        /**
         * @featuresAll(['feature1', 'feature2'])
         * Check if tenant has ALL of the features
         */
        Blade::if('featuresAll', function (array $featureKeys) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant) {
                return false;
            }

            return app(FeatureGateService::class)->canAccessAll($tenant, $featureKeys);
        });

        /**
         * @plan('plan_slug')
         * Check if tenant is on a specific plan
         */
        Blade::if('plan', function (string $planSlug) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant || ! $tenant->getCurrentPlan()) {
                return false;
            }

            return $tenant->getCurrentPlan()->slug === $planSlug;
        });

        /**
         * @planMin('professional')
         * Check if tenant is on this plan or higher
         */
        Blade::if('planMin', function (string $planSlug) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant || ! $tenant->getCurrentPlan()) {
                return false;
            }

            $currentPlan = $tenant->getCurrentPlan();
            $requiredPlan = Plan::where('slug', $planSlug)->first();

            if (! $requiredPlan) {
                return false;
            }

            return $currentPlan->sort_order >= $requiredPlan->sort_order;
        });

        /**
         * @subscribed
         * Check if tenant has active subscription
         */
        Blade::if('subscribed', function () {
            $tenant = $this->getCurrentTenant();

            return $tenant && $tenant->hasActiveSubscription();
        });

        /**
         * @trial
         * Check if tenant is on trial
         */
        Blade::if('trial', function () {
            $tenant = $this->getCurrentTenant();

            return $tenant && $tenant->isOnTrial();
        });

        /**
         * @withinLimit('max_ai_replies')
         * Check if tenant is within a specific limit
         */
        Blade::if('withinLimit', function (string $limitKey, int $additionalUsage = 0) {
            $tenant = $this->getCurrentTenant();

            if (! $tenant) {
                return false;
            }

            $limit = $tenant->getLimit($limitKey);

            if ($limit === -1) {
                return true; // Unlimited
            }

            $usage = UsageSummary::getCurrentForTenant($tenant->id);
            $usageKey = $this->getLimitUsageKey($limitKey);
            $currentUsage = $usage->{$usageKey} ?? 0;

            return ($currentUsage + $additionalUsage) < $limit;
        });

        /**
         * @canAddBranch
         * Check if tenant can add more branches
         */
        Blade::if('canAddBranch', function () {
            $tenant = $this->getCurrentTenant();

            return $tenant && $tenant->canAddBranch();
        });

        /**
         * @canAddCompetitor
         * Check if tenant can add more competitors
         */
        Blade::if('canAddCompetitor', function () {
            $tenant = $this->getCurrentTenant();

            return $tenant && $tenant->canAddCompetitor();
        });

        /**
         * @canUseAiReply
         * Check if tenant can use AI reply
         */
        Blade::if('canUseAiReply', function () {
            $tenant = $this->getCurrentTenant();

            return $tenant && $tenant->canUseAiReply();
        });
    }

    /**
     * Get current tenant from auth
     */
    protected function getCurrentTenant()
    {
        $user = auth()->user();

        if ($user && isset($user->tenant)) {
            return $user->tenant;
        }

        if (function_exists('tenant')) {
            return tenant();
        }

        return null;
    }

    /**
     * Map limit key to usage key
     */
    protected function getLimitUsageKey(string $limitKey): string
    {
        return match ($limitKey) {
            'max_ai_replies' => 'ai_replies_used',
            'max_ai_tokens' => 'ai_tokens_used',
            'max_api_calls' => 'api_calls_used',
            'max_reviews_sync' => 'reviews_synced',
            'max_analysis_runs' => 'analysis_runs',
            default => $limitKey,
        };
    }
}
