<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionBranchResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompetitionBranch extends EditRecord
{
    protected static string $resource = CompetitionBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
