<?php

namespace App\Traits;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\TenantOverride;
use App\Models\UsageSummary;
use App\Services\FeatureGateService;

trait HasSubscription
{
    /**
     * Check if tenant can access features (includes past_due)
     */
    public function canAccessFeatures(): bool
    {
        $subscription = $this->currentSubscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->canAccessFeatures();
    }

    /**
     * Check if tenant is in grace period
     */
    public function isInGracePeriod(): bool
    {
        return $this->currentSubscription?->isInGracePeriod() ?? false;
    }

    /**
     * Check if subscription is expiring soon
     */
    public function isSubscriptionExpiringSoon(int $days = 7): bool
    {
        return $this->currentSubscription?->isExpiringSoon($days) ?? false;
    }

    /**
     * Get days until subscription expires
     */
    public function daysUntilExpiry(): int
    {
        return $this->currentSubscription?->daysUntilExpiry() ?? 0;
    }

    /**
     * Check if tenant can use a feature (alias for hasFeature with subscription check)
     */
    public function canUseFeature(string $featureKey): bool
    {
        if (! $this->canAccessFeatures()) {
            return false;
        }

        return $this->hasFeature($featureKey);
    }

    /**
     * Check if tenant can add more branches
     */
    public function canAddBranch(): bool
    {
        $limit = $this->getLimit('max_branches');

        if ($limit === -1) {
            return true;
        }

        $currentCount = $this->branches()->where('is_competitor', false)->count();

        return $currentCount < $limit;
    }

    /**
     * Check if tenant can add more competitor branches
     */
    public function canAddCompetitor(): bool
    {
        $limit = $this->getLimit('max_competitors');

        if ($limit === -1) {
            return true;
        }

        $currentCount = $this->branches()->where('is_competitor', true)->count();

        return $currentCount < $limit;
    }

    /**
     * Check if tenant can add more users
     */
    public function canAddUser(): bool
    {
        $limit = $this->getLimit('max_users');

        if ($limit === -1) {
            return true;
        }

        $currentCount = $this->users()->count();

        return $currentCount < $limit;
    }

    /**
     * Check if tenant can use AI reply (within monthly limit)
     */
    public function canUseAiReply(): bool
    {
        if (! $this->hasFeature('ai_reply')) {
            return false;
        }

        $limit = $this->getLimit('max_ai_replies');

        if ($limit === -1) {
            return true;
        }

        $usage = UsageSummary::getCurrentForTenant($this->id);

        return $usage->ai_replies_used < $limit;
    }

    /**
     * Get remaining AI replies for this month
     */
    public function getRemainingAiReplies(): int
    {
        $limit = $this->getLimit('max_ai_replies');

        if ($limit === -1) {
            return PHP_INT_MAX; // Essentially unlimited
        }

        $usage = UsageSummary::getCurrentForTenant($this->id);

        return max(0, $limit - $usage->ai_replies_used);
    }

    /**
     * Get usage percentage for a specific metric
     */
    public function getUsagePercentage(string $usageKey, string $limitKey): float
    {
        $limit = $this->getLimit($limitKey);

        if ($limit === -1) {
            return 0; // Unlimited
        }

        $usage = UsageSummary::getCurrentForTenant($this->id);
        $currentUsage = $usage->{$usageKey} ?? 0;

        return $limit > 0 ? round(($currentUsage / $limit) * 100, 1) : 0;
    }

    /**
     * Check if tenant is approaching a usage limit (>80%)
     */
    public function isApproachingLimit(string $usageKey, string $limitKey): bool
    {
        return $this->getUsagePercentage($usageKey, $limitKey) >= 80;
    }

    /**
     * Get feature override if exists
     */
    public function getFeatureOverride(string $featureKey): ?TenantOverride
    {
        return $this->overrides()
            ->valid()
            ->features()
            ->where('key', $featureKey)
            ->first();
    }

    /**
     * Get limit override if exists
     */
    public function getLimitOverride(string $limitKey): ?TenantOverride
    {
        return $this->overrides()
            ->valid()
            ->limits()
            ->where('key', $limitKey)
            ->first();
    }

    /**
     * Get subscription status label for display
     */
    public function getSubscriptionStatusLabel(): string
    {
        if (! $this->currentSubscription) {
            return 'غير مشترك';
        }

        return $this->currentSubscription->status->label();
    }

    /**
     * Get subscription status color for display
     */
    public function getSubscriptionStatusColor(): string
    {
        if (! $this->currentSubscription) {
            return 'gray';
        }

        return $this->currentSubscription->status->color();
    }

    /**
     * Get all available features for this tenant
     */
    public function getAvailableFeatures(): array
    {
        return app(FeatureGateService::class)->getAvailableFeatures($this);
    }

    /**
     * Get all limits for this tenant
     */
    public function getAllLimits(): array
    {
        return app(FeatureGateService::class)->getAllLimits($this);
    }

    /**
     * Get usage summary for dashboard
     */
    public function getUsageSummary(): array
    {
        $usage = UsageSummary::getCurrentForTenant($this->id);
        $limits = $this->getAllLimits();

        return [
            'ai_replies' => [
                'used' => $usage->ai_replies_used,
                'limit' => $limits['max_ai_replies'],
                'percentage' => $this->getUsagePercentage('ai_replies_used', 'max_ai_replies'),
                'unlimited' => $limits['max_ai_replies'] === -1,
            ],
            'branches' => [
                'used' => $this->branches()->where('is_competitor', false)->count(),
                'limit' => $limits['max_branches'],
                'unlimited' => $limits['max_branches'] === -1,
            ],
            'competitors' => [
                'used' => $this->branches()->where('is_competitor', true)->count(),
                'limit' => $limits['max_competitors'],
                'unlimited' => $limits['max_competitors'] === -1,
            ],
            'users' => [
                'used' => $this->users()->count(),
                'limit' => $limits['max_users'],
                'unlimited' => $limits['max_users'] === -1,
            ],
            'reviews_synced' => [
                'used' => $usage->reviews_synced,
                'limit' => $limits['max_reviews_sync'],
                'unlimited' => $limits['max_reviews_sync'] === -1,
            ],
        ];
    }
}
