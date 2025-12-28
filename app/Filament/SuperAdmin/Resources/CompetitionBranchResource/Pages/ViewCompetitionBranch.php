<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionBranchResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCompetitionBranch extends ViewRecord
{
    protected static string $resource = CompetitionBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
