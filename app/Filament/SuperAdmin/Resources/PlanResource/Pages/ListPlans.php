<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\Pages;

use App\Filament\SuperAdmin\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('compare')
                ->label('مقارنة الباقات')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(fn () => route('filament.super-admin.pages.compare-plans')),

            Actions\CreateAction::make()
                ->label('إضافة باقة')
                ->icon('heroicon-o-plus'),
        ];
    }
}
