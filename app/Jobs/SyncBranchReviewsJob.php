<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\Branch;
use App\Services\Reviews\ReviewSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBranchReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600; // 10 minutes
    public int $backoff = 60;  // 1 minute between retries

    public function __construct(
        public Branch $branch,
        public bool $fullSync = false
    ) {}

    public function handle(ReviewSyncService $syncService): void
    {
        Log::info('SyncBranchReviewsJob: Starting', [
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'full_sync' => $this->fullSync,
        ]);

        try {
            $stats = $syncService->syncBranch($this->branch, $this->fullSync);

            Log::info('SyncBranchReviewsJob: Completed', [
                'branch_id' => $this->branch->id,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('SyncBranchReviewsJob: Failed', [
                'branch_id' => $this->branch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncBranchReviewsJob: Permanently failed', [
            'branch_id' => $this->branch->id,
            'error' => $exception->getMessage(),
        ]);

        // Update branch status to failed
        $this->branch->update(['sync_status' => SyncStatus::FAILED]);
    }
}
