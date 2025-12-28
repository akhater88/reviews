<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetitionPeriod extends EditRecord
{
    protected static string $resource = CompetitionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
