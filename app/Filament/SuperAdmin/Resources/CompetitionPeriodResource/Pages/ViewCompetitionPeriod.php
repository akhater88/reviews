<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompetitionPeriod extends ViewRecord
{
    protected static string $resource = CompetitionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
