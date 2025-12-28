<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Competition\CompetitionBranch;
use App\Models\Competition\CompetitionNomination;
use App\Models\Competition\CompetitionParticipant;
use App\Models\Competition\CompetitionPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompetitionStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $currentPeriod = CompetitionPeriod::current();

        $periodNominations = $currentPeriod
            ? CompetitionNomination::where('competition_period_id', $currentPeriod->id)->count()
            : 0;

        return [
            Stat::make('الفترة الحالية', $currentPeriod?->name_ar ?? 'لا توجد')
                ->description($currentPeriod ? 'تنتهي: ' . $currentPeriod->ends_at->format('d/m/Y') : '')
                ->color($currentPeriod ? 'success' : 'warning')
                ->icon('heroicon-o-calendar'),

            Stat::make('المشاركون', CompetitionParticipant::count())
                ->description('إجمالي المسجلين')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('المطاعم', CompetitionBranch::count())
                ->description('مطاعم مشاركة')
                ->color('success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('الترشيحات', $periodNominations)
                ->description('الفترة الحالية')
                ->color('warning')
                ->icon('heroicon-o-hand-thumb-up'),
        ];
    }
}
