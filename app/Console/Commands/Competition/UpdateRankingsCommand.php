<?php

namespace App\Console\Commands\Competition;

use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\RankingService;
use Illuminate\Console\Command;

class UpdateRankingsCommand extends Command
{
    protected $signature = 'competition:update-rankings {--period= : Specific period ID}';

    protected $description = 'Update competition rankings';

    public function handle(RankingService $service): int
    {
        $periodId = $this->option('period');

        $period = $periodId
            ? CompetitionPeriod::findOrFail($periodId)
            : CompetitionPeriod::current();

        if (!$period) {
            $this->error('No active competition period found.');

            return 1;
        }

        $this->info("Updating rankings for period: {$period->name}");

        $updated = $service->updateRankings($period);

        $this->info("Rankings updated for {$updated} branches.");

        return 0;
    }
}
