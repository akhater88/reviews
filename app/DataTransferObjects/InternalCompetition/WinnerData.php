<?php

namespace App\DataTransferObjects\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\WinnerType;

class WinnerData
{
    public function __construct(
        public readonly CompetitionMetric $metric,
        public readonly WinnerType $winnerType,
        public readonly int $rank,
        public readonly float $score,
        public readonly int $tenantId,
        public readonly ?int $branchId,
        public readonly ?int $employeeId,
        public readonly ?string $employeeName,
        public readonly ?int $prizeId,
    ) {}

    public static function forBranch(
        CompetitionMetric $metric,
        int $rank,
        float $score,
        int $tenantId,
        int $branchId,
        ?int $prizeId = null
    ): self {
        return new self(
            metric: $metric,
            winnerType: WinnerType::BRANCH,
            rank: $rank,
            score: $score,
            tenantId: $tenantId,
            branchId: $branchId,
            employeeId: null,
            employeeName: null,
            prizeId: $prizeId,
        );
    }

    public static function forEmployee(
        CompetitionMetric $metric,
        int $rank,
        float $score,
        int $tenantId,
        int $branchId,
        int $employeeId,
        string $employeeName,
        ?int $prizeId = null
    ): self {
        return new self(
            metric: $metric,
            winnerType: WinnerType::EMPLOYEE,
            rank: $rank,
            score: $score,
            tenantId: $tenantId,
            branchId: $branchId,
            employeeId: $employeeId,
            employeeName: $employeeName,
            prizeId: $prizeId,
        );
    }

    public function toArray(): array
    {
        return [
            'metric_type' => $this->metric->value,
            'winner_type' => $this->winnerType->value,
            'final_rank' => $this->rank,
            'final_score' => $this->score,
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'prize_id' => $this->prizeId,
        ];
    }
}
