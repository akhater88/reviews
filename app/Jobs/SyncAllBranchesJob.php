<?php

namespace App\Jobs;

use App\Enums\BranchSource;
use App\Enums\BranchType;
use App\Enums\SyncStatus;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAllBranchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60; // Quick job - just dispatches child jobs

    /**
     * @param string|null $sourceFilter 'google_business', 'manual', or null for all
     * @param string|null $typeFilter 'owned', 'competitor', or null for all
     */
    public function __construct(
        public ?string $sourceFilter = null,
        public ?string $typeFilter = null
    ) {}

    public function handle(): void
    {
        Log::info('SyncAllBranchesJob: Starting', [
            'source_filter' => $this->sourceFilter,
            'type_filter' => $this->typeFilter,
        ]);

        $query = Branch::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereNotNull('google_place_id'); // Must have Place ID to sync

        // Apply filters
        if ($this->sourceFilter) {
            $query->where('source', $this->sourceFilter);
        }

        if ($this->typeFilter) {
            $query->where('branch_type', $this->typeFilter);
        }

        // Only sync branches that need it (not currently syncing)
        $query->where(function ($q) {
            $q->whereNull('sync_status')
              ->orWhere('sync_status', '!=', SyncStatus::SYNCING->value);
        });

        $branches = $query->get();

        Log::info('SyncAllBranchesJob: Found branches to sync', [
            'count' => $branches->count(),
        ]);

        // Dispatch individual sync jobs
        foreach ($branches as $branch) {
            SyncBranchReviewsJob::dispatch($branch)
                ->onQueue('reviews'); // Use dedicated queue
        }

        Log::info('SyncAllBranchesJob: Dispatched all jobs');
    }
}
