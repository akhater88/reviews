<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\AnalysisResult;
use App\Models\AnalysisOverview;
use App\Enums\AnalysisType;
use App\Enums\AnalysisStatus;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PerformanceScoreService
{
    /**
     * Weight configuration for performance score calculation
     */
    protected array $weights = [
        'rating' => 0.40,          // 40% - Average rating
        'volume' => 0.20,          // 20% - Review volume
        'response_rate' => 0.20,   // 20% - Response rate
        'sentiment' => 0.20,       // 20% - Sentiment score
    ];

    /**
     * Get rankings for all branches in a tenant
     */
    public function getRankings(
        int $tenantId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        string $category = 'overall'
    ): Collection {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['reviews' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('review_date', [$startDate, $endDate]);
            }, 'users'])
            ->get();

        $rankings = $branches->map(function ($branch) use ($startDate, $endDate, $category) {
            return $this->calculateBranchScore($branch, $startDate, $endDate, $category);
        });

        // Sort by performance score descending
        $sorted = $rankings->sortByDesc('performance_score')->values();

        // Assign ranks and badges
        return $this->assignRanksAndBadges($sorted);
    }

    /**
     * Calculate performance score for a single branch
     */
    public function calculateBranchScore(
        Branch $branch,
        Carbon $startDate,
        Carbon $endDate,
        string $category = 'overall'
    ): array {
        $reviews = $branch->reviews()
            ->whereBetween('review_date', [$startDate, $endDate])
            ->get();

        $totalReviews = $reviews->count();
        $avgRating = $reviews->avg('rating') ?? 0;
        $repliedCount = $reviews->whereNotNull('owner_reply')->count();
        $responseRate = $totalReviews > 0 ? ($repliedCount / $totalReviews) * 100 : 0;

        // Get sentiment score from analysis results
        $sentimentScore = $this->getSentimentScore($branch->id);

        // Calculate growth compared to previous period
        $periodLength = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($periodLength + 1);
        $previousEnd = $startDate->copy()->subDay();
        $previousReviews = $branch->reviews()
            ->whereBetween('review_date', [$previousStart, $previousEnd])
            ->count();

        $growth = $previousReviews > 0
            ? (($totalReviews - $previousReviews) / $previousReviews) * 100
            : ($totalReviews > 0 ? 100 : 0);

        // Calculate average response time
        $avgResponseTime = $this->calculateAvgResponseTime($branch->id, $startDate, $endDate);

        // Calculate normalized scores (0-100)
        $ratingScore = ($avgRating / 5) * 100;
        $volumeScore = min(($totalReviews / 100) * 100, 100); // Cap at 100 reviews
        $responseScore = $responseRate;
        $sentimentNormalized = (($sentimentScore + 1) / 2) * 100; // Convert -1 to 1 → 0 to 100

        // Apply category filter if needed
        if ($category !== 'overall') {
            $categoryScore = $this->getCategoryScore($branch->id, $category);
            if ($categoryScore !== null) {
                $ratingScore = $categoryScore;
            }
        }

        // Calculate weighted performance score
        $performanceScore =
            ($ratingScore * $this->weights['rating']) +
            ($volumeScore * $this->weights['volume']) +
            ($responseScore * $this->weights['response_rate']) +
            ($sentimentNormalized * $this->weights['sentiment']);

        // Get first assigned user as manager (if any)
        $manager = $branch->users->first();

        return [
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'branch_city' => $branch->city ?? '',
            'branch_country' => $branch->country ?? '',
            'manager_name' => $manager?->name ?? 'غير محدد',
            'manager_email' => $manager?->email ?? '',
            'performance_score' => round($performanceScore, 1),
            'rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
            'response_rate' => round($responseRate, 1),
            'avg_response_time' => $avgResponseTime,
            'growth' => round($growth, 1),
            'sentiment_score' => round($sentimentScore, 2),
            'rank' => 0, // Will be assigned later
            'badges' => [], // Will be assigned later
        ];
    }

    /**
     * Get sentiment score from latest analysis
     */
    protected function getSentimentScore(int $branchId): float
    {
        $analysisOverview = AnalysisOverview::where('branch_id', $branchId)
            ->where('status', AnalysisStatus::COMPLETED)
            ->latest()
            ->first();

        if (!$analysisOverview) {
            return 0;
        }

        $result = $analysisOverview->results()
            ->where('analysis_type', AnalysisType::SENTIMENT->value)
            ->first();

        if (!$result || empty($result->result)) {
            return 0;
        }

        $distribution = $result->result['sentimentDistribution'] ?? [];
        $positive = $distribution['positive'] ?? 0;
        $negative = $distribution['negative'] ?? 0;

        // Calculate sentiment score: -1 (all negative) to 1 (all positive)
        $total = $positive + $negative;
        if ($total === 0) {
            return 0;
        }

        return (($positive - $negative) / $total);
    }

    /**
     * Get category-specific score
     */
    protected function getCategoryScore(int $branchId, string $category): ?float
    {
        $analysisOverview = AnalysisOverview::where('branch_id', $branchId)
            ->where('status', AnalysisStatus::COMPLETED)
            ->latest()
            ->first();

        if (!$analysisOverview) {
            return null;
        }

        $result = $analysisOverview->results()
            ->where('analysis_type', AnalysisType::CATEGORY_INSIGHTS->value)
            ->first();

        if (!$result || empty($result->result['categories'])) {
            return null;
        }

        $categoryMap = [
            'food' => ['الطعام', 'الطعم', 'الأكل', 'food', 'Food'],
            'service' => ['الخدمة', 'المعاملة', 'service', 'Service'],
        ];

        $searchTerms = $categoryMap[$category] ?? [$category];

        foreach ($result->result['categories'] as $cat) {
            $catName = $cat['name'] ?? '';
            if (in_array($catName, $searchTerms)) {
                return ($cat['rating'] ?? 0) * 20; // Convert 5-star to 100
            }
        }

        return null;
    }

    /**
     * Calculate average response time in hours
     */
    protected function calculateAvgResponseTime(int $branchId, Carbon $startDate, Carbon $endDate): ?float
    {
        // This would require storing reply timestamps
        // For now, return a placeholder
        return null;
    }

    /**
     * Assign ranks and achievement badges
     */
    protected function assignRanksAndBadges(Collection $rankings): Collection
    {
        if ($rankings->isEmpty()) {
            return $rankings;
        }

        // Find badge winners
        $highestGrowth = $rankings->sortByDesc('growth')->first();
        $mostReviews = $rankings->sortByDesc('total_reviews')->first();
        $highestRating = $rankings->sortByDesc('rating')->first();
        $fastestResponse = $rankings->sortByDesc('response_rate')->first();

        return $rankings->map(function ($item, $index) use ($highestGrowth, $mostReviews, $highestRating, $fastestResponse) {
            $item['rank'] = $index + 1;
            $badges = [];

            // Best Overall Performance (Rank 1)
            if ($index === 0) {
                $badges[] = [
                    'key' => 'best_overall',
                    'label' => 'أفضل أداء شامل',
                    'icon' => 'trophy',
                    'color' => 'yellow',
                ];
            }

            // Highest Growth
            if ($item['branch_id'] === $highestGrowth['branch_id'] && $item['growth'] > 0) {
                $badges[] = [
                    'key' => 'highest_growth',
                    'label' => 'أعلى نمو',
                    'icon' => 'trending-up',
                    'color' => 'green',
                ];
            }

            // Most Reviews
            if ($item['branch_id'] === $mostReviews['branch_id'] && $item['total_reviews'] > 0) {
                $badges[] = [
                    'key' => 'most_reviews',
                    'label' => 'الأكثر مراجعات',
                    'icon' => 'chat',
                    'color' => 'blue',
                ];
            }

            // Highest Rating
            if ($item['branch_id'] === $highestRating['branch_id'] && $item['rating'] > 0) {
                $badges[] = [
                    'key' => 'highest_rating',
                    'label' => 'أعلى تقييم',
                    'icon' => 'star',
                    'color' => 'purple',
                ];
            }

            // Fastest Response
            if ($item['branch_id'] === $fastestResponse['branch_id'] && $item['response_rate'] > 80) {
                $badges[] = [
                    'key' => 'fastest_response',
                    'label' => 'أسرع استجابة',
                    'icon' => 'bolt',
                    'color' => 'orange',
                ];
            }

            $item['badges'] = $badges;
            return $item;
        });
    }

    /**
     * Get top 3 branches for podium
     */
    public function getTopThree(int $tenantId, ?Carbon $startDate = null, ?Carbon $endDate = null, string $category = 'overall'): array
    {
        $rankings = $this->getRankings($tenantId, $startDate, $endDate, $category);

        return [
            'first' => $rankings->get(0),
            'second' => $rankings->get(1),
            'third' => $rankings->get(2),
        ];
    }
}
