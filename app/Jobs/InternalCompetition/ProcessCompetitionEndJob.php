<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\BenchmarkService;
use App\Services\InternalCompetition\CompetitionService;
use App\Services\InternalCompetition\ScoreCalculationService;
use App\Services\InternalCompetition\WinnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCompetitionEndJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300;
    public int $timeout = 1200; // 20 minutes

    public function __construct(
        public int $competitionId
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return "process-end-{$this->competitionId}";
    }

    public function handle(
        CompetitionService $competitionService,
        ScoreCalculationService $scoreService,
        WinnerService $winnerService,
        BenchmarkService $benchmarkService
    ): void {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::error('Competition not found for end processing', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        // Verify competition should be ended
        if ($competition->status !== CompetitionStatus::ACTIVE) {
            Log::warning('Competition not active, cannot process end', [
                'competition_id' => $this->competitionId,
                'status' => $competition->status->value,
            ]);
            return;
        }

        Log::info('Starting competition end processing', [
            'competition_id' => $this->competitionId,
            'name' => $competition->name,
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Set status to calculating
            $competition->update([
                'status' => CompetitionStatus::CALCULATING,
            ]);

            // Step 2: Calculate final scores
            $scoreService->finalizeAllScores($competition);

            // Step 3: Calculate benchmarks
            $benchmarkService->calculateAllBenchmarks($competition);

            // Step 4: Determine winners
            $winners = $winnerService->determineWinners($competition);

            // Step 5: Set status to ended
            $competition->update([
                'status' => CompetitionStatus::ENDED,
                'ended_at' => now(),
            ]);

            DB::commit();

            Log::info('Competition end processing completed', [
                'competition_id' => $this->competitionId,
                'winners_count' => $winners->count(),
            ]);

            // Dispatch notification job for winners
            SendCompetitionNotificationJob::dispatch(
                $this->competitionId,
                'ended'
            )->delay(now()->addMinutes(5));

        } catch (\Exception $e) {
            DB::rollBack();

            // Revert to active status if failed
            $competition->update([
                'status' => CompetitionStatus::ACTIVE,
            ]);

            Log::error('Failed to process competition end', [
                'competition_id' => $this->competitionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'competition-end',
            "competition:{$this->competitionId}",
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessCompetitionEndJob failed permanently', [
            'competition_id' => $this->competitionId,
            'error' => $exception->getMessage(),
        ]);

        // Notify admins about the failure
        // You could dispatch a notification here
    }
}
