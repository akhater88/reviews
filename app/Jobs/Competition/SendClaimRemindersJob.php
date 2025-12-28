<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionWinner;
use App\Services\Competition\WinnerNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendClaimRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(WinnerNotificationService $service): void
    {
        // Get unclaimed winners with prizes, selected 7+ days ago
        $unclaimedWinners = CompetitionWinner::where('prize_claimed', false)
            ->whereNotNull('prize_amount')
            ->where('prize_amount', '>', 0)
            ->where('selected_at', '<', now()->subDays(7))
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subDays(7));
            })
            ->where('selected_at', '>', now()->subDays(25)) // Still within claim period
            ->with('participant')
            ->get();

        $sentCount = 0;

        foreach ($unclaimedWinners as $winner) {
            try {
                if ($service->sendClaimReminder($winner)) {
                    $sentCount++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to send claim reminder', [
                    'winner_id' => $winner->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Claim reminders sent', [
            'total_unclaimed' => $unclaimedWinners->count(),
            'reminders_sent' => $sentCount,
        ]);
    }

    public function tags(): array
    {
        return ['competition', 'claim-reminders'];
    }
}
