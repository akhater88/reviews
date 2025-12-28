<?php

namespace App\Services\Competition;

use App\Jobs\Competition\NotifyWinnerJob;
use App\Models\Competition\CompetitionNomination;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use App\Models\Competition\CompetitionWinner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WinnerSelectionService
{
    /**
     * Select all winners for a completed period
     */
    public function selectWinners(CompetitionPeriod $period): array
    {
        if ($period->status->value !== 'completed') {
            throw new \InvalidArgumentException('Period must be completed to select winners');
        }

        if ($period->winners_selected) {
            throw new \InvalidArgumentException('Winners already selected for this period');
        }

        return DB::transaction(function () use ($period) {
            $results = [
                'branch_winners' => [],
                'nominator_winners' => [],
                'total_prize_amount' => 0,
            ];

            // 1. Select top 3 branch winners
            $branchWinners = $this->selectBranchWinners($period);
            $results['branch_winners'] = $branchWinners;

            // 2. Select random nominator winners via lottery
            $nominatorWinners = $this->selectNominatorWinners($period);
            $results['nominator_winners'] = $nominatorWinners;

            // 3. Calculate total prize amount
            $results['total_prize_amount'] = collect($branchWinners)
                ->merge($nominatorWinners)
                ->sum('prize_amount');

            // 4. Mark period as winners selected
            $period->update([
                'winners_selected' => true,
                'winners_selected_at' => now(),
                'winners_announced' => false,
            ]);

            // 5. Dispatch notification jobs
            $this->dispatchWinnerNotifications($branchWinners, $nominatorWinners);

            Log::info('Competition winners selected', [
                'period_id' => $period->id,
                'branch_winners' => count($branchWinners),
                'nominator_winners' => count($nominatorWinners),
                'total_prize' => $results['total_prize_amount'],
            ]);

            return $results;
        });
    }

    /**
     * Select top 3 branch winners based on ranking
     */
    protected function selectBranchWinners(CompetitionPeriod $period): array
    {
        $topScores = CompetitionScore::where('competition_period_id', $period->id)
            ->whereNotNull('rank_position')
            ->where('rank_position', '<=', 3)
            ->with(['competitionBranch', 'competitionBranch.nominations' => function ($q) use ($period) {
                $q->where('competition_period_id', $period->id)
                    ->where('is_valid', true)
                    ->with('participant');
            }])
            ->orderBy('rank_position')
            ->get();

        $winners = [];
        $prizeAmounts = [
            1 => $period->first_prize ?? 2000,
            2 => $period->second_prize ?? 1500,
            3 => $period->third_prize ?? 1000,
        ];

        foreach ($topScores as $score) {
            $rank = $score->rank_position;
            $prizeAmount = $prizeAmounts[$rank] ?? 0;

            // Create winner record for the branch
            $winner = CompetitionWinner::create([
                'competition_period_id' => $period->id,
                'competition_branch_id' => $score->competition_branch_id,
                'competition_score_id' => $score->id,
                'winner_type' => 'branch',
                'prize_rank' => $rank,
                'prize_amount' => $prizeAmount,
                'competition_score' => $score->competition_score,
                'selected_at' => now(),
                'claim_code' => $this->generateClaimCode(),
            ]);

            // Get nominations for this branch
            $nominations = CompetitionNomination::where('competition_period_id', $period->id)
                ->where('competition_branch_id', $score->competition_branch_id)
                ->where('is_valid', true)
                ->with('participant')
                ->get();

            // Mark all nominations for this branch as winners (branch_nominator type - no prize, just recognition)
            foreach ($nominations as $nomination) {
                $nomination->update([
                    'is_winner' => true,
                    'winner_type' => 'branch_nominator',
                    'won_at' => now(),
                ]);
            }

            $winners[] = [
                'winner' => $winner,
                'branch' => $score->competitionBranch,
                'score' => $score,
                'rank' => $rank,
                'prize_amount' => $prizeAmount,
                'nominators' => $nominations->pluck('participant'),
            ];
        }

        return $winners;
    }

    /**
     * Select random nominator winners via lottery
     */
    protected function selectNominatorWinners(CompetitionPeriod $period): array
    {
        $winnersCount = $period->nominator_winners_count ?? 5;
        $prizeAmount = $period->nominator_prize ?? 500;

        // Get all eligible nominations (not already branch winners)
        $eligibleNominations = CompetitionNomination::where('competition_period_id', $period->id)
            ->where('is_valid', true)
            ->where(function ($q) {
                $q->whereNull('is_winner')
                    ->orWhere('is_winner', false);
            })
            ->whereHas('participant', function ($q) {
                $q->where('is_blocked', false);
            })
            ->with('participant')
            ->get();

        if ($eligibleNominations->isEmpty()) {
            return [];
        }

        // Shuffle and pick random winners
        $selectedNominations = $eligibleNominations->shuffle()->take($winnersCount);

        $winners = [];

        foreach ($selectedNominations as $nomination) {
            // Create winner record
            $winner = CompetitionWinner::create([
                'competition_period_id' => $period->id,
                'competition_branch_id' => $nomination->competition_branch_id,
                'participant_id' => $nomination->participant_id,
                'nomination_id' => $nomination->id,
                'winner_type' => 'lottery',
                'prize_amount' => $prizeAmount,
                'selected_at' => now(),
                'lottery_ticket_number' => CompetitionWinner::generateLotteryNumber(),
                'claim_code' => $this->generateClaimCode(),
            ]);

            // Update nomination
            $nomination->update([
                'is_winner' => true,
                'winner_type' => 'lottery',
                'prize_amount' => $prizeAmount,
                'won_at' => now(),
            ]);

            $winners[] = [
                'winner' => $winner,
                'participant' => $nomination->participant,
                'nomination' => $nomination,
                'prize_amount' => $prizeAmount,
            ];
        }

        return $winners;
    }

    /**
     * Generate unique claim code
     */
    protected function generateClaimCode(): string
    {
        do {
            $code = strtoupper('WIN-' . substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (CompetitionWinner::where('claim_code', $code)->exists());

        return $code;
    }

    /**
     * Dispatch notification jobs for all winners
     */
    protected function dispatchWinnerNotifications(array $branchWinners, array $nominatorWinners): void
    {
        $delay = 5;

        // Notify branch nominators
        foreach ($branchWinners as $branchWinner) {
            foreach ($branchWinner['nominators'] as $participant) {
                dispatch(new NotifyWinnerJob(
                    $participant,
                    $branchWinner['winner'],
                    'branch_nominator'
                ))->onQueue('notifications')->delay(now()->addSeconds($delay));
                $delay += 2; // Stagger notifications
            }
        }

        // Notify lottery winners
        foreach ($nominatorWinners as $nominatorWinner) {
            dispatch(new NotifyWinnerJob(
                $nominatorWinner['participant'],
                $nominatorWinner['winner'],
                'lottery'
            ))->onQueue('notifications')->delay(now()->addSeconds($delay));
            $delay += 2;
        }
    }

    /**
     * Announce winners publicly
     */
    public function announceWinners(CompetitionPeriod $period): void
    {
        if (!$period->winners_selected) {
            throw new \InvalidArgumentException('Winners must be selected before announcement');
        }

        $period->update([
            'winners_announced' => true,
            'winners_announced_at' => now(),
        ]);

        Log::info('Competition winners announced', ['period_id' => $period->id]);
    }

    /**
     * Get winners for a period
     */
    public function getWinners(CompetitionPeriod $period): Collection
    {
        return CompetitionWinner::where('competition_period_id', $period->id)
            ->with(['competitionBranch', 'participant', 'score'])
            ->orderBy('winner_type')
            ->orderBy('prize_rank')
            ->get();
    }

    /**
     * Get branch winners (top 3)
     */
    public function getBranchWinners(CompetitionPeriod $period): Collection
    {
        return CompetitionWinner::where('competition_period_id', $period->id)
            ->where('winner_type', 'branch')
            ->with(['competitionBranch', 'score'])
            ->orderBy('prize_rank')
            ->get();
    }

    /**
     * Get lottery winners
     */
    public function getLotteryWinners(CompetitionPeriod $period): Collection
    {
        return CompetitionWinner::where('competition_period_id', $period->id)
            ->where('winner_type', 'lottery')
            ->with(['competitionBranch', 'participant'])
            ->get();
    }

    /**
     * Get total prizes distributed for a period
     */
    public function getTotalPrizes(CompetitionPeriod $period): float
    {
        return CompetitionWinner::where('competition_period_id', $period->id)
            ->sum('prize_amount');
    }
}
