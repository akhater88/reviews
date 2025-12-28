<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionBranchResource\Pages;

use App\Filament\SuperAdmin\Resources\CompetitionBranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompetitionBranches extends ListRecords
{
    protected static string $resource = CompetitionBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
