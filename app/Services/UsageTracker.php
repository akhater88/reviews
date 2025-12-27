<?php

namespace App\Services;

use App\Enums\UsageType;
use App\Models\Tenant;
use App\Models\UsageRecord;
use App\Models\UsageSummary;
use Illuminate\Support\Facades\DB;

class UsageTracker
{
    /**
     * Record AI reply usage
     */
    public function recordAiReply(Tenant $tenant, int $tokensUsed = 0, array $metadata = []): void
    {
        DB::transaction(function () use ($tenant, $tokensUsed, $metadata) {
            // Record detailed usage
            UsageRecord::record(
                $tenant->id,
                UsageType::AI_REPLY,
                1,
                array_merge($metadata, ['tokens' => $tokensUsed])
            );

            // Update summary
            $summary = UsageSummary::getCurrentForTenant($tenant->id);
            $summary->incrementUsage('ai_replies_used');

            if ($tokensUsed > 0) {
                $summary->incrementUsage('ai_tokens_used', $tokensUsed);
            }
        });
    }

    /**
     * Record API call usage
     */
    public function recordApiCall(Tenant $tenant, string $endpoint, array $metadata = []): void
    {
        DB::transaction(function () use ($tenant, $endpoint, $metadata) {
            UsageRecord::record(
                $tenant->id,
                UsageType::API_CALL,
                1,
                array_merge($metadata, ['endpoint' => $endpoint])
            );

            $summary = UsageSummary::getCurrentForTenant($tenant->id);
            $summary->incrementUsage('api_calls_used');
        });
    }

    /**
     * Record review sync usage
     */
    public function recordReviewSync(Tenant $tenant, int $reviewCount, array $metadata = []): void
    {
        DB::transaction(function () use ($tenant, $reviewCount, $metadata) {
            UsageRecord::record(
                $tenant->id,
                UsageType::REVIEW_SYNC,
                $reviewCount,
                $metadata
            );

            $summary = UsageSummary::getCurrentForTenant($tenant->id);
            $summary->incrementUsage('reviews_synced', $reviewCount);
        });
    }

    /**
     * Record analysis run usage
     */
    public function recordAnalysisRun(Tenant $tenant, string $analysisType, array $metadata = []): void
    {
        DB::transaction(function () use ($tenant, $analysisType, $metadata) {
            UsageRecord::record(
                $tenant->id,
                UsageType::ANALYSIS_RUN,
                1,
                array_merge($metadata, ['type' => $analysisType])
            );

            $summary = UsageSummary::getCurrentForTenant($tenant->id);
            $summary->incrementUsage('analysis_runs');
        });
    }

    /**
     * Check if tenant can use AI reply
     */
    public function canUseAiReply(Tenant $tenant): bool
    {
        if (! $tenant->hasFeature('ai_reply')) {
            return false;
        }

        $limit = $tenant->getLimit('max_ai_replies');

        if ($limit === -1) {
            return true;
        }

        $summary = UsageSummary::getCurrentForTenant($tenant->id);

        return $summary->ai_replies_used < $limit;
    }

    /**
     * Check if tenant can sync reviews
     */
    public function canSyncReviews(Tenant $tenant, int $count = 1): bool
    {
        $limit = $tenant->getLimit('max_reviews_sync');

        if ($limit === -1) {
            return true;
        }

        $summary = UsageSummary::getCurrentForTenant($tenant->id);

        return ($summary->reviews_synced + $count) <= $limit;
    }

    /**
     * Check if tenant can run analysis
     */
    public function canRunAnalysis(Tenant $tenant): bool
    {
        $limit = $tenant->getLimit('max_analysis_runs');

        if ($limit === -1) {
            return true;
        }

        $summary = UsageSummary::getCurrentForTenant($tenant->id);

        return $summary->analysis_runs < $limit;
    }

    /**
     * Get remaining usage for a specific metric
     */
    public function getRemainingUsage(Tenant $tenant, string $limitKey): int
    {
        $limit = $tenant->getLimit($limitKey);

        if ($limit === -1) {
            return PHP_INT_MAX;
        }

        $summary = UsageSummary::getCurrentForTenant($tenant->id);
        $usageKey = $this->limitToUsageKey($limitKey);
        $currentUsage = $summary->{$usageKey} ?? 0;

        return max(0, $limit - $currentUsage);
    }

    /**
     * Map limit key to usage summary key
     */
    protected function limitToUsageKey(string $limitKey): string
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
