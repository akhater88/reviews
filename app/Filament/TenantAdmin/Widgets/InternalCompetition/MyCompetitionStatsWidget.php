<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyCompetitionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $tenantId = $user?->tenant_id;
        if (!$tenantId) {
            return [];
        }

        // Get accessible branch IDs for the current user
        $accessibleBranchIds = $user->accessibleBranches()->pluck('branches.id');

        return [
            Stat::make('المسابقات النشطة', InternalCompetition::active()->forTenant($tenantId)->count())
                ->description('مسابقة مشارك فيها')
                ->icon('heroicon-o-trophy')
                ->color('success'),
            Stat::make('إجمالي الفوز', InternalCompetitionWinner::whereIn('branch_id', $accessibleBranchIds)->count())
                ->description('مرات الفوز')
                ->icon('heroicon-o-star')
                ->color('warning'),
            Stat::make('المراكز الأولى', InternalCompetitionWinner::whereIn('branch_id', $accessibleBranchIds)->where('final_rank', 1)->count())
                ->description('🥇 مركز أول')
                ->icon('heroicon-o-trophy')
                ->color('info'),
        ];
    }
}
