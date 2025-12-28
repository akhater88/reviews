<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionBranch;
use App\Services\Competition\OutscraperReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBranchReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public int $backoff = 120;

    public function __construct(
        protected CompetitionBranch $branch,
        protected int $limit = 100
    ) {}

    public function handle(OutscraperReviewService $service): void
    {
        try {
            $this->branch->update(['sync_status' => 'syncing']);

            $results = $service->fetchReviews($this->branch, $this->limit);

            Log::info('Branch reviews synced', [
                'branch_id' => $this->branch->id,
                'results' => $results,
            ]);

            // Dispatch analysis job after sync
            dispatch(new AnalyzeBranchReviewsJob($this->branch))
                ->onQueue('competition')
                ->delay(now()->addSeconds(30));

        } catch (\Exception $e) {
            $this->branch->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->branch->update([
            'sync_status' => 'failed',
            'sync_error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return ['competition', 'review-sync', 'branch:' . $this->branch->id];
    }
}
