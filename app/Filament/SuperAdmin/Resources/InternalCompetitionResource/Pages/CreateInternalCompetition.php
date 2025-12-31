<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\TenantEnrollmentMode;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use App\Services\InternalCompetition\ParticipantService;
use Filament\Resources\Pages\CreateRecord;

class CreateInternalCompetition extends CreateRecord
{
    protected static string $resource = InternalCompetitionResource::class;

    // Store selected tenant IDs temporarily
    public array $selectedTenantIds = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();

        // Store selected tenant IDs for after creation
        $this->selectedTenantIds = $data['selected_tenant_ids'] ?? [];

        // Remove from data as it's not a database column
        unset($data['selected_tenant_ids']);

        // Set default notification settings if not provided
        if (!isset($data['notification_settings'])) {
            $data['notification_settings'] = [
                'whatsapp' => ['enabled' => true],
                'email' => ['enabled' => true],
                'events' => ['start', 'ended', 'winner'],
                'reminder_days' => [7, 3, 1],
            ];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $competition = $this->record;
        $participantService = app(ParticipantService::class);
        $enrolledById = auth()->id();

        // Handle multi-tenant enrollment
        if ($competition->scope === CompetitionScope::MULTI_TENANT) {
            $enrollmentMode = $competition->tenant_enrollment_mode;

            if ($enrollmentMode === TenantEnrollmentMode::MANUAL) {
                // Enroll only selected tenants
                foreach ($this->selectedTenantIds as $tenantId) {
                    try {
                        $participantService->enrollTenant($competition, $tenantId, $enrolledById);
                        // Optionally auto-enroll all branches of each tenant
                        $participantService->enrollAllTenantBranches($competition, $tenantId, $enrolledById);
                    } catch (\Exception $e) {
                        // Log error but continue with other tenants
                        logger()->error("Failed to enroll tenant {$tenantId}", ['error' => $e->getMessage()]);
                    }
                }
            } elseif (in_array($enrollmentMode, [TenantEnrollmentMode::AUTO_ALL, TenantEnrollmentMode::AUTO_NEW])) {
                // Auto-enroll all active tenants
                $tenants = \App\Models\Tenant::where('is_active', true)->get();
                foreach ($tenants as $tenant) {
                    try {
                        $participantService->enrollTenant($competition, $tenant->id, $enrolledById);
                        $participantService->enrollAllTenantBranches($competition, $tenant->id, $enrolledById);
                    } catch (\Exception $e) {
                        logger()->error("Failed to auto-enroll tenant {$tenant->id}", ['error' => $e->getMessage()]);
                    }
                }
            }
        }
    }
}
