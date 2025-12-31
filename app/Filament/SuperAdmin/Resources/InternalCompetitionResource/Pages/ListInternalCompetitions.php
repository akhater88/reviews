<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use App\Models\InternalCompetition\InternalCompetition;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInternalCompetitions extends ListRecords
{
    protected static string $resource = InternalCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء مسابقة جديدة'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(InternalCompetition::count()),

            'active' => Tab::make('النشطة')
                ->badge(InternalCompetition::active()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->active()),

            'draft' => Tab::make('مسودة')
                ->badge(InternalCompetition::draft()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->draft()),

            'ended' => Tab::make('منتهية')
                ->badge(InternalCompetition::where('status', CompetitionStatus::ENDED)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CompetitionStatus::ENDED)),

            'published' => Tab::make('منشورة')
                ->badge(InternalCompetition::where('status', CompetitionStatus::PUBLISHED)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CompetitionStatus::PUBLISHED)),
        ];
    }
}
