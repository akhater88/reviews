<?php

namespace App\Services\InternalCompetition;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\DataTransferObjects\InternalCompetition\SatisfactionScoreData;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerSatisfactionScoreService implements ScoreCalculatorInterface
{
    /**
     * Calculate customer satisfaction score for a specific branch.
     */
    public function calculateScore(
        InternalCompetition $competition,
        InternalCompetitionBranch $participant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $scoreData = $this->calculateSatisfactionData(
            $participant->branch_id,
            $periodStart,
            $periodEnd
        );

        return $scoreData->score;
    }

    /**
     * Calculate scores for all participants in a competition.
     */
    public function calculateScoresForCompetition(
        InternalCompetition $competition,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $branchIds = $competition->activeBranches()->pluck('branch_id')->toArray();

        $scores = [];
        foreach ($branchIds as $branchId) {
            $scoreData = $this->calculateSatisfactionData($branchId, $periodStart, $periodEnd);
            $scores[$branchId] = $scoreData->score;
        }

        return $scores;
    }

    /**
     * Get the metric type this calculator handles.
     */
    public function getMetricType(): string
    {
        return CompetitionMetric::CUSTOMER_SATISFACTION->value;
    }

    /**
     * Calculate detailed satisfaction data for a branch.
     */
    public function calculateSatisfactionData(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): SatisfactionScoreData {
        $stats = Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->visible()
            ->selectRaw('
                COUNT(*) as total_reviews,
                SUM(CASE WHEN sentiment = "positive" OR rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                SUM(CASE WHEN sentiment = "negative" OR rating <= 2 THEN 1 ELSE 0 END) as negative_reviews,
                SUM(CASE WHEN sentiment = "neutral" OR rating = 3 THEN 1 ELSE 0 END) as neutral_reviews,
                AVG(rating) as average_rating
            ')
            ->first();

        return SatisfactionScoreData::fromReviewData(
            branchId: $branchId,
            totalReviews: (int) ($stats->total_reviews ?? 0),
            positiveReviews: (int) ($stats->positive_reviews ?? 0),
            negativeReviews: (int) ($stats->negative_reviews ?? 0),
            neutralReviews: (int) ($stats->neutral_reviews ?? 0),
            averageRating: (float) ($stats->average_rating ?? 0)
        );
    }

    /**
     * Get rating distribution for a branch.
     */
    public function getRatingDistribution(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        return Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->visible()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();
    }

    /**
     * Calculate satisfaction trend over time.
     */
    public function calculateSatisfactionTrend(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $interval = 'day'
    ): array {
        $dateFormat = match ($interval) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->visible()
            ->selectRaw("
                DATE_FORMAT(review_date, '{$dateFormat}') as period,
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative_count
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                $satisfactionRate = $row->total_reviews > 0
                    ? ($row->positive_count / $row->total_reviews) * 100
                    : 0;

                return [
                    'period' => $row->period,
                    'total_reviews' => $row->total_reviews,
                    'average_rating' => round($row->average_rating, 2),
                    'satisfaction_rate' => round($satisfactionRate, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Compare satisfaction scores between branches.
     */
    public function compareBranches(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $comparison = [];

        foreach ($branchIds as $branchId) {
            $scoreData = $this->calculateSatisfactionData($branchId, $periodStart, $periodEnd);
            $comparison[$branchId] = $scoreData->toArray();
        }

        // Sort by score descending
        uasort($comparison, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Add ranking
        $rank = 1;
        foreach ($comparison as $branchId => &$data) {
            $data['rank'] = $rank++;
        }

        return $comparison;
    }
}
