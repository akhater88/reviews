<?php

namespace App\Services\InternalCompetition;

use App\DTOs\InternalCompetition\CreateCompetitionDTO;
use App\DTOs\InternalCompetition\UpdateCompetitionDTO;
use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Events\InternalCompetition\CompetitionActivated;
use App\Events\InternalCompetition\CompetitionCancelled;
use App\Events\InternalCompetition\CompetitionCreated;
use App\Events\InternalCompetition\CompetitionEnded;
use App\Events\InternalCompetition\CompetitionPublished;
use App\Exceptions\InternalCompetition\CompetitionException;
use App\Exceptions\InternalCompetition\InsufficientParticipantsException;
use App\Exceptions\InternalCompetition\InvalidCompetitionStateException;
use App\Models\InternalCompetition\InternalCompetition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompetitionService
{
    public function __construct(
        protected ParticipantService $participantService
    ) {}

    // ==================== QUERY METHODS ====================

    /**
     * Get competition by ID
     */
    public function findById(int $id): ?InternalCompetition
    {
        return InternalCompetition::find($id);
    }

    /**
     * Get competition by UUID
     */
    public function findByUuid(string $uuid): ?InternalCompetition
    {
        return InternalCompetition::where('uuid', $uuid)->first();
    }

    /**
     * Get competition by ID or throw exception
     */
    public function findOrFail(int $id): InternalCompetition
    {
        $competition = $this->findById($id);

        if (!$competition) {
            throw CompetitionException::notFound($id);
        }

        return $competition;
    }

    /**
     * Get all competitions (for super admin)
     */
    public function getAllCompetitions(
        ?CompetitionStatus $status = null,
        ?CompetitionScope $scope = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = InternalCompetition::with(['createdBy', 'tenant'])
            ->withCount(['activeBranches', 'activeTenants']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($scope) {
            $query->where('scope', $scope);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get competitions for a specific tenant
     */
    public function getCompetitionsForTenant(
        int $tenantId,
        ?CompetitionStatus $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = InternalCompetition::forTenant($tenantId)
            ->with(['createdBy', 'tenant'])
            ->withCount(['activeBranches', 'activeTenants']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get active competitions
     */
    public function getActiveCompetitions(): Collection
    {
        return InternalCompetition::active()
            ->with(['activeBranches', 'activeTenants'])
            ->get();
    }

    /**
     * Get competitions ending soon (within specified days)
     */
    public function getCompetitionsEndingSoon(int $days = 3): Collection
    {
        return InternalCompetition::active()
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->with(['activeBranches', 'activeTenants'])
            ->get();
    }

    /**
     * Get competitions that should end today
     */
    public function getCompetitionsEndingToday(): Collection
    {
        return InternalCompetition::active()
            ->whereDate('end_date', today())
            ->get();
    }

    /**
     * Get draft competitions that should start today
     */
    public function getCompetitionsStartingToday(): Collection
    {
        return InternalCompetition::draft()
            ->whereDate('start_date', today())
            ->get();
    }

    // ==================== CREATE / UPDATE ====================

    /**
     * Create a new competition
     */
    public function create(CreateCompetitionDTO $dto): InternalCompetition
    {
        // Validate
        $this->validateDates($dto->startDate, $dto->endDate);
        $this->validateMetricsConfig($dto->metricsConfig);

        // For single tenant, tenant_id is required
        if ($dto->scope === CompetitionScope::SINGLE_TENANT && !$dto->tenantId) {
            throw CompetitionException::unauthorized();
        }

        try {
            DB::beginTransaction();

            $competition = InternalCompetition::create([
                'name' => $dto->name,
                'name_ar' => $dto->nameAr,
                'description' => $dto->description,
                'description_ar' => $dto->descriptionAr,
                'scope' => $dto->scope,
                'period_type' => $dto->periodType,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'metrics_config' => $dto->metricsConfig,
                'leaderboard_visibility' => $dto->leaderboardVisibility,
                'show_progress_hints' => $dto->showProgressHints,
                'public_showcase' => $dto->publicShowcase,
                'notification_settings' => $dto->notificationSettings,
                'created_by_id' => $dto->createdById,
                'created_by_type' => $dto->createdByType,
                'tenant_id' => $dto->tenantId,
                'cover_image' => $dto->coverImage,
                'status' => CompetitionStatus::DRAFT,
            ]);

            DB::commit();

            Log::info('Competition created', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
                'scope' => $competition->scope->value,
                'created_by' => $dto->createdById,
            ]);

            event(new CompetitionCreated($competition));

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create competition', [
                'error' => $e->getMessage(),
                'dto' => $dto->toArray(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a competition (only allowed in draft status)
     */
    public function update(InternalCompetition $competition, UpdateCompetitionDTO $dto): InternalCompetition
    {
        // Can only update draft competitions
        if (!$competition->status->canEdit()) {
            throw CompetitionException::cannotModify($competition->status->getLabel());
        }

        // Validate dates if provided
        $startDate = $dto->startDate ?? $competition->start_date;
        $endDate = $dto->endDate ?? $competition->end_date;
        $this->validateDates($startDate, $endDate, allowPast: false);

        // Validate metrics if provided
        if ($dto->metricsConfig !== null) {
            $this->validateMetricsConfig($dto->metricsConfig);
        }

        try {
            DB::beginTransaction();

            $updateData = $dto->toArray();

            if (!empty($updateData)) {
                $competition->update($updateData);
            }

            DB::commit();

            Log::info('Competition updated', [
                'competition_id' => $competition->id,
                'changes' => array_keys($updateData),
            ]);

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ==================== STATE TRANSITIONS ====================

    /**
     * Activate a competition (draft -> active)
     */
    public function activate(InternalCompetition $competition): InternalCompetition
    {
        // Validate current status
        if (!$competition->status->canActivate()) {
            throw InvalidCompetitionStateException::cannotActivate($competition->status);
        }

        // Validate has participants
        $this->validateHasParticipants($competition);

        // Validate has at least one metric enabled
        if (empty($competition->enabled_metrics)) {
            throw CompetitionException::noMetricsEnabled();
        }

        try {
            DB::beginTransaction();

            $competition->update([
                'status' => CompetitionStatus::ACTIVE,
                'activated_at' => now(),
            ]);

            DB::commit();

            Log::info('Competition activated', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
                'branches_count' => $competition->activeBranches()->count(),
            ]);

            event(new CompetitionActivated($competition));

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * End a competition (active -> calculating -> ended)
     */
    public function end(InternalCompetition $competition): InternalCompetition
    {
        if ($competition->status !== CompetitionStatus::ACTIVE) {
            throw InvalidCompetitionStateException::cannotEnd($competition->status);
        }

        try {
            DB::beginTransaction();

            // First, set to calculating status
            $competition->update([
                'status' => CompetitionStatus::CALCULATING,
            ]);

            // Note: Actual score calculation will be done by a separate job
            // Here we just mark it as ended after calculation
            $competition->update([
                'status' => CompetitionStatus::ENDED,
                'ended_at' => now(),
            ]);

            DB::commit();

            Log::info('Competition ended', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
            ]);

            event(new CompetitionEnded($competition));

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to end competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Publish competition results (ended -> published)
     */
    public function publish(InternalCompetition $competition): InternalCompetition
    {
        if (!$competition->status->canPublish()) {
            throw InvalidCompetitionStateException::cannotPublish($competition->status);
        }

        try {
            DB::beginTransaction();

            $competition->update([
                'status' => CompetitionStatus::PUBLISHED,
                'published_at' => now(),
            ]);

            DB::commit();

            Log::info('Competition published', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
            ]);

            event(new CompetitionPublished($competition));

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a competition
     */
    public function cancel(InternalCompetition $competition, ?string $reason = null): InternalCompetition
    {
        if (!$competition->status->canCancel()) {
            throw InvalidCompetitionStateException::cannotCancel($competition->status);
        }

        try {
            DB::beginTransaction();

            $competition->update([
                'status' => CompetitionStatus::CANCELLED,
            ]);

            DB::commit();

            Log::info('Competition cancelled', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
                'reason' => $reason,
            ]);

            event(new CompetitionCancelled($competition, $reason));

            return $competition->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ==================== DELETE ====================

    /**
     * Delete a competition (soft delete, only draft or cancelled)
     */
    public function delete(InternalCompetition $competition): bool
    {
        if (!in_array($competition->status, [CompetitionStatus::DRAFT, CompetitionStatus::CANCELLED])) {
            throw CompetitionException::cannotModify($competition->status->getLabel());
        }

        try {
            DB::beginTransaction();

            // Delete related records
            $competition->participatingTenants()->delete();
            $competition->participatingBranches()->delete();
            $competition->prizes()->delete();

            // Soft delete the competition
            $competition->delete();

            DB::commit();

            Log::info('Competition deleted', [
                'competition_id' => $competition->id,
                'name' => $competition->name,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete competition', [
                'competition_id' => $competition->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ==================== VALIDATION HELPERS ====================

    /**
     * Validate start and end dates
     */
    protected function validateDates($startDate, $endDate, bool $allowPast = false): void
    {
        if ($startDate >= $endDate) {
            throw CompetitionException::invalidDateRange();
        }

        if (!$allowPast && $startDate->isPast()) {
            throw CompetitionException::startDateInPast();
        }
    }

    /**
     * Validate metrics configuration
     */
    protected function validateMetricsConfig(array $metricsConfig): void
    {
        // Check for enabled_metrics array structure (current format)
        if (isset($metricsConfig['enabled_metrics']) && is_array($metricsConfig['enabled_metrics'])) {
            if (empty($metricsConfig['enabled_metrics'])) {
                throw CompetitionException::noMetricsEnabled();
            }
            return;
        }

        // Fallback: Check for legacy format ['metric' => ['enabled' => true]]
        $hasEnabledMetric = false;
        foreach ($metricsConfig as $metric => $config) {
            if ($config['enabled'] ?? false) {
                $hasEnabledMetric = true;
                break;
            }
        }

        if (!$hasEnabledMetric) {
            throw CompetitionException::noMetricsEnabled();
        }
    }

    /**
     * Validate competition has sufficient participants
     */
    protected function validateHasParticipants(InternalCompetition $competition): void
    {
        $branchCount = $competition->activeBranches()->count();

        if ($branchCount === 0) {
            throw InsufficientParticipantsException::noBranches();
        }

        // For multi-tenant, ensure at least one tenant is enrolled
        if ($competition->is_multi_tenant) {
            $tenantCount = $competition->activeTenants()->count();
            if ($tenantCount === 0) {
                throw InsufficientParticipantsException::noTenants();
            }
        }

        // Require minimum 2 branches for meaningful competition
        if ($branchCount < 2) {
            throw InsufficientParticipantsException::minimumBranches(2, $branchCount);
        }
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Check if user can manage competition
     */
    public function canManage(InternalCompetition $competition, int $userId, ?int $tenantId = null): bool
    {
        // Super admin (created by super admin and no tenant_id)
        if ($competition->created_by_type === 'super_admin') {
            return $competition->created_by_id === $userId;
        }

        // Tenant admin - must belong to the same tenant
        if ($tenantId && $competition->tenant_id === $tenantId) {
            return true;
        }

        return false;
    }

    /**
     * Get competition statistics
     */
    public function getStatistics(InternalCompetition $competition): array
    {
        return [
            'total_branches' => $competition->activeBranches()->count(),
            'total_tenants' => $competition->is_multi_tenant
                ? $competition->activeTenants()->count()
                : 1,
            'total_employees' => $competition->employees()->count(),
            'total_prizes' => $competition->prizes()->count(),
            'days_remaining' => $competition->remaining_days,
            'progress_percentage' => $competition->progress_percentage,
            'is_active' => $competition->status === CompetitionStatus::ACTIVE,
            'is_ended' => $competition->status->isCompleted(),
        ];
    }

    /**
     * Duplicate a competition (for creating similar competitions)
     */
    public function duplicate(
        InternalCompetition $competition,
        int $createdById,
        string $createdByType,
        ?string $newName = null
    ): InternalCompetition {
        $dto = CreateCompetitionDTO::fromArray([
            'name' => $newName ?? $competition->name . ' (نسخة)',
            'name_ar' => $competition->name_ar ? $competition->name_ar . ' (نسخة)' : null,
            'description' => $competition->description,
            'description_ar' => $competition->description_ar,
            'scope' => $competition->scope,
            'period_type' => $competition->period_type,
            'start_date' => now()->addDay(), // Start tomorrow
            'end_date' => now()->addDay()->addDays($competition->duration_in_days),
            'metrics_config' => $competition->metrics_config,
            'leaderboard_visibility' => $competition->leaderboard_visibility,
            'show_progress_hints' => $competition->show_progress_hints,
            'public_showcase' => $competition->public_showcase,
            'notification_settings' => $competition->notification_settings,
            'created_by_id' => $createdById,
            'created_by_type' => $createdByType,
            'tenant_id' => $competition->tenant_id,
        ]);

        $newCompetition = $this->create($dto);

        // Duplicate prizes
        foreach ($competition->prizes as $prize) {
            $newCompetition->prizes()->create([
                'metric_type' => $prize->metric_type,
                'rank' => $prize->rank,
                'name' => $prize->name,
                'name_ar' => $prize->name_ar,
                'description' => $prize->description,
                'description_ar' => $prize->description_ar,
                'prize_type' => $prize->prize_type,
                'estimated_value' => $prize->estimated_value,
                'currency' => $prize->currency,
                'physical_details' => $prize->physical_details,
            ]);
        }

        // Duplicate benchmarks
        foreach ($competition->benchmarks as $benchmark) {
            $newCompetition->benchmarks()->create([
                'tenant_id' => $benchmark->tenant_id,
                'branch_id' => $benchmark->branch_id,
                'period_type' => $benchmark->period_type,
                'period_start' => $benchmark->period_start,
                'period_end' => $benchmark->period_end,
                'metrics' => $benchmark->metrics,
                'calculated_at' => $benchmark->calculated_at,
            ]);
        }

        return $newCompetition;
    }
}
