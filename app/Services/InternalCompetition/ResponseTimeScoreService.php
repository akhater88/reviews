<?php

namespace App\Services\InternalCompetition;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\DataTransferObjects\InternalCompetition\ResponseTimeScoreData;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResponseTimeScoreService implements ScoreCalculatorInterface
{
    /**
     * Calculate response time score for a specific branch.
     */
    public function calculateScore(
        InternalCompetition $competition,
        InternalCompetitionBranch $participant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $scoreData = $this->calculateResponseTimeData(
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
            $scoreData = $this->calculateResponseTimeData($branchId, $periodStart, $periodEnd);
            $scores[$branchId] = $scoreData->score;
        }

        return $scores;
    }

    /**
     * Get the metric type this calculator handles.
     */
    public function getMetricType(): string
    {
        return CompetitionMetric::RESPONSE_TIME->value;
    }

    /**
     * Calculate detailed response time data for a branch.
     */
    public function calculateResponseTimeData(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): ResponseTimeScoreData {
        $stats = Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->visible()
            ->selectRaw('
                COUNT(*) as total_reviews,
                SUM(CASE WHEN owner_reply IS NOT NULL AND owner_reply != "" THEN 1 ELSE 0 END) as responded_reviews,
                AVG(
                    CASE
                        WHEN owner_reply_date IS NOT NULL
                        THEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date)
                        ELSE NULL
                    END
                ) as avg_response_hours
            ')
            ->first();

        return ResponseTimeScoreData::fromResponseData(
            branchId: $branchId,
            totalReviews: (int) ($stats->total_reviews ?? 0),
            respondedReviews: (int) ($stats->responded_reviews ?? 0),
            averageResponseTimeHours: (float) ($stats->avg_response_hours ?? 0)
        );
    }

    /**
     * Get response time distribution for a branch.
     */
    public function getResponseTimeDistribution(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        return Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('owner_reply_date')
            ->visible()
            ->selectRaw('
                CASE
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 1 THEN "under_1_hour"
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 4 THEN "1_to_4_hours"
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 12 THEN "4_to_12_hours"
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 24 THEN "12_to_24_hours"
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 48 THEN "24_to_48_hours"
                    WHEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date) < 72 THEN "48_to_72_hours"
                    ELSE "over_72_hours"
                END as time_bucket,
                COUNT(*) as count
            ')
            ->groupBy('time_bucket')
            ->pluck('count', 'time_bucket')
            ->toArray();
    }

    /**
     * Calculate response time trend over time.
     */
    public function calculateResponseTimeTrend(
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
                SUM(CASE WHEN owner_reply IS NOT NULL THEN 1 ELSE 0 END) as responded_reviews,
                AVG(
                    CASE
                        WHEN owner_reply_date IS NOT NULL
                        THEN TIMESTAMPDIFF(HOUR, review_date, owner_reply_date)
                        ELSE NULL
                    END
                ) as avg_response_hours
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                $responseRate = $row->total_reviews > 0
                    ? ($row->responded_reviews / $row->total_reviews) * 100
                    : 0;

                return [
                    'period' => $row->period,
                    'total_reviews' => $row->total_reviews,
                    'responded_reviews' => $row->responded_reviews,
                    'response_rate' => round($responseRate, 2),
                    'avg_response_hours' => round($row->avg_response_hours ?? 0, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Compare response times between branches.
     */
    public function compareBranches(
        array $branchIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $comparison = [];

        foreach ($branchIds as $branchId) {
            $scoreData = $this->calculateResponseTimeData($branchId, $periodStart, $periodEnd);
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

    /**
     * Get unreplied reviews for a branch.
     */
    public function getUnrepliedReviews(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $limit = 10
    ): array {
        return Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->visible()
            ->unreplied()
            ->orderBy('review_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'reviewer_name' => $review->reviewer_name,
                    'rating' => $review->rating,
                    'review_date' => $review->review_date->toDateTimeString(),
                    'hours_waiting' => $review->review_date->diffInHours(now()),
                ];
            })
            ->toArray();
    }
}
