<?php

namespace App\Console\Commands\Competition;

use App\Jobs\Competition\SyncBranchReviewsJob;
use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionPeriod;
use Illuminate\Console\Command;

class SyncCompetitionReviewsCommand extends Command
{
    protected $signature = 'competition:sync-reviews
                            {--period= : Specific period ID}
                            {--stale-hours=24 : Sync branches not synced in X hours}';

    protected $description = 'Sync reviews for competition branches from Outscraper';

    public function handle(): int
    {
        $periodId = $this->option('period');
        $staleHours = (int) $this->option('stale-hours');

        $period = $periodId
            ? CompetitionPeriod::findOrFail($periodId)
            : CompetitionPeriod::current();

        if (!$period) {
            $this->error('No active competition period found.');

            return 1;
        }

        $query = CompetitionBranch::whereHas('nominations', function ($q) use ($period) {
            $q->where('competition_period_id', $period->id);
        })->where(function ($q) use ($staleHours) {
            $q->whereNull('reviews_last_synced_at')
              ->orWhere('reviews_last_synced_at', '<', now()->subHours($staleHours));
        });

        $branches = $query->get();

        if ($branches->isEmpty()) {
            $this->info('All branches are up to date.');

            return 0;
        }

        $this->info("Found {$branches->count()} branches to sync.");

        $bar = $this->output->createProgressBar($branches->count());
        $bar->start();

        $delay = 0;
        foreach ($branches as $branch) {
            dispatch(new SyncBranchReviewsJob($branch))
                ->onQueue('competition')
                ->delay(now()->addSeconds($delay));

            $delay += 10; // Stagger jobs by 10 seconds
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Review sync jobs dispatched successfully.');

        return 0;
    }
}
