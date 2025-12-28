<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionParticipantResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetitionParticipant extends EditRecord
{
    protected static string $resource = CompetitionParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
