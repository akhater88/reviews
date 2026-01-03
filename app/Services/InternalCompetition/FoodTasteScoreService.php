<?php

namespace App\Services\InternalCompetition;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FoodTasteScoreService implements ScoreCalculatorInterface
{
    /**
     * Calculate food/taste score for a specific branch.
     * Score formula: (positive × 10) - (negative × 5)
     */
    public function calculateScore(
        InternalCompetition $competition,
        InternalCompetitionBranch $participant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $stats = $this->calculateFoodTasteStats(
            $participant->branch_id,
            $periodStart,
            $periodEnd
        );

        return $this->computeScore($stats);
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
            $stats = $this->calculateFoodTasteStats($branchId, $periodStart, $periodEnd);
            $scores[$branchId] = $this->computeScore($stats);
        }

        return $scores;
    }

    /**
     * Get the metric type this calculator handles.
     */
    public function getMetricType(): string
    {
        return CompetitionMetric::FOOD_TASTE->value;
    }

    /**
     * Calculate food/taste statistics for a branch.
     */
    public function calculateFoodTasteStats(
        int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // Query reviews that have 'food' in their categories JSON array
        $stats = Review::query()
            ->where('branch_id', $branchId)
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('categories')
            ->whereRaw('JSON_CONTAINS(categories, \'"food"\')')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN sentiment = "neutral" OR sentiment IS NULL THEN 1 ELSE 0 END) as neutral
            ')
            ->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'positive' => (int) ($stats->positive ?? 0),
            'negative' => (int) ($stats->negative ?? 0),
            'neutral' => (int) ($stats->neutral ?? 0),
        ];
    }

    /**
     * Compute score from statistics.
     * Formula: (positive × 10) - (negative × 5)
     */
    protected function computeScore(array $stats): float
    {
        $positiveScore = $stats['positive'] * 10;
        $negativeScore = $stats['negative'] * 5;

        return max(0, $positiveScore - $negativeScore);
    }

    /**
     * Get top performers by food/taste score.
     */
    public function getTopPerformers(
        InternalCompetition $competition,
        int $limit = 10
    ): array {
        $branchIds = $competition->activeBranches()->pluck('branch_id')->toArray();
        $periodStart = $competition->start_date;
        $periodEnd = min($competition->end_date, now());

        $performers = [];
        foreach ($branchIds as $branchId) {
            $stats = $this->calculateFoodTasteStats($branchId, $periodStart, $periodEnd);
            $performers[$branchId] = [
                'branch_id' => $branchId,
                'score' => $this->computeScore($stats),
                'positive' => $stats['positive'],
                'negative' => $stats['negative'],
                'total' => $stats['total'],
            ];
        }

        // Sort by score descending
        uasort($performers, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Take top performers and add ranking
        $topPerformers = array_slice($performers, 0, $limit, true);
        $rank = 1;
        foreach ($topPerformers as &$performer) {
            $performer['rank'] = $rank++;
        }

        return array_values($topPerformers);
    }
}
