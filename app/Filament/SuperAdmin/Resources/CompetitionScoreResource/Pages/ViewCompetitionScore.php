<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionScoreResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompetitionScore extends ViewRecord
{
    protected static string $resource = CompetitionScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
