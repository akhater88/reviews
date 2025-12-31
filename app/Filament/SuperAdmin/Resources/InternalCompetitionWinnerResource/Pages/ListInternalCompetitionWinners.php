<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionWinnerResource\Pages;

use App\Enums\InternalCompetition\PrizeStatus;
use App\Filament\SuperAdmin\Resources\InternalCompetitionWinnerResource;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInternalCompetitionWinners extends ListRecords
{
    protected static string $resource = InternalCompetitionWinnerResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(InternalCompetitionWinner::count()),

            'pending' => Tab::make('بانتظار التسليم')
                ->badge(InternalCompetitionWinner::whereIn('prize_status', [
                    PrizeStatus::ANNOUNCED,
                    PrizeStatus::CLAIMED,
                    PrizeStatus::PROCESSING,
                ])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('prize_status', [
                    PrizeStatus::ANNOUNCED,
                    PrizeStatus::CLAIMED,
                    PrizeStatus::PROCESSING,
                ])),

            'delivered' => Tab::make('تم التسليم')
                ->badge(InternalCompetitionWinner::where('prize_status', PrizeStatus::DELIVERED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('prize_status', PrizeStatus::DELIVERED)),
        ];
    }
}
