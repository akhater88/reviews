<?php

namespace App\Jobs\Competition;

use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\RankingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRankingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected CompetitionPeriod $period
    ) {}

    public function handle(RankingService $service): void
    {
        $service->updateRankings($this->period);
    }

    public function tags(): array
    {
        return ['competition', 'rankings', 'period:' . $this->period->id];
    }
}
