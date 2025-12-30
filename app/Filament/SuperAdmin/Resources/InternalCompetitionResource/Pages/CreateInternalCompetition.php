<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInternalCompetition extends CreateRecord
{
    protected static string $resource = InternalCompetitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

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
}
