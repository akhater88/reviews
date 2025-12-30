<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInternalCompetition extends EditRecord
{
    protected static string $resource = InternalCompetitionResource::class;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
