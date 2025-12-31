<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\CustomerSatisfactionScoreService;
use App\Services\InternalCompetition\EmployeeExtractionService;
use App\Services\InternalCompetition\ResponseTimeScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateFinalRankingsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public int $competitionId
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return "final-rankings-{$this->competitionId}";
    }

    public function handle(
        CustomerSatisfactionScoreService $satisfactionService,
        ResponseTimeScoreService $responseTimeService,
        EmployeeExtractionService $employeeService
    ): void {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::warning('Competition not found for final rankings', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        Log::info('Calculating final rankings', [
            'competition_id' => $this->competitionId,
        ]);

        try {
            // Update rankings for each enabled metric
            if ($competition->isMetricEnabled(CompetitionMetric::CUSTOMER_SATISFACTION)) {
                $satisfactionService->updateRankings($competition);
                Log::info('Satisfaction rankings updated');
            }

            if ($competition->isMetricEnabled(CompetitionMetric::RESPONSE_TIME)) {
                $responseTimeService->updateRankings($competition);
                Log::info('Response time rankings updated');
            }

            if ($competition->isMetricEnabled(CompetitionMetric::EMPLOYEE_MENTIONS)) {
                $employeeService->updateRankings($competition);
                Log::info('Employee rankings updated');
            }

            Log::info('Final rankings calculated successfully', [
                'competition_id' => $this->competitionId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to calculate final rankings', [
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
            'final-rankings',
            "competition:{$this->competitionId}",
        ];
    }
}
