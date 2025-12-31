<?php

namespace App\Console\Commands\InternalCompetition;

use App\Jobs\InternalCompetition\AutoEndCompetitionsJob;
use App\Jobs\InternalCompetition\AutoStartCompetitionsJob;
use App\Jobs\InternalCompetition\CalculateDailyScoresJob;
use App\Jobs\InternalCompetition\ScheduleCompetitionRemindersJob;
use Illuminate\Console\Command;

class ProcessCompetitionsCommand extends Command
{
    protected $signature = 'internal-competition:process
                            {--scores : Calculate daily scores}
                            {--start : Auto-start pending competitions}
                            {--end : Auto-end finished competitions}
                            {--reminders : Schedule reminders}
                            {--all : Run all processes}';

    protected $description = 'Process internal competitions (scores, start, end, reminders)';

    public function handle(): int
    {
        $runAll = $this->option('all');

        if ($runAll || $this->option('start')) {
            $this->info('Dispatching auto-start job...');
            AutoStartCompetitionsJob::dispatch();
        }

        if ($runAll || $this->option('end')) {
            $this->info('Dispatching auto-end job...');
            AutoEndCompetitionsJob::dispatch();
        }

        if ($runAll || $this->option('scores')) {
            $this->info('Dispatching daily scores job...');
            CalculateDailyScoresJob::dispatch();
        }

        if ($runAll || $this->option('reminders')) {
            $this->info('Dispatching reminders job...');
            ScheduleCompetitionRemindersJob::dispatch();
        }

        $this->info('Jobs dispatched successfully!');

        return Command::SUCCESS;
    }
}
