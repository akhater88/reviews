<?php

namespace App\Console\Commands\Competition;

use App\Jobs\Competition\CalculateBranchScoreJob;
use App\Jobs\Competition\SyncBranchReviewsJob;
use App\Jobs\Competition\UpdateRankingsJob;
use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionPeriod;
use Illuminate\Console\Command;

class ProcessCompetitionScoresCommand extends Command
{
    protected $signature = 'competition:process-scores
                            {--period= : Specific period ID}
                            {--branch= : Specific branch ID}
                            {--sync : Also sync reviews before calculating}';

    protected $description = 'Process competition scores for all or specific branches';

    public function handle(): int
    {
        $periodId = $this->option('period');
        $branchId = $this->option('branch');
        $sync = $this->option('sync');

        // Get period
        $period = $periodId
            ? CompetitionPeriod::findOrFail($periodId)
            : CompetitionPeriod::current();

        if (!$period) {
            $this->error('No active competition period found.');

            return 1;
        }

        $this->info("Processing scores for period: {$period->name}");

        // Get branches
        $query = CompetitionBranch::whereHas('nominations', function ($q) use ($period) {
            $q->where('competition_period_id', $period->id);
        });

        if ($branchId) {
            $query->where('id', $branchId);
        }

        $branches = $query->get();

        if ($branches->isEmpty()) {
            $this->warn('No branches found to process.');

            return 0;
        }

        $this->info("Found {$branches->count()} branches to process.");

        $bar = $this->output->createProgressBar($branches->count());
        $bar->start();

        foreach ($branches as $branch) {
            if ($sync) {
                dispatch(new SyncBranchReviewsJob($branch))
                    ->onQueue('competition');
            }

            dispatch(new CalculateBranchScoreJob($branch, $period))
                ->onQueue('competition')
                ->delay($sync ? now()->addMinutes(5) : now());

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Queue ranking update
        dispatch(new UpdateRankingsJob($period))
            ->onQueue('competition')
            ->delay(now()->addMinutes($sync ? 10 : 2));

        $this->info('Score processing jobs dispatched successfully.');
        $this->info('Rankings will be updated after all scores are calculated.');

        return 0;
    }
}
