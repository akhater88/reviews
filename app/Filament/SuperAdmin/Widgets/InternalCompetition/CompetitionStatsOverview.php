<?php

namespace App\Filament\SuperAdmin\Widgets\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\PrizeStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompetitionStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make('المسابقات النشطة', InternalCompetition::active()->count())
                ->description('مسابقة تعمل الآن')
                ->descriptionIcon('heroicon-o-play')
                ->color('success'),
            Stat::make('بانتظار النتائج', InternalCompetition::where('status', CompetitionStatus::ENDED)->count())
                ->description('مسابقة انتهت')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('جوائز معلقة', InternalCompetitionWinner::whereIn('prize_status', [PrizeStatus::ANNOUNCED, PrizeStatus::CLAIMED, PrizeStatus::PROCESSING])->count())
                ->description('بانتظار التسليم')
                ->descriptionIcon('heroicon-o-gift')
                ->color('danger'),
        ];
    }
}
