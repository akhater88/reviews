<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\TenantEnrollmentMode;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use App\Services\InternalCompetition\ParticipantService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInternalCompetition extends EditRecord
{
    protected static string $resource = InternalCompetitionResource::class;

    public array $selectedTenantIds = [];
    public array $originalTenantIds = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => in_array(
                    $this->record->status,
                    [CompetitionStatus::DRAFT, CompetitionStatus::CANCELLED]
                )),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load currently enrolled tenant IDs for the form
        if ($this->record->scope === CompetitionScope::MULTI_TENANT) {
            $enrolledTenantIds = $this->record->participatingTenants()
                ->pluck('tenant_id')
                ->toArray();

            $data['selected_tenant_ids'] = $enrolledTenantIds;
            $this->originalTenantIds = $enrolledTenantIds;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store selected tenant IDs for after save
        $this->selectedTenantIds = $data['selected_tenant_ids'] ?? [];

        // Remove from data as it's not a database column
        unset($data['selected_tenant_ids']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Prevent editing if not in draft status
        if (!$this->record->status->canEdit()) {
            Notification::make()
                ->title('لا يمكن التعديل')
                ->body('لا يمكن تعديل المسابقة في هذه الحالة')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $competition = $this->record;

        // Only handle tenant changes for multi-tenant competitions in draft status
        if ($competition->scope !== CompetitionScope::MULTI_TENANT) {
            return;
        }

        if ($competition->tenant_enrollment_mode !== TenantEnrollmentMode::MANUAL) {
            return;
        }

        if (!$competition->status->canEdit()) {
            return;
        }

        $participantService = app(ParticipantService::class);
        $enrolledById = auth()->id();

        // Find tenants to remove (were enrolled, now not selected)
        $tenantsToRemove = array_diff($this->originalTenantIds, $this->selectedTenantIds);
        foreach ($tenantsToRemove as $tenantId) {
            try {
                $participantService->withdrawTenant($competition, $tenantId);
            } catch (\Exception $e) {
                logger()->error("Failed to withdraw tenant {$tenantId}", ['error' => $e->getMessage()]);
            }
        }

        // Find tenants to add (selected, but not yet enrolled)
        $tenantsToAdd = array_diff($this->selectedTenantIds, $this->originalTenantIds);
        foreach ($tenantsToAdd as $tenantId) {
            try {
                $participantService->enrollTenant($competition, $tenantId, $enrolledById);
                $participantService->enrollAllTenantBranches($competition, $tenantId, $enrolledById);
            } catch (\Exception $e) {
                logger()->error("Failed to enroll tenant {$tenantId}", ['error' => $e->getMessage()]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
