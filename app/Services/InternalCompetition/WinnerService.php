<?php

namespace App\Services\InternalCompetition;

use App\DataTransferObjects\InternalCompetition\WinnerData;
use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\PrizeStatus;
use App\Enums\InternalCompetition\PrizeType;
use App\Enums\InternalCompetition\WinnerType;
use App\Events\InternalCompetition\PrizeClaimed;
use App\Events\InternalCompetition\PrizeDelivered;
use App\Events\InternalCompetition\WinnersAnnounced;
use App\Exceptions\InternalCompetition\WinnerException;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\InternalCompetition\InternalCompetitionEmployee;
use App\Models\InternalCompetition\InternalCompetitionPrize;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WinnerService
{
    protected const DEFAULT_WINNER_RANKS = [1, 2, 3];

    /**
     * Determine all winners for a competition
     */
    public function determineWinners(
        InternalCompetition $competition,
        array $ranks = self::DEFAULT_WINNER_RANKS
    ): Collection {
        // Validate competition status
        if (!in_array($competition->status, [CompetitionStatus::ENDED, CompetitionStatus::CALCULATING])) {
            throw WinnerException::competitionNotEnded($competition->id);
        }

        // Check if winners already exist
        if ($competition->winners()->exists()) {
            throw WinnerException::winnersAlreadyDetermined($competition->id);
        }

        $winners = collect();

        DB::beginTransaction();

        try {
            // Determine winners for each enabled metric
            foreach ($competition->enabled_metrics as $metric) {
                $metricWinners = $this->determineMetricWinners($competition, $metric, $ranks);
                $winners = $winners->merge($metricWinners);
            }

            DB::commit();

            Log::info('Winners determined for competition', [
                'competition_id' => $competition->id,
                'winners_count' => $winners->count(),
            ]);

            event(new WinnersAnnounced($competition, $winners));

            return $winners;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to determine winners', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Determine winners for a specific metric
     */
    protected function determineMetricWinners(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        array $ranks
    ): Collection {
        $winners = collect();

        // Get top performers based on metric type
        if ($metric === CompetitionMetric::EMPLOYEE_MENTIONS) {
            $topPerformers = $this->getTopEmployees($competition, max($ranks));
        } else {
            $topPerformers = $this->getTopBranches($competition, $metric, max($ranks));
        }

        foreach ($ranks as $rank) {
            $performer = $topPerformers->firstWhere('rank', $rank);

            if (!$performer) {
                continue;
            }

            // Find prize for this metric and rank
            $prize = $this->findPrize($competition, $metric, $rank);

            // Create winner record
            $winner = $this->createWinnerRecord($competition, $metric, $performer, $rank, $prize);
            $winners->push($winner);
        }

        return $winners;
    }

    /**
     * Get top branches for a metric
     */
    protected function getTopBranches(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        int $limit
    ): Collection {
        return InternalCompetitionBranchScore::where('competition_id', $competition->id)
            ->where('metric_type', $metric->value)
            ->where('is_final', true)
            ->orderBy('rank')
            ->limit($limit)
            ->with(['branch', 'tenant'])
            ->get()
            ->map(function ($score) {
                return [
                    'type' => 'branch',
                    'rank' => $score->rank,
                    'score' => $score->score,
                    'tenant_id' => $score->tenant_id,
                    'branch_id' => $score->branch_id,
                    'branch' => $score->branch,
                    'tenant' => $score->tenant,
                ];
            });
    }

    /**
     * Get top employees
     */
    protected function getTopEmployees(
        InternalCompetition $competition,
        int $limit
    ): Collection {
        return InternalCompetitionEmployee::where('competition_id', $competition->id)
            ->where('is_final', true)
            ->orderBy('rank')
            ->limit($limit)
            ->with(['branch', 'tenant'])
            ->get()
            ->map(function ($employee) {
                return [
                    'type' => 'employee',
                    'rank' => $employee->rank,
                    'score' => $employee->score,
                    'tenant_id' => $employee->tenant_id,
                    'branch_id' => $employee->branch_id,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->employee_name,
                    'branch' => $employee->branch,
                    'tenant' => $employee->tenant,
                ];
            });
    }

    /**
     * Find prize for metric and rank
     */
    protected function findPrize(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        int $rank
    ): ?InternalCompetitionPrize {
        return InternalCompetitionPrize::where('competition_id', $competition->id)
            ->where('metric_type', $metric->value)
            ->where('rank', $rank)
            ->first();
    }

    /**
     * Create winner record
     */
    protected function createWinnerRecord(
        InternalCompetition $competition,
        CompetitionMetric $metric,
        array $performer,
        int $rank,
        ?InternalCompetitionPrize $prize
    ): InternalCompetitionWinner {
        $winnerType = $performer['type'] === 'employee'
            ? WinnerType::EMPLOYEE
            : WinnerType::BRANCH;

        return InternalCompetitionWinner::create([
            'competition_id' => $competition->id,
            'prize_id' => $prize?->id,
            'winner_type' => $winnerType,
            'tenant_id' => $performer['tenant_id'],
            'branch_id' => $performer['branch_id'],
            'employee_id' => $performer['employee_id'] ?? null,
            'employee_name' => $performer['employee_name'] ?? null,
            'metric_type' => $metric,
            'final_score' => $performer['score'],
            'final_rank' => $rank,
            'prize_status' => PrizeStatus::ANNOUNCED,
            'announced_at' => now(),
        ]);
    }

    /**
     * Get all winners for a competition
     */
    public function getWinners(InternalCompetition $competition): Collection
    {
        return InternalCompetitionWinner::where('competition_id', $competition->id)
            ->with(['prize', 'branch', 'tenant'])
            ->orderBy('metric_type')
            ->orderBy('final_rank')
            ->get();
    }

    /**
     * Get winners for a specific tenant (for tenant admin view)
     */
    public function getWinnersForTenant(
        InternalCompetition $competition,
        int $tenantId
    ): Collection {
        return InternalCompetitionWinner::where('competition_id', $competition->id)
            ->where('tenant_id', $tenantId)
            ->with(['prize', 'branch'])
            ->orderBy('metric_type')
            ->orderBy('final_rank')
            ->get();
    }

    /**
     * Get winners by metric
     */
    public function getWinnersByMetric(
        InternalCompetition $competition,
        CompetitionMetric $metric
    ): Collection {
        return InternalCompetitionWinner::where('competition_id', $competition->id)
            ->where('metric_type', $metric)
            ->with(['prize', 'branch', 'tenant'])
            ->orderBy('final_rank')
            ->get();
    }

    /**
     * Claim a prize
     */
    public function claimPrize(
        InternalCompetitionWinner $winner,
        array $recipientInfo = []
    ): InternalCompetitionWinner {
        // Validate current status
        if ($winner->prize_status !== PrizeStatus::ANNOUNCED) {
            throw WinnerException::invalidStatusTransition(
                $winner->prize_status->value,
                PrizeStatus::CLAIMED->value
            );
        }

        $winner->update([
            'prize_status' => PrizeStatus::CLAIMED,
            'claimed_at' => now(),
            'recipient_name' => $recipientInfo['name'] ?? null,
            'recipient_phone' => $recipientInfo['phone'] ?? null,
            'recipient_address' => $recipientInfo['address'] ?? null,
        ]);

        Log::info('Prize claimed', [
            'winner_id' => $winner->id,
            'competition_id' => $winner->competition_id,
        ]);

        event(new PrizeClaimed($winner));

        return $winner->fresh();
    }

    /**
     * Start processing a prize
     */
    public function startProcessing(InternalCompetitionWinner $winner): InternalCompetitionWinner
    {
        if (!in_array($winner->prize_status, [PrizeStatus::ANNOUNCED, PrizeStatus::CLAIMED])) {
            throw WinnerException::invalidStatusTransition(
                $winner->prize_status->value,
                PrizeStatus::PROCESSING->value
            );
        }

        $winner->update([
            'prize_status' => PrizeStatus::PROCESSING,
        ]);

        Log::info('Prize processing started', [
            'winner_id' => $winner->id,
        ]);

        return $winner->fresh();
    }

    /**
     * Mark prize as delivered
     */
    public function markAsDelivered(
        InternalCompetitionWinner $winner,
        ?string $proofPath = null,
        ?string $notes = null
    ): InternalCompetitionWinner {
        if ($winner->prize_status === PrizeStatus::DELIVERED) {
            throw WinnerException::prizeAlreadyDelivered($winner->id);
        }

        if (!in_array($winner->prize_status, [PrizeStatus::CLAIMED, PrizeStatus::PROCESSING])) {
            throw WinnerException::invalidStatusTransition(
                $winner->prize_status->value,
                PrizeStatus::DELIVERED->value
            );
        }

        $winner->update([
            'prize_status' => PrizeStatus::DELIVERED,
            'delivered_at' => now(),
            'delivery_proof_path' => $proofPath,
            'delivery_notes' => $notes,
        ]);

        Log::info('Prize delivered', [
            'winner_id' => $winner->id,
            'competition_id' => $winner->competition_id,
        ]);

        event(new PrizeDelivered($winner));

        return $winner->fresh();
    }

    /**
     * Update recipient information
     */
    public function updateRecipientInfo(
        InternalCompetitionWinner $winner,
        array $info
    ): InternalCompetitionWinner {
        if ($winner->prize_status === PrizeStatus::DELIVERED) {
            throw WinnerException::prizeAlreadyDelivered($winner->id);
        }

        $winner->update([
            'recipient_name' => $info['name'] ?? $winner->recipient_name,
            'recipient_phone' => $info['phone'] ?? $winner->recipient_phone,
            'recipient_address' => $info['address'] ?? $winner->recipient_address,
        ]);

        return $winner->fresh();
    }

    /**
     * Get prize delivery summary
     */
    public function getDeliverySummary(InternalCompetition $competition): array
    {
        $winners = $competition->winners;

        $byStatus = $winners->groupBy(fn ($w) => $w->prize_status->value);

        return [
            'total' => $winners->count(),
            'announced' => $byStatus->get('announced', collect())->count(),
            'claimed' => $byStatus->get('claimed', collect())->count(),
            'processing' => $byStatus->get('processing', collect())->count(),
            'delivered' => $byStatus->get('delivered', collect())->count(),
            'delivery_rate' => $winners->count() > 0
                ? round(($byStatus->get('delivered', collect())->count() / $winners->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get pending deliveries
     */
    public function getPendingDeliveries(?InternalCompetition $competition = null): Collection
    {
        $query = InternalCompetitionWinner::whereIn('prize_status', [
            PrizeStatus::CLAIMED,
            PrizeStatus::PROCESSING,
        ])
        ->whereHas('prize', fn ($q) => $q->where('prize_type', PrizeType::PHYSICAL))
        ->with(['competition', 'prize', 'branch', 'tenant']);

        if ($competition) {
            $query->where('competition_id', $competition->id);
        }

        return $query->orderBy('claimed_at')->get();
    }

    /**
     * Get winners showcase data (for public display)
     */
    public function getShowcaseData(InternalCompetition $competition): array
    {
        if (!$competition->public_showcase) {
            return [];
        }

        $winners = $this->getWinners($competition);

        return $winners->groupBy(fn ($w) => $w->metric_type->value)
            ->map(function ($metricWinners, $metricType) {
                return [
                    'metric' => CompetitionMetric::from($metricType),
                    'metric_label' => CompetitionMetric::from($metricType)->getLabel(),
                    'winners' => $metricWinners->map(function ($winner) {
                        return [
                            'rank' => $winner->final_rank,
                            'rank_label' => $winner->rank_label,
                            'winner_type' => $winner->winner_type->value,
                            'winner_name' => $winner->winner_display_name,
                            'branch_name' => $winner->branch?->name,
                            'score' => $winner->final_score,
                            'prize_name' => $winner->prize?->display_name,
                            'prize_image' => $winner->prize?->image_url,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Redetermine winners (for corrections)
     */
    public function redetermineWinners(
        InternalCompetition $competition,
        array $ranks = self::DEFAULT_WINNER_RANKS
    ): Collection {
        DB::beginTransaction();

        try {
            // Delete existing winners
            $competition->winners()->delete();

            DB::commit();

            // Redetermine (determineWinners will start its own transaction)
            return $this->determineWinners($competition, $ranks);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get winner statistics
     */
    public function getWinnerStatistics(InternalCompetition $competition): array
    {
        $winners = $this->getWinners($competition);

        // Winners by tenant
        $byTenant = $winners->groupBy('tenant_id')->map(fn ($g) => $g->count());

        // Winners by metric
        $byMetric = $winners->groupBy(fn ($w) => $w->metric_type->value)->map(fn ($g) => $g->count());

        // Total prize value
        $totalPrizeValue = $winners->sum(fn ($w) => $w->prize?->estimated_value ?? 0);

        return [
            'total_winners' => $winners->count(),
            'unique_branches' => $winners->unique('branch_id')->count(),
            'unique_tenants' => $winners->unique('tenant_id')->count(),
            'winners_by_tenant' => $byTenant->toArray(),
            'winners_by_metric' => $byMetric->toArray(),
            'total_prize_value' => $totalPrizeValue,
            'prize_value_currency' => 'SAR',
        ];
    }

    /**
     * Check if a branch/employee won anything
     */
    public function checkIfWinner(
        InternalCompetition $competition,
        ?int $branchId = null,
        ?int $employeeId = null
    ): Collection {
        $query = InternalCompetitionWinner::where('competition_id', $competition->id);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->with(['prize', 'competition'])->get();
    }

    /**
     * Get podium data for display
     */
    public function getPodiumData(
        InternalCompetition $competition,
        CompetitionMetric $metric
    ): array {
        $winners = $this->getWinnersByMetric($competition, $metric);

        $podium = [
            'first' => null,
            'second' => null,
            'third' => null,
        ];

        foreach ($winners as $winner) {
            $data = [
                'winner_id' => $winner->id,
                'winner_type' => $winner->winner_type->value,
                'winner_name' => $winner->winner_display_name,
                'branch_name' => $winner->branch?->name,
                'tenant_name' => $winner->tenant?->name,
                'score' => $winner->final_score,
                'prize' => $winner->prize ? [
                    'name' => $winner->prize->display_name,
                    'image' => $winner->prize->image_url,
                    'value' => $winner->prize->estimated_value,
                ] : null,
            ];

            match ($winner->final_rank) {
                1 => $podium['first'] = $data,
                2 => $podium['second'] = $data,
                3 => $podium['third'] = $data,
                default => null,
            };
        }

        return [
            'metric' => $metric->value,
            'metric_label' => $metric->getLabel(),
            'podium' => $podium,
        ];
    }
}
