<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\WinnerSelectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SelectWinnersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        protected CompetitionPeriod $period
    ) {}

    public function handle(WinnerSelectionService $service): void
    {
        try {
            $results = $service->selectWinners($this->period);

            Log::info('Winners selection completed', [
                'period_id' => $this->period->id,
                'branch_winners' => count($results['branch_winners']),
                'nominator_winners' => count($results['nominator_winners']),
                'total_prize' => $results['total_prize_amount'],
            ]);

        } catch (\Exception $e) {
            Log::error('Winners selection failed', [
                'period_id' => $this->period->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['competition', 'winner-selection', 'period:' . $this->period->id];
    }
}
