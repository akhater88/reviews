<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionWinner;
use App\Services\Competition\WinnerNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyWinnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        protected CompetitionParticipant $participant,
        protected CompetitionWinner $winner,
        protected string $winnerType
    ) {}

    public function handle(WinnerNotificationService $service): void
    {
        try {
            $results = $service->notifyWinner(
                $this->participant,
                $this->winner,
                $this->winnerType
            );

            Log::info('Winner notification sent', [
                'participant_id' => $this->participant->id,
                'winner_id' => $this->winner->id,
                'channels' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Winner notification failed', [
                'participant_id' => $this->participant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function tags(): array
    {
        return [
            'competition',
            'notification',
            'winner:' . $this->winner->id,
            'participant:' . $this->participant->id,
        ];
    }
}
