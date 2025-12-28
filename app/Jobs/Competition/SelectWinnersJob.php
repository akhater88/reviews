<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use App\Models\Competition\CompetitionWinner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelectWinnersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected CompetitionPeriod $period
    ) {}

    public function handle(): void
    {
        $prizes = $this->period->prizes ?? [];
        $winnerCount = count($prizes);

        if ($winnerCount === 0) {
            $winnerCount = 3; // Default to top 3
        }

        $topScores = CompetitionScore::where('competition_period_id', $this->period->id)
            ->where('analysis_status', 'completed')
            ->whereNotNull('rank_position')
            ->orderBy('rank_position')
            ->limit($winnerCount)
            ->with('competitionBranch')
            ->get();

        if ($topScores->isEmpty()) {
            Log::warning('No scores found for winner selection', [
                'period_id' => $this->period->id,
            ]);

            return;
        }

        DB::beginTransaction();

        try {
            foreach ($topScores as $index => $score) {
                $prizeRank = $index + 1;
                $prize = $prizes[$index] ?? [];

                // Get first nominator as the winner
                $nomination = $score->competitionBranch->nominations()
                    ->where('competition_period_id', $this->period->id)
                    ->valid()
                    ->oldest()
                    ->with('participant')
                    ->first();

                if (!$nomination) {
                    continue;
                }

                // Create winner record
                CompetitionWinner::updateOrCreate(
                    [
                        'competition_period_id' => $this->period->id,
                        'competition_branch_id' => $score->competition_branch_id,
                    ],
                    [
                        'competition_participant_id' => $nomination->competition_participant_id,
                        'competition_nomination_id' => $nomination->id,
                        'prize_rank' => $prizeRank,
                        'prize_amount' => $prize['amount'] ?? null,
                        'prize_currency' => $prize['currency'] ?? 'SAR',
                        'prize_details' => $prize,
                    ]
                );

                // Mark nomination as winner
                $nomination->update([
                    'is_winner' => true,
                    'prize_rank' => $prizeRank,
                ]);
            }

            // Update period with winning info
            $firstWinner = $topScores->first();
            $this->period->update([
                'winning_branch_id' => $firstWinner->competition_branch_id,
                'winning_score' => $firstWinner->competition_score,
                'winners_announced_at' => now(),
            ]);

            DB::commit();

            Log::info('Competition winners selected', [
                'period_id' => $this->period->id,
                'winners_count' => $topScores->count(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to select competition winners', [
                'period_id' => $this->period->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['competition', 'winner-selection', 'period:' . $this->period->id];
    }
}
