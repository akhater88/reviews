<?php

namespace App\Console\Commands;

use App\Jobs\SyncBranchReviewsJob;
use App\Models\Branch;
use Illuminate\Console\Command;

class SyncReviewsCommand extends Command
{
    protected $signature = 'reviews:sync
                            {branch? : Branch ID to sync (optional, syncs all if not provided)}
                            {--full : Perform full sync instead of incremental}
                            {--source= : Filter by source (manual, google_business)}
                            {--type= : Filter by type (owned, competitor)}
                            {--tenant= : Filter by tenant ID}';

    protected $description = 'Sync reviews from Outscraper for branches';

    public function handle(): int
    {
        $branchId = $this->argument('branch');
        $fullSync = $this->option('full');
        $source = $this->option('source');
        $type = $this->option('type');
        $tenantId = $this->option('tenant');

        if ($branchId) {
            return $this->syncSingleBranch($branchId, $fullSync);
        }

        return $this->syncMultipleBranches($source, $type, $tenantId, $fullSync);
    }

    private function syncSingleBranch(int $branchId, bool $fullSync): int
    {
        $branch = Branch::withoutGlobalScopes()->find($branchId);

        if (!$branch) {
            $this->error("Branch {$branchId} not found");
            return 1;
        }

        if (empty($branch->google_place_id)) {
            $this->error("Branch {$branchId} has no Google Place ID");
            return 1;
        }

        $syncType = $fullSync ? 'full' : 'incremental';
        $this->info("Dispatching {$syncType} sync job for: {$branch->name}");

        SyncBranchReviewsJob::dispatch($branch, $fullSync)->onQueue('reviews');

        $this->info('Sync job dispatched. Check queue for progress.');
        return 0;
    }

    private function syncMultipleBranches(?string $source, ?string $type, ?string $tenantId, bool $fullSync): int
    {
        $query = Branch::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereNotNull('google_place_id');

        if ($source) {
            $query->where('source', $source);
            $this->line("Filtering by source: {$source}");
        }

        if ($type) {
            $query->where('branch_type', $type);
            $this->line("Filtering by type: {$type}");
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
            $this->line("Filtering by tenant: {$tenantId}");
        }

        $branches = $query->get();

        if ($branches->isEmpty()) {
            $this->warn('No branches found matching the criteria.');
            return 0;
        }

        $syncType = $fullSync ? 'full' : 'incremental';
        $this->info("Dispatching {$syncType} sync jobs for {$branches->count()} branches...");

        $progressBar = $this->output->createProgressBar($branches->count());
        $progressBar->start();

        foreach ($branches as $branch) {
            SyncBranchReviewsJob::dispatch($branch, $fullSync)->onQueue('reviews');
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('All sync jobs dispatched. Check queue for progress.');
        $this->newLine();

        $this->table(
            ['ID', 'Name', 'Place ID', 'Source', 'Type'],
            $branches->map(fn ($b) => [
                $b->id,
                $b->name,
                substr($b->google_place_id, 0, 20) . '...',
                $b->source?->value ?? $b->source,
                $b->branch_type?->value ?? $b->branch_type,
            ])->toArray()
        );

        return 0;
    }
}
