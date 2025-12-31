<?php

namespace App\Console\Commands\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Jobs\InternalCompetition\SendCompetitionNotificationJob;
use App\Models\InternalCompetition\InternalCompetition;
use Illuminate\Console\Command;

class SendCompetitionRemindersCommand extends Command
{
    protected $signature = 'internal-competition:send-reminders
                            {--competition= : Specific competition ID}
                            {--type=reminder : Notification type (reminder, progress, ending_soon)}
                            {--dry-run : Show what would be sent without sending}';

    protected $description = 'Send competition reminders manually';

    public function handle(): int
    {
        $competitionId = $this->option('competition');
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($competitionId) {
            $competitions = InternalCompetition::where('id', $competitionId)
                ->where('status', CompetitionStatus::ACTIVE)
                ->get();
        } else {
            $competitions = InternalCompetition::active()->get();
        }

        if ($competitions->isEmpty()) {
            $this->warn('No active competitions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$competitions->count()} active competition(s).");

        foreach ($competitions as $competition) {
            $this->line("Processing: {$competition->name} (ID: {$competition->id})");
            $this->line("  - Remaining days: {$competition->remaining_days}");
            $this->line("  - Branches: {$competition->activeBranches()->count()}");

            if ($dryRun) {
                $this->info("  [DRY RUN] Would send '{$type}' notification");
            } else {
                SendCompetitionNotificationJob::dispatch(
                    $competition->id,
                    $type
                );
                $this->info("  Notification job dispatched.");
            }
        }

        return Command::SUCCESS;
    }
}
