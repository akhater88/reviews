<?php

namespace App\Jobs\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\BenchmarkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateBenchmarksJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        public int $competitionId,
        public bool $forceRecalculate = false
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return "benchmarks-{$this->competitionId}";
    }

    public function handle(BenchmarkService $benchmarkService): void
    {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::warning('Competition not found for benchmarks', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        Log::info('Starting benchmark calculation', [
            'competition_id' => $this->competitionId,
            'force_recalculate' => $this->forceRecalculate,
        ]);

        try {
            if ($this->forceRecalculate) {
                $results = $benchmarkService->recalculateBenchmarks($competition);
            } else {
                $results = $benchmarkService->calculateAllBenchmarks($competition);
            }

            Log::info('Benchmarks calculated successfully', [
                'competition_id' => $this->competitionId,
                'tenants_count' => count($results['tenants'] ?? []),
                'branches_count' => count($results['branches'] ?? []),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to calculate benchmarks', [
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
            'benchmarks',
            "competition:{$this->competitionId}",
        ];
    }
}
