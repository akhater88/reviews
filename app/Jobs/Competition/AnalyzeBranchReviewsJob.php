<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Services\Competition\ReviewAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeBranchReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        protected CompetitionBranch $branch,
        protected int $limit = 50
    ) {}

    public function handle(ReviewAnalysisService $service): void
    {
        $service->analyzeReviews($this->branch, $this->limit);
    }

    public function tags(): array
    {
        return ['competition', 'review-analysis', 'branch:' . $this->branch->id];
    }
}
