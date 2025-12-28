<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionParticipantResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetitionParticipants extends ListRecords
{
    protected static string $resource = CompetitionParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
