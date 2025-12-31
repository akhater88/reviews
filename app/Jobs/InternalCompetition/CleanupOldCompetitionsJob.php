<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupOldCompetitionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public int $retentionDays = 365 // Keep data for 1 year
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return 'cleanup-old-competitions';
    }

    public function handle(): void
    {
        Log::info('Starting old competition cleanup', [
            'retention_days' => $this->retentionDays,
        ]);

        $cutoffDate = now()->subDays($this->retentionDays);

        // Clean up old notifications (keep for 90 days only)
        $notificationsDeleted = InternalCompetitionNotification::where('created_at', '<', now()->subDays(90))
            ->delete();

        Log::info('Old notifications cleaned up', [
            'deleted_count' => $notificationsDeleted,
        ]);

        // Archive or delete old cancelled competitions
        $cancelledCompetitions = InternalCompetition::where('status', CompetitionStatus::CANCELLED)
            ->where('updated_at', '<', $cutoffDate)
            ->get();

        foreach ($cancelledCompetitions as $competition) {
            $this->archiveCompetition($competition);
        }

        Log::info('Old competition cleanup completed', [
            'cancelled_archived' => $cancelledCompetitions->count(),
        ]);
    }

    protected function archiveCompetition(InternalCompetition $competition): void
    {
        // You could move to an archive table or just soft delete
        // For now, we'll just log and potentially clean up related data

        Log::info('Archiving old competition', [
            'competition_id' => $competition->id,
            'name' => $competition->name,
            'status' => $competition->status->value,
        ]);

        // Delete related transient data
        $competition->notifications()->delete();

        // Keep the main competition record for audit purposes
        // but you could also fully delete here if needed
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'cleanup',
        ];
    }
}
