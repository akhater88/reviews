<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\ScoreCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateBranchScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected CompetitionBranch $branch,
        protected CompetitionPeriod $period
    ) {}

    public function handle(ScoreCalculationService $service): void
    {
        $service->calculateBranchScore($this->branch, $this->period);
    }

    public function tags(): array
    {
        return [
            'competition',
            'score-calculation',
            'branch:' . $this->branch->id,
            'period:' . $this->period->id,
        ];
    }
}
