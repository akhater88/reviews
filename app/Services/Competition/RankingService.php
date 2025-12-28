<?php

namespace App\Services\Competition;

use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RankingService
{
    /**
     * Update rankings for a period
     */
    public function updateRankings(CompetitionPeriod $period): int
    {
        $scores = CompetitionScore::where('competition_period_id', $period->id)
            ->where('analysis_status', 'completed')
            ->orderByDesc('competition_score')
            ->get();

        if ($scores->isEmpty()) {
            return 0;
        }

        $rank = 0;
        $previousScore = null;
        $sameRankCount = 0;
        $updated = 0;

        DB::beginTransaction();

        try {
            foreach ($scores as $score) {
                // Handle ties - same score = same rank
                if ($previousScore !== null && $score->competition_score === $previousScore) {
                    $sameRankCount++;
                } else {
                    $rank += $sameRankCount + 1;
                    $sameRankCount = 0;
                }

                $previousRank = $score->rank_position;

                $score->update([
                    'rank_position' => $rank,
                    'previous_rank' => $previousRank,
                    'rank_change' => $previousRank ? ($previousRank - $rank) : null,
                ]);

                $previousScore = $score->competition_score;
                $updated++;
            }

            // Update period stats
            $period->update([
                'total_branches' => $scores->count(),
                'total_nominations' => $scores->sum('nomination_count'),
            ]);

            DB::commit();

            Log::info('Rankings updated', [
                'period_id' => $period->id,
                'branches_ranked' => $updated,
            ]);

            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ranking update failed', [
                'period_id' => $period->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get leaderboard for a period
     */
    public function getLeaderboard(CompetitionPeriod $period, int $limit = 10): array
    {
        return CompetitionScore::where('competition_period_id', $period->id)
            ->whereNotNull('rank_position')
            ->with('competitionBranch')
            ->orderBy('rank_position')
            ->limit($limit)
            ->get()
            ->map(function ($score) {
                return [
                    'rank' => $score->rank_position,
                    'previous_rank' => $score->previous_rank,
                    'rank_change' => $score->rank_change,
                    'branch' => [
                        'id' => $score->competitionBranch->id,
                        'name' => $score->competitionBranch->name,
                        'city' => $score->competitionBranch->city,
                        'photo_url' => $score->competitionBranch->photo_url,
                        'rating' => $score->competitionBranch->google_rating,
                    ],
                    'score' => round($score->competition_score, 2),
                    'breakdown' => [
                        'rating' => $score->rating_score ?? 0,
                        'sentiment' => $score->sentiment_score,
                        'response_rate' => $score->response_rate,
                        'volume' => $score->review_volume_score,
                        'trend' => $score->trend_score,
                        'keywords' => $score->keyword_score,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get top winners for a completed period
     */
    public function getTopWinners(CompetitionPeriod $period, int $count = 3): array
    {
        return CompetitionScore::where('competition_period_id', $period->id)
            ->whereNotNull('rank_position')
            ->where('rank_position', '<=', $count)
            ->with(['competitionBranch', 'competitionBranch.nominations' => function ($q) use ($period) {
                $q->where('competition_period_id', $period->id)->with('participant');
            }])
            ->orderBy('rank_position')
            ->get()
            ->map(function ($score) use ($period) {
                $nominations = $score->competitionBranch->nominations
                    ->where('competition_period_id', $period->id);

                return [
                    'rank' => $score->rank_position,
                    'branch' => $score->competitionBranch,
                    'score' => $score->competition_score,
                    'nominators' => $nominations->map(fn ($n) => $n->participant),
                ];
            })
            ->toArray();
    }

    /**
     * Get branch rank for a specific period
     */
    public function getBranchRank(int $branchId, CompetitionPeriod $period): ?array
    {
        $score = CompetitionScore::where('competition_period_id', $period->id)
            ->where('competition_branch_id', $branchId)
            ->first();

        if (!$score) {
            return null;
        }

        $totalBranches = CompetitionScore::where('competition_period_id', $period->id)
            ->whereNotNull('rank_position')
            ->count();

        return [
            'rank' => $score->rank_position,
            'total' => $totalBranches,
            'score' => $score->competition_score,
            'previous_rank' => $score->previous_rank,
            'rank_change' => $score->rank_change,
        ];
    }
}
