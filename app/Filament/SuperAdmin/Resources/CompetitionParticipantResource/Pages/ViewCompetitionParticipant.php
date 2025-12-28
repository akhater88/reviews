<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionParticipantResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompetitionParticipant extends ViewRecord
{
    protected static string $resource = CompetitionParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
