<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\EmployeeExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractEmployeeMentionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 900; // 15 minutes

    public function __construct(
        public int $competitionId,
        public ?int $branchId = null
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return $this->branchId
            ? "extract-employees-{$this->competitionId}-{$this->branchId}"
            : "extract-employees-{$this->competitionId}";
    }

    public function handle(EmployeeExtractionService $extractionService): void
    {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::warning('Competition not found for employee extraction', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        // Check if employee mentions metric is enabled
        if (!$competition->isMetricEnabled(CompetitionMetric::EMPLOYEE_MENTIONS)) {
            Log::info('Employee mentions metric not enabled, skipping', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        // Only process active competitions
        if ($competition->status !== CompetitionStatus::ACTIVE) {
            Log::info('Competition not active, skipping employee extraction', [
                'competition_id' => $this->competitionId,
                'status' => $competition->status->value,
            ]);
            return;
        }

        try {
            $periodStart = $competition->start_date;
            $periodEnd = min($competition->end_date, now());

            if ($this->branchId) {
                // Extract for specific branch
                $employees = $extractionService->extractAndSaveEmployees(
                    $competition,
                    $this->branchId,
                    $periodStart,
                    $periodEnd
                );

                Log::info('Employee mentions extracted for branch', [
                    'competition_id' => $this->competitionId,
                    'branch_id' => $this->branchId,
                    'employees_found' => $employees->count(),
                ]);
            } else {
                // Extract for all branches
                $employees = $extractionService->calculateForAllBranches(
                    $competition,
                    $periodStart,
                    $periodEnd
                );

                Log::info('Employee mentions extracted for competition', [
                    'competition_id' => $this->competitionId,
                    'total_employees' => $employees->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to extract employee mentions', [
                'competition_id' => $this->competitionId,
                'branch_id' => $this->branchId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'employee-extraction',
            "competition:{$this->competitionId}",
        ];
    }
}
