<?php

namespace App\Services\InternalCompetition;

use App\DTOs\InternalCompetition\EnrollParticipantDTO;
use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\ParticipantStatus;
use App\Events\InternalCompetition\ParticipantEnrolled;
use App\Events\InternalCompetition\ParticipantWithdrawn;
use App\Exceptions\InternalCompetition\ParticipantException;
use App\Models\Branch;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\InternalCompetition\InternalCompetitionTenant;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParticipantService
{
    // ==================== TENANT ENROLLMENT ====================

    /**
     * Enroll a tenant in a multi-tenant competition
     */
    public function enrollTenant(
        InternalCompetition $competition,
        int $tenantId,
        int $enrolledById
    ): InternalCompetitionTenant {
        // Validate competition scope
        if ($competition->scope !== CompetitionScope::MULTI_TENANT) {
            throw new \InvalidArgumentException('يمكن تسجيل المستأجرين فقط في المسابقات متعددة المستأجرين');
        }

        // Validate competition status allows enrollment
        $this->validateCanEnroll($competition);

        // Check tenant exists
        $tenant = Tenant::findOrFail($tenantId);

        // Check if already enrolled
        $existing = $competition->participatingTenants()
            ->where('tenant_id', $tenantId)
            ->first();

        if ($existing) {
            if ($existing->status === ParticipantStatus::ACTIVE) {
                throw ParticipantException::tenantAlreadyEnrolled($tenantId);
            }

            // Re-enroll if previously withdrawn
            $existing->update([
                'status' => ParticipantStatus::ACTIVE,
                'enrolled_at' => now(),
                'enrolled_by_id' => $enrolledById,
                'withdrawn_at' => null,
                'withdrawal_reason' => null,
            ]);

            Log::info('Tenant re-enrolled in competition', [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'enrolled_by' => $enrolledById,
            ]);

            event(new ParticipantEnrolled($competition, $existing, 'tenant'));

            return $existing->fresh();
        }

        try {
            DB::beginTransaction();

            $participant = InternalCompetitionTenant::create([
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'enrolled_at' => now(),
                'enrolled_by_id' => $enrolledById,
                'status' => ParticipantStatus::ACTIVE,
            ]);

            DB::commit();

            Log::info('Tenant enrolled in competition', [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'enrolled_by' => $enrolledById,
            ]);

            event(new ParticipantEnrolled($competition, $participant, 'tenant'));

            return $participant;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to enroll tenant', [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Withdraw a tenant from a competition
     */
    public function withdrawTenant(
        InternalCompetition $competition,
        int $tenantId,
        ?string $reason = null
    ): InternalCompetitionTenant {
        $this->validateCanWithdraw($competition);

        $participant = $competition->participatingTenants()
            ->where('tenant_id', $tenantId)
            ->where('status', ParticipantStatus::ACTIVE)
            ->first();

        if (!$participant) {
            throw ParticipantException::tenantNotEnrolled($tenantId);
        }

        try {
            DB::beginTransaction();

            $participant->update([
                'status' => ParticipantStatus::WITHDRAWN,
                'withdrawn_at' => now(),
                'withdrawal_reason' => $reason,
            ]);

            // Also withdraw all branches of this tenant
            $competition->participatingBranches()
                ->where('tenant_id', $tenantId)
                ->where('status', ParticipantStatus::ACTIVE)
                ->update([
                    'status' => ParticipantStatus::WITHDRAWN,
                    'withdrawn_at' => now(),
                    'withdrawal_reason' => 'انسحاب المستأجر',
                ]);

            DB::commit();

            Log::info('Tenant withdrawn from competition', [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'reason' => $reason,
            ]);

            event(new ParticipantWithdrawn($competition, $participant, 'tenant', $reason));

            return $participant->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to withdraw tenant', [
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Enroll multiple tenants at once
     */
    public function enrollMultipleTenants(
        InternalCompetition $competition,
        array $tenantIds,
        int $enrolledById
    ): Collection {
        $enrolled = collect();

        foreach ($tenantIds as $tenantId) {
            try {
                $participant = $this->enrollTenant($competition, $tenantId, $enrolledById);
                $enrolled->push($participant);
            } catch (ParticipantException $e) {
                // Log and continue with next tenant
                Log::warning('Skipped tenant enrollment', [
                    'competition_id' => $competition->id,
                    'tenant_id' => $tenantId,
                    'reason' => $e->getMessage(),
                ]);
            }
        }

        return $enrolled;
    }

    // ==================== BRANCH ENROLLMENT ====================

    /**
     * Enroll a branch in a competition
     */
    public function enrollBranch(
        InternalCompetition $competition,
        int $branchId,
        int $enrolledById
    ): InternalCompetitionBranch {
        $this->validateCanEnroll($competition);

        // Get branch and validate
        $branch = Branch::findOrFail($branchId);
        $tenantId = $branch->tenant_id;

        // For multi-tenant competitions, ensure tenant is enrolled first
        if ($competition->scope === CompetitionScope::MULTI_TENANT) {
            $tenantEnrolled = $competition->participatingTenants()
                ->where('tenant_id', $tenantId)
                ->where('status', ParticipantStatus::ACTIVE)
                ->exists();

            if (!$tenantEnrolled) {
                throw ParticipantException::tenantNotInCompetition($tenantId);
            }
        }

        // For single-tenant competitions, ensure branch belongs to the competition's tenant
        if ($competition->scope === CompetitionScope::SINGLE_TENANT) {
            if ($branch->tenant_id !== $competition->tenant_id) {
                throw ParticipantException::branchNotBelongsToTenant($branchId, $competition->tenant_id);
            }
        }

        // Check if already enrolled
        $existing = $competition->participatingBranches()
            ->where('branch_id', $branchId)
            ->first();

        if ($existing) {
            if ($existing->status === ParticipantStatus::ACTIVE) {
                throw ParticipantException::branchAlreadyEnrolled($branchId);
            }

            // Re-enroll if previously withdrawn
            $existing->update([
                'status' => ParticipantStatus::ACTIVE,
                'enrolled_at' => now(),
                'enrolled_by_id' => $enrolledById,
                'withdrawn_at' => null,
                'withdrawal_reason' => null,
            ]);

            Log::info('Branch re-enrolled in competition', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'tenant_id' => $tenantId,
            ]);

            event(new ParticipantEnrolled($competition, $existing, 'branch'));

            return $existing->fresh();
        }

        try {
            DB::beginTransaction();

            $participant = InternalCompetitionBranch::create([
                'competition_id' => $competition->id,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'enrolled_at' => now(),
                'enrolled_by_id' => $enrolledById,
                'status' => ParticipantStatus::ACTIVE,
            ]);

            DB::commit();

            Log::info('Branch enrolled in competition', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'tenant_id' => $tenantId,
            ]);

            event(new ParticipantEnrolled($competition, $participant, 'branch'));

            return $participant;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to enroll branch', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Withdraw a branch from a competition
     */
    public function withdrawBranch(
        InternalCompetition $competition,
        int $branchId,
        ?string $reason = null
    ): InternalCompetitionBranch {
        $this->validateCanWithdraw($competition);

        $participant = $competition->participatingBranches()
            ->where('branch_id', $branchId)
            ->where('status', ParticipantStatus::ACTIVE)
            ->first();

        if (!$participant) {
            throw ParticipantException::branchNotEnrolled($branchId);
        }

        try {
            DB::beginTransaction();

            $participant->update([
                'status' => ParticipantStatus::WITHDRAWN,
                'withdrawn_at' => now(),
                'withdrawal_reason' => $reason,
            ]);

            DB::commit();

            Log::info('Branch withdrawn from competition', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'reason' => $reason,
            ]);

            event(new ParticipantWithdrawn($competition, $participant, 'branch', $reason));

            return $participant->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to withdraw branch', [
                'competition_id' => $competition->id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Enroll multiple branches at once
     */
    public function enrollMultipleBranches(
        InternalCompetition $competition,
        array $branchIds,
        int $enrolledById
    ): Collection {
        $enrolled = collect();

        foreach ($branchIds as $branchId) {
            try {
                $participant = $this->enrollBranch($competition, $branchId, $enrolledById);
                $enrolled->push($participant);
            } catch (ParticipantException $e) {
                Log::warning('Skipped branch enrollment', [
                    'competition_id' => $competition->id,
                    'branch_id' => $branchId,
                    'reason' => $e->getMessage(),
                ]);
            }
        }

        return $enrolled;
    }

    /**
     * Enroll all branches of a tenant
     */
    public function enrollAllTenantBranches(
        InternalCompetition $competition,
        int $tenantId,
        int $enrolledById
    ): Collection {
        $branches = Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        return $this->enrollMultipleBranches($competition, $branches, $enrolledById);
    }

    // ==================== QUERY METHODS ====================

    /**
     * Get all participating tenants for a competition
     */
    public function getParticipatingTenants(
        InternalCompetition $competition,
        bool $activeOnly = true
    ): Collection {
        $query = $competition->participatingTenants()->with('tenant');

        if ($activeOnly) {
            $query->where('status', ParticipantStatus::ACTIVE);
        }

        return $query->get();
    }

    /**
     * Get all participating branches for a competition
     */
    public function getParticipatingBranches(
        InternalCompetition $competition,
        ?int $tenantId = null,
        bool $activeOnly = true
    ): Collection {
        $query = $competition->participatingBranches()->with(['branch', 'tenant']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($activeOnly) {
            $query->where('status', ParticipantStatus::ACTIVE);
        }

        return $query->get();
    }

    /**
     * Get available branches for enrollment (not yet enrolled)
     */
    public function getAvailableBranchesForEnrollment(
        InternalCompetition $competition,
        int $tenantId
    ): Collection {
        $enrolledBranchIds = $competition->participatingBranches()
            ->where('tenant_id', $tenantId)
            ->where('status', ParticipantStatus::ACTIVE)
            ->pluck('branch_id');

        return Branch::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledBranchIds)
            ->get();
    }

    /**
     * Get available tenants for enrollment (not yet enrolled)
     */
    public function getAvailableTenantsForEnrollment(InternalCompetition $competition): Collection
    {
        $enrolledTenantIds = $competition->participatingTenants()
            ->where('status', ParticipantStatus::ACTIVE)
            ->pluck('tenant_id');

        return Tenant::where('is_active', true)
            ->whereNotIn('id', $enrolledTenantIds)
            ->get();
    }

    /**
     * Check if a branch is enrolled in a competition
     */
    public function isBranchEnrolled(InternalCompetition $competition, int $branchId): bool
    {
        return $competition->participatingBranches()
            ->where('branch_id', $branchId)
            ->where('status', ParticipantStatus::ACTIVE)
            ->exists();
    }

    /**
     * Check if a tenant is enrolled in a competition
     */
    public function isTenantEnrolled(InternalCompetition $competition, int $tenantId): bool
    {
        // For single-tenant, check if it's the competition's tenant
        if ($competition->scope === CompetitionScope::SINGLE_TENANT) {
            return $competition->tenant_id === $tenantId;
        }

        // For multi-tenant, check participating tenants
        return $competition->participatingTenants()
            ->where('tenant_id', $tenantId)
            ->where('status', ParticipantStatus::ACTIVE)
            ->exists();
    }

    /**
     * Get participant counts
     */
    public function getParticipantCounts(InternalCompetition $competition): array
    {
        return [
            'total_tenants' => $competition->scope === CompetitionScope::MULTI_TENANT
                ? $competition->participatingTenants()->where('status', ParticipantStatus::ACTIVE)->count()
                : 1,
            'total_branches' => $competition->participatingBranches()
                ->where('status', ParticipantStatus::ACTIVE)
                ->count(),
            'withdrawn_tenants' => $competition->participatingTenants()
                ->where('status', ParticipantStatus::WITHDRAWN)
                ->count(),
            'withdrawn_branches' => $competition->participatingBranches()
                ->where('status', ParticipantStatus::WITHDRAWN)
                ->count(),
        ];
    }

    // ==================== VALIDATION HELPERS ====================

    /**
     * Validate that enrollment is allowed
     */
    protected function validateCanEnroll(InternalCompetition $competition): void
    {
        // Can only enroll in draft or active competitions
        if (!in_array($competition->status, [CompetitionStatus::DRAFT, CompetitionStatus::ACTIVE])) {
            throw ParticipantException::cannotEnrollAfterStart();
        }
    }

    /**
     * Validate that withdrawal is allowed
     */
    protected function validateCanWithdraw(InternalCompetition $competition): void
    {
        // Cannot withdraw after competition has ended
        if ($competition->status->isCompleted()) {
            throw ParticipantException::cannotWithdrawAfterEnd();
        }
    }

    // ==================== BULK OPERATIONS ====================

    /**
     * Sync branches for a competition (add new, remove missing)
     */
    public function syncBranches(
        InternalCompetition $competition,
        array $branchIds,
        int $enrolledById
    ): array {
        $currentBranchIds = $competition->participatingBranches()
            ->where('status', ParticipantStatus::ACTIVE)
            ->pluck('branch_id')
            ->toArray();

        $toAdd = array_diff($branchIds, $currentBranchIds);
        $toRemove = array_diff($currentBranchIds, $branchIds);

        $added = [];
        $removed = [];

        // Add new branches
        foreach ($toAdd as $branchId) {
            try {
                $participant = $this->enrollBranch($competition, $branchId, $enrolledById);
                $added[] = $branchId;
            } catch (\Exception $e) {
                Log::warning('Failed to add branch during sync', [
                    'branch_id' => $branchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove branches
        foreach ($toRemove as $branchId) {
            try {
                $this->withdrawBranch($competition, $branchId, 'تم الإزالة أثناء المزامنة');
                $removed[] = $branchId;
            } catch (\Exception $e) {
                Log::warning('Failed to remove branch during sync', [
                    'branch_id' => $branchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'unchanged' => array_intersect($branchIds, $currentBranchIds),
        ];
    }

    /**
     * Sync tenants for a multi-tenant competition
     */
    public function syncTenants(
        InternalCompetition $competition,
        array $tenantIds,
        int $enrolledById
    ): array {
        if ($competition->scope !== CompetitionScope::MULTI_TENANT) {
            throw new \InvalidArgumentException('مزامنة المستأجرين متاحة فقط للمسابقات متعددة المستأجرين');
        }

        $currentTenantIds = $competition->participatingTenants()
            ->where('status', ParticipantStatus::ACTIVE)
            ->pluck('tenant_id')
            ->toArray();

        $toAdd = array_diff($tenantIds, $currentTenantIds);
        $toRemove = array_diff($currentTenantIds, $tenantIds);

        $added = [];
        $removed = [];

        // Add new tenants
        foreach ($toAdd as $tenantId) {
            try {
                $this->enrollTenant($competition, $tenantId, $enrolledById);
                $added[] = $tenantId;
            } catch (\Exception $e) {
                Log::warning('Failed to add tenant during sync', [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove tenants (and their branches)
        foreach ($toRemove as $tenantId) {
            try {
                $this->withdrawTenant($competition, $tenantId, 'تم الإزالة أثناء المزامنة');
                $removed[] = $tenantId;
            } catch (\Exception $e) {
                Log::warning('Failed to remove tenant during sync', [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'unchanged' => array_intersect($tenantIds, $currentTenantIds),
        ];
    }
}
