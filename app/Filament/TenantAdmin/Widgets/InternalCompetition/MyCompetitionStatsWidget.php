<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyCompetitionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tenantId = auth()->user()?->tenant_id;
        if (!$tenantId) {
            return [];
        }

        return [
            Stat::make('المسابقات النشطة', InternalCompetition::active()->forTenant($tenantId)->count())
                ->description('مسابقة مشارك فيها')
                ->icon('heroicon-o-trophy')
                ->color('success'),
            Stat::make('إجمالي الفوز', InternalCompetitionWinner::where('tenant_id', $tenantId)->count())
                ->description('مرات الفوز')
                ->icon('heroicon-o-star')
                ->color('warning'),
            Stat::make('المراكز الأولى', InternalCompetitionWinner::where('tenant_id', $tenantId)->where('final_rank', 1)->count())
                ->description('🥇 مركز أول')
                ->icon('heroicon-o-trophy')
                ->color('info'),
        ];
    }
}
