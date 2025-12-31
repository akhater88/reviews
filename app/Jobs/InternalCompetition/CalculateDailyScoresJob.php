<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\ScoreCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateDailyScoresJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 600; // 10 minutes

    public function __construct(
        public ?int $competitionId = null
    ) {
        $this->onQueue('internal-competition');
    }

    /**
     * Unique ID for preventing duplicate jobs
     */
    public function uniqueId(): string
    {
        return $this->competitionId
            ? "daily-scores-{$this->competitionId}"
            : 'daily-scores-all';
    }

    public function handle(ScoreCalculationService $scoreService): void
    {
        Log::info('Starting daily scores calculation', [
            'competition_id' => $this->competitionId,
        ]);

        try {
            if ($this->competitionId) {
                // Calculate for specific competition
                $competition = InternalCompetition::find($this->competitionId);

                if (!$competition || $competition->status !== CompetitionStatus::ACTIVE) {
                    Log::warning('Competition not active, skipping score calculation', [
                        'competition_id' => $this->competitionId,
                    ]);
                    return;
                }

                $this->calculateForCompetition($competition, $scoreService);
            } else {
                // Calculate for all active competitions
                $competitions = InternalCompetition::active()->get();

                foreach ($competitions as $competition) {
                    $this->calculateForCompetition($competition, $scoreService);
                }

                Log::info('Daily scores calculated for all competitions', [
                    'competitions_count' => $competitions->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to calculate daily scores', [
                'competition_id' => $this->competitionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function calculateForCompetition(
        InternalCompetition $competition,
        ScoreCalculationService $scoreService
    ): void {
        $results = $scoreService->calculateAllScores($competition);

        Log::info('Daily scores calculated for competition', [
            'competition_id' => $competition->id,
            'name' => $competition->name,
            'satisfaction_count' => $results['satisfaction']['count'] ?? 0,
            'response_time_count' => $results['response_time']['count'] ?? 0,
            'employee_count' => $results['employee_mentions']['count'] ?? 0,
        ]);
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'daily-scores',
            $this->competitionId ? "competition:{$this->competitionId}" : 'all-competitions',
        ];
    }
}
