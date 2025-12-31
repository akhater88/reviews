<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\CompetitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoStartCompetitionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return 'auto-start-competitions';
    }

    public function handle(CompetitionService $competitionService): void
    {
        Log::info('Checking for competitions to auto-start');

        // Find draft competitions that should start today
        $competitions = InternalCompetition::where('status', CompetitionStatus::DRAFT)
            ->whereDate('start_date', '<=', today())
            ->get();

        foreach ($competitions as $competition) {
            try {
                // Check if it can be activated
                if ($competition->canBeActivated()) {
                    $competitionService->activate($competition);

                    Log::info('Competition auto-started', [
                        'competition_id' => $competition->id,
                        'name' => $competition->name,
                    ]);

                    // Send start notification
                    SendCompetitionNotificationJob::dispatch(
                        $competition->id,
                        'start'
                    );
                } else {
                    Log::warning('Competition cannot be auto-started (missing requirements)', [
                        'competition_id' => $competition->id,
                        'name' => $competition->name,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to auto-start competition', [
                    'competition_id' => $competition->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'auto-start',
        ];
    }
}
