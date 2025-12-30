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

class AutoEndCompetitionsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return 'auto-end-competitions';
    }

    public function handle(): void
    {
        Log::info('Checking for competitions to auto-end');

        // Find active competitions that should end today or have passed end date
        $competitions = InternalCompetition::where('status', CompetitionStatus::ACTIVE)
            ->whereDate('end_date', '<=', today())
            ->get();

        foreach ($competitions as $competition) {
            Log::info('Dispatching competition end job', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
                'end_date' => $competition->end_date->toDateString(),
            ]);

            // Dispatch the end processing job
            ProcessCompetitionEndJob::dispatch($competition->id);
        }

        Log::info('Auto-end check completed', [
            'competitions_to_end' => $competitions->count(),
        ]);
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'auto-end',
        ];
    }
}
