<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionWinnerResource\Pages;

use App\Filament\SuperAdmin\Resources\InternalCompetitionWinnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalCompetitionWinner extends EditRecord
{
    protected static string $resource = InternalCompetitionWinnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
