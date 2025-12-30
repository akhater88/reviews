<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\User;
use App\Services\InternalCompetition\CompetitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishCompetitionResultsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $competitionId,
        public bool $notifyWinners = true
    ) {
        $this->onQueue('internal-competition');
    }

    public function uniqueId(): string
    {
        return "publish-results-{$this->competitionId}";
    }

    public function handle(CompetitionService $competitionService): void
    {
        $competition = InternalCompetition::find($this->competitionId);

        if (!$competition) {
            Log::warning('Competition not found for publishing', [
                'competition_id' => $this->competitionId,
            ]);
            return;
        }

        if ($competition->status !== CompetitionStatus::ENDED) {
            Log::warning('Competition not in ended status, cannot publish', [
                'competition_id' => $this->competitionId,
                'status' => $competition->status->value,
            ]);
            return;
        }

        try {
            // Publish the competition
            $competitionService->publish($competition);

            Log::info('Competition results published', [
                'competition_id' => $this->competitionId,
            ]);

            // Notify winners
            if ($this->notifyWinners) {
                $this->notifyWinners($competition);
            }

        } catch (\Exception $e) {
            Log::error('Failed to publish competition results', [
                'competition_id' => $this->competitionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function notifyWinners(InternalCompetition $competition): void
    {
        $winners = $competition->winners()->with('branch', 'tenant')->get();

        foreach ($winners as $winner) {
            // Get the branch manager or tenant admin to notify
            $userId = $this->getNotificationUserId($winner);

            if ($userId) {
                SendCompetitionNotificationJob::dispatch(
                    $competition->id,
                    'winner',
                    $userId,
                    [
                        'winner_id' => $winner->id,
                        'rank' => $winner->final_rank,
                        'metric' => $winner->metric_type->value,
                        'prize_name' => $winner->prize?->display_name,
                    ]
                );
            }
        }

        Log::info('Winner notifications dispatched', [
            'competition_id' => $competition->id,
            'winners_count' => $winners->count(),
        ]);
    }

    protected function getNotificationUserId($winner): ?int
    {
        // Try to find branch manager first
        $branchManager = User::where('branch_id', $winner->branch_id)
            ->where('role', 'manager')
            ->first();

        if ($branchManager) {
            return $branchManager->id;
        }

        // Fall back to tenant admin
        $tenantAdmin = User::where('tenant_id', $winner->tenant_id)
            ->where('role', 'admin')
            ->first();

        return $tenantAdmin?->id;
    }

    public function tags(): array
    {
        return [
            'internal-competition',
            'publish-results',
            "competition:{$this->competitionId}",
        ];
    }
}
