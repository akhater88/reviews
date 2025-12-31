<?php

namespace App\Services\InternalCompetition;

use App\Contracts\InternalCompetition\ScoreCalculatorInterface;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ScoreCalculationService
{
    /**
     * @var array<string, ScoreCalculatorInterface>
     */
    private array $calculators = [];

    public function __construct(
        private readonly CustomerSatisfactionScoreService $satisfactionScoreService,
        private readonly ResponseTimeScoreService $responseTimeScoreService,
        private readonly EmployeeExtractionService $employeeService
    ) {
        $this->registerCalculator($this->satisfactionScoreService);
        $this->registerCalculator($this->responseTimeScoreService);
        $this->registerCalculator($this->employeeService);
    }

    /**
     * Register a score calculator for a specific metric.
     */
    public function registerCalculator(ScoreCalculatorInterface $calculator): void
    {
        $this->calculators[$calculator->getMetricType()] = $calculator;
    }

    /**
     * Get calculator for a specific metric.
     */
    public function getCalculator(CompetitionMetric $metric): ScoreCalculatorInterface
    {
        if (!isset($this->calculators[$metric->value])) {
            throw new InvalidArgumentException("No calculator registered for metric: {$metric->value}");
        }

        return $this->calculators[$metric->value];
    }

    /**
     * Calculate and store scores for a competition.
     */
    public function calculateCompetitionScores(
        InternalCompetition $competition,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null
    ): void {
        $periodStart = $periodStart ?? $competition->start_date;
        $periodEnd = $periodEnd ?? min($competition->end_date, now());

        $enabledMetrics = $competition->enabled_metrics;

        foreach ($enabledMetrics as $metric) {
            if ($metric === CompetitionMetric::EMPLOYEE_MENTIONS) {
                // Employee mentions use a different storage mechanism
                $this->employeeService->calculateForAllBranches(
                    $competition,
                    $periodStart,
                    $periodEnd
                );
                continue;
            }

            $this->calculateAndStoreMetricScores(
                $competition,
                $metric,
                $periodStart,
                $periodEnd
            );
        }
    }

    /**
     * Calculate all scores for a competition and return summary.
     */
    public function calculateAllScores(InternalCompetition $competition): array
    {
        $results = [
            'satisfaction' => null,
            'response_time' => null,
            'employee_mentions' => null,
            'total_branches' => 0,
            'calculated_at' => now(),
        ];

        $periodStart = $competition->start_date;
        $periodEnd = min($competition->end_date, now());

        // Calculate satisfaction scores if enabled
        if ($competition->isMetricEnabled(CompetitionMetric::CUSTOMER_SATISFACTION)) {
            $this->calculateAndStoreMetricScores(
                $competition,
                CompetitionMetric::CUSTOMER_SATISFACTION,
                $periodStart,
                $periodEnd
            );
            $results['satisfaction'] = [
                'calculated' => true,
                'statistics' => $this->getMetricStatistics($competition, CompetitionMetric::CUSTOMER_SATISFACTION),
            ];
        }

        // Calculate response time scores if enabled
        if ($competition->isMetricEnabled(CompetitionMetric::RESPONSE_TIME)) {
            $this->calculateAndStoreMetricScores(
                $competition,
                CompetitionMetric::RESPONSE_TIME,
                $periodStart,
                $periodEnd
            );
            $results['response_time'] = [
                'calculated' => true,
                'statistics' => $this->getMetricStatistics($competition, CompetitionMetric::RESPONSE_TIME),
            ];
        }

        // Extract and calculate employee mentions if enabled
        if ($competition->isMetricEnabled(CompetitionMetric::EMPLOYEE_MENTIONS)) {
            $employees = $this->employeeService->calculateForAllBranches(
                $competition,
                $periodStart,
                $periodEnd
            );
            $results['employee_mentions'] = [
                'count' => $employees->count(),
                'statistics' => $this->employeeService->getScoreStatistics($competition),
            ];
        }

        $results['total_branches'] = $competition->activeBranches()->count();

        Log::info('All scores calculated for competition', [
            'competition_id' => $competition->id,
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * Finalize all scores at competition end.
     */
    public function finalizeAllScores(InternalCompetition $competition): void
    {
        $this->calculateAllScores($competition);

        // Mark branch scores as final
        InternalCompetitionBranchScore::where('competition_id', $competition->id)
            ->update(['is_final' => true]);

        // Finalize employee scores
        if ($competition->isMetricEnabled(CompetitionMetric::EMPLOYEE_MENTIONS)) {
            $this->employeeService->finalizeScores($competition);
        }

        Log::info('All scores finalized for competition', [
            'competition_id' => $competition->id,
        ]);
    }

    /**
     * Get top performers across all metrics.
     */
    public function getTopPerformers(InternalCompetition $competition, int $limit = 3): array
    {
        $topPerformers = [];

        if ($competition->isMetricEnabled(CompetitionMetric::CUSTOMER_SATISFACTION)) {
            $topPerformers['satisfaction'] = $this->getLeaderboard(
                $competition,
                CompetitionMetric::CUSTOMER_SATISFACTION,
                $limit
            );
        }

        if ($competition->isMetricEnabled(CompetitionMetric::RESPONSE_TIME)) {
            $topPerformers['response_time'] = $this->getLeaderboard(
                $competition,
                CompetitionMetric::RESPONSE_TIME,
                $limit
            );
        }

        if ($competition->isMetricEnabled(CompetitionMetric::EMPLOYEE_MENTIONS)) {
            $topPerformers['employee_mentions'] = $this->employeeService->getTopPerformers($competition, $limit);
        }

        return $topPerformers;
    }

    /**
     * Get service for a specific metric.
     */
    public function getServiceForMetric(CompetitionMetric $metric): ?object
    {
        return match ($metric) {
            CompetitionMetric::CUSTOMER_SATISFACTION => $this->satisfactionScoreService,
            CompetitionMetric::RESPONSE_TIME => $this->responseTimeScoreService,
            CompetitionMetric::EMPLOYEE_MENTIONS => $this->employeeService,
        };
    }

    /**
     * Get statistics for a specific metric.
     */
    protected function getMetricStatistics(InternalCompetition $competition, CompetitionMetric $metric): array
    {
        $scores = InternalCompetitionBranchScore::query()
            ->where('competition_id', $competition->id)
            ->where('metric_type', $metric->value)
            ->get();

        if ($scores->isEmpty()) {
            return [
                'count' => 0,
                'average_score' => 0,
                'min_score' => 0,
                'max_score' => 0,
            ];
        }

        return [
            'count' => $scores->count(),
            'average_score' => round($scores->avg('score') ?? 0, 2),
            'min_score' => round($scores->min('score') ?? 0, 2),
            'max_score' => round($scores->max('score') ?? 0, 2),
        ];
    }

    /**
     * Calculate and store scores for a specific metric.
     */
    public function calculateAndStoreMetricScores(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        Carbon $periodStart,
        Carbon $periodEnd
    ): void {
        $calculator = $this->getCalculator($metric);
        $scores = $calculator->calculateScoresForCompetition($competition, $periodStart, $periodEnd);

        DB::transaction(function () use ($competition, $metric, $scores, $periodStart, $periodEnd) {
            foreach ($scores as $branchId => $score) {
                $participant = $competition->activeBranches()
                    ->where('branch_id', $branchId)
                    ->first();

                if (!$participant) {
                    continue;
                }

                InternalCompetitionBranchScore::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'tenant_id' => $participant->tenant_id,
                        'branch_id' => $branchId,
                        'metric_type' => $metric->value,
                    ],
                    [
                        'score' => $score,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'calculated_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Calculate weighted total score for a branch.
     */
    public function calculateWeightedTotalScore(
        InternalCompetition $competition,
        int $branchId
    ): float {
        $scores = InternalCompetitionBranchScore::query()
            ->where('competition_id', $competition->id)
            ->where('branch_id', $branchId)
            ->get();

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $score) {
            $metric = CompetitionMetric::from($score->metric_type);
            $weight = $competition->getMetricWeight($metric);

            $weightedSum += $score->score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Get leaderboard for a competition.
     */
    public function getLeaderboard(
        InternalCompetition $competition,
        ?CompetitionMetric $metric = null,
        int $limit = 10
    ): Collection {
        if ($metric) {
            return $this->getMetricLeaderboard($competition, $metric, $limit);
        }

        return $this->getOverallLeaderboard($competition, $limit);
    }

    /**
     * Get leaderboard for a specific metric.
     */
    private function getMetricLeaderboard(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        int $limit
    ): Collection {
        return InternalCompetitionBranchScore::query()
            ->where('competition_id', $competition->id)
            ->where('metric_type', $metric->value)
            ->with(['branch', 'tenant'])
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(function ($score, $index) {
                return [
                    'rank' => $index + 1,
                    'branch_id' => $score->branch_id,
                    'branch_name' => $score->branch?->name,
                    'tenant_id' => $score->tenant_id,
                    'tenant_name' => $score->tenant?->name,
                    'score' => round($score->score, 2),
                    'metric' => $score->metric_type,
                ];
            });
    }

    /**
     * Get overall leaderboard with weighted scores.
     */
    private function getOverallLeaderboard(
        InternalCompetition $competition,
        int $limit
    ): Collection {
        $branchIds = $competition->activeBranches()->pluck('branch_id')->unique();

        $leaderboard = $branchIds->map(function ($branchId) use ($competition) {
            $participant = $competition->activeBranches()
                ->where('branch_id', $branchId)
                ->with(['branch', 'tenant'])
                ->first();

            return [
                'branch_id' => $branchId,
                'branch_name' => $participant?->branch?->name,
                'tenant_id' => $participant?->tenant_id,
                'tenant_name' => $participant?->tenant?->name,
                'score' => $this->calculateWeightedTotalScore($competition, $branchId),
            ];
        });

        return $leaderboard
            ->sortByDesc('score')
            ->values()
            ->take($limit)
            ->map(function ($entry, $index) {
                $entry['rank'] = $index + 1;
                $entry['score'] = round($entry['score'], 2);
                return $entry;
            });
    }

    /**
     * Get branch position in leaderboard.
     */
    public function getBranchPosition(
        InternalCompetition $competition,
        int $branchId,
        ?CompetitionMetric $metric = null
    ): ?int {
        $leaderboard = $this->getLeaderboard($competition, $metric, PHP_INT_MAX);

        $position = $leaderboard->search(fn ($entry) => $entry['branch_id'] === $branchId);

        return $position !== false ? $position + 1 : null;
    }

    /**
     * Get score summary for a branch.
     */
    public function getBranchScoreSummary(
        InternalCompetition $competition,
        int $branchId
    ): array {
        $scores = InternalCompetitionBranchScore::query()
            ->where('competition_id', $competition->id)
            ->where('branch_id', $branchId)
            ->get();

        $summary = [
            'branch_id' => $branchId,
            'overall_score' => $this->calculateWeightedTotalScore($competition, $branchId),
            'overall_rank' => $this->getBranchPosition($competition, $branchId),
            'metrics' => [],
        ];

        foreach ($scores as $score) {
            $metric = CompetitionMetric::from($score->metric_type);
            $summary['metrics'][$score->metric_type] = [
                'score' => round($score->score, 2),
                'rank' => $this->getBranchPosition($competition, $branchId, $metric),
                'weight' => $competition->getMetricWeight($metric),
                'calculated_at' => $score->calculated_at?->toDateTimeString(),
            ];
        }

        return $summary;
    }

    /**
     * Recalculate all scores for active competitions.
     */
    public function recalculateActiveCompetitions(): int
    {
        $count = 0;

        $competitions = InternalCompetition::query()
            ->where('status', CompetitionStatus::ACTIVE)
            ->get();

        foreach ($competitions as $competition) {
            $this->calculateCompetitionScores($competition);
            $count++;
        }

        return $count;
    }

    /**
     * Get competition statistics.
     */
    public function getCompetitionStatistics(InternalCompetition $competition): array
    {
        $branches = $competition->activeBranches()->count();
        $scores = InternalCompetitionBranchScore::query()
            ->where('competition_id', $competition->id)
            ->get();

        $stats = [
            'total_participants' => $branches,
            'metrics' => [],
        ];

        foreach ($competition->enabled_metrics as $metric) {
            if ($metric === CompetitionMetric::EMPLOYEE_MENTIONS) {
                continue;
            }

            $metricScores = $scores->where('metric_type', $metric->value);

            $stats['metrics'][$metric->value] = [
                'participants_scored' => $metricScores->count(),
                'average_score' => round($metricScores->avg('score') ?? 0, 2),
                'highest_score' => round($metricScores->max('score') ?? 0, 2),
                'lowest_score' => round($metricScores->min('score') ?? 0, 2),
            ];
        }

        return $stats;
    }
}
