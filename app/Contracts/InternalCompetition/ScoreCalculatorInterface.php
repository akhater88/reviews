<?php

namespace App\Contracts\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use Carbon\Carbon;

interface ScoreCalculatorInterface
{
    /**
     * Calculate score for a specific branch within a competition.
     *
     * @param InternalCompetition $competition
     * @param InternalCompetitionBranch $participant
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return float
     */
    public function calculateScore(
        InternalCompetition $competition,
        InternalCompetitionBranch $participant,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float;

    /**
     * Calculate scores for all participants in a competition.
     *
     * @param InternalCompetition $competition
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return array<int, float> Array of branch_id => score
     */
    public function calculateScoresForCompetition(
        InternalCompetition $competition,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array;

    /**
     * Get the metric type this calculator handles.
     *
     * @return string
     */
    public function getMetricType(): string;
}
