<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScheduleCompetitionRemindersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return 'schedule-reminders';
    }

    public function handle(): void
    {
        Log::info('Checking for competition reminders to send');

        $competitions = InternalCompetition::active()->get();

        foreach ($competitions as $competition) {
            $this->checkAndScheduleReminders($competition);
        }
    }

    protected function checkAndScheduleReminders(InternalCompetition $competition): void
    {
        $remainingDays = $competition->remaining_days;
        $settings = $competition->notification_settings ?? [];
        $reminderDays = $settings['reminder_days'] ?? [7, 3, 1];

        // Weekly reminder (every 7 days if competition is longer)
        if ($competition->duration_in_days > 14 && $remainingDays % 7 === 0 && $remainingDays > 0) {
            $this->dispatchReminder($competition, 'reminder');
        }

        // Ending soon reminders
        if (in_array($remainingDays, $reminderDays)) {
            $eventType = $remainingDays <= 3 ? 'ending_soon' : 'reminder';
            $this->dispatchReminder($competition, $eventType);
        }

        // Progress update (mid-competition)
        $midPoint = (int) ($competition->duration_in_days / 2);
        if ($remainingDays === $midPoint) {
            $this->dispatchReminder($competition, 'progress');
        }
    }

    protected function dispatchReminder(InternalCompetition $competition, string $eventType): void
    {
        // Check if we already sent this reminder today
        $alreadySent = $competition->notifications()
            ->where('event_type', $eventType)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) {
            Log::info('Reminder already sent today, skipping', [
                'competition_id' => $competition->id,
                'event_type' => $eventType,
            ]);
            return;
        }

        SendCompetitionNotificationJob::dispatch(
            $competition->id,
            $eventType
        );

        Log::info('Competition reminder scheduled', [
            'competition_id' => $competition->id,
            'event_type' => $eventType,
            'remaining_days' => $competition->remaining_days,
        ]);
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'schedule-reminders',
        ];
    }
}
