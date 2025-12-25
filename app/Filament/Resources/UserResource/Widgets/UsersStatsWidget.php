<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->description('جميع المستخدمين في النظام')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('المدراء الرئيسيين', User::where('role', 'admin')->count())
                ->description('وصول كامل للنظام')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('success'),

            Stat::make('مدراء الفروع', User::where('role', 'manager')->count())
                ->description('وصول محدود للفروع')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('info'),
        ];
    }
}
