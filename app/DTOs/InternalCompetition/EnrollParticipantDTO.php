<?php

namespace App\DTOs\InternalCompetition;

class EnrollParticipantDTO
{
    public function __construct(
        public readonly int $competitionId,
        public readonly int $enrolledById,
        public readonly ?int $tenantId = null,
        public readonly ?int $branchId = null,
        public readonly array $branchIds = [],
    ) {}

    public static function forTenant(int $competitionId, int $tenantId, int $enrolledById): self
    {
        return new self(
            competitionId: $competitionId,
            enrolledById: $enrolledById,
            tenantId: $tenantId,
        );
    }

    public static function forBranch(int $competitionId, int $tenantId, int $branchId, int $enrolledById): self
    {
        return new self(
            competitionId: $competitionId,
            enrolledById: $enrolledById,
            tenantId: $tenantId,
            branchId: $branchId,
        );
    }

    public static function forMultipleBranches(int $competitionId, int $tenantId, array $branchIds, int $enrolledById): self
    {
        return new self(
            competitionId: $competitionId,
            enrolledById: $enrolledById,
            tenantId: $tenantId,
            branchIds: $branchIds,
        );
    }

    public function isTenantEnrollment(): bool
    {
        return $this->tenantId !== null && $this->branchId === null && empty($this->branchIds);
    }

    public function isSingleBranchEnrollment(): bool
    {
        return $this->branchId !== null;
    }

    public function isMultipleBranchEnrollment(): bool
    {
        return !empty($this->branchIds);
    }
}
