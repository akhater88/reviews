<?php

namespace App\Jobs\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\TenantEnrollmentMode;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\Tenant;
use App\Services\InternalCompetition\ParticipantService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoEnrollNewTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $tenantId
    ) {}

    public function handle(ParticipantService $participantService): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (!$tenant || !$tenant->is_active) {
            return;
        }

        // Find all active multi-tenant competitions with AUTO_NEW enrollment mode
        $competitions = InternalCompetition::where('status', CompetitionStatus::ACTIVE)
            ->where('scope', CompetitionScope::MULTI_TENANT)
            ->where('tenant_enrollment_mode', TenantEnrollmentMode::AUTO_NEW)
            ->get();

        foreach ($competitions as $competition) {
            // Check if tenant is already enrolled
            $alreadyEnrolled = $competition->participatingTenants()
                ->where('tenant_id', $this->tenantId)
                ->exists();

            if ($alreadyEnrolled) {
                continue;
            }

            try {
                // Use system user ID (1) or null for auto-enrollment
                $enrolledById = 1;

                $participantService->enrollTenant($competition, $this->tenantId, $enrolledById);
                $participantService->enrollAllTenantBranches($competition, $this->tenantId, $enrolledById);

                logger()->info("Auto-enrolled new tenant {$this->tenantId} in competition {$competition->id}");
            } catch (\Exception $e) {
                logger()->error("Failed to auto-enroll tenant {$this->tenantId} in competition {$competition->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function tags(): array
    {
        return ['internal-competition', 'auto-enroll', "tenant:{$this->tenantId}"];
    }
}
