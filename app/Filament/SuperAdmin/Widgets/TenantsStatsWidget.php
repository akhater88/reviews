<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $inactiveTenants = $totalTenants - $activeTenants;

        $trialTenants = Subscription::where('status', SubscriptionStatus::TRIAL)->count();
        $paidTenants = Subscription::where('status', SubscriptionStatus::ACTIVE)->count();

        $newThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('إجمالي العملاء', number_format($totalTenants))
                ->description("نشط: {$activeTenants} | غير نشط: {$inactiveTenants}")
                ->color('primary')
                ->icon('heroicon-o-users'),

            Stat::make('فترة تجريبية', number_format($trialTenants))
                ->description('عملاء في الفترة التجريبية')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('مشتركين', number_format($paidTenants))
                ->description('عملاء باشتراك مدفوع')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('جديد هذا الشهر', number_format($newThisMonth))
                ->description(now()->translatedFormat('F Y'))
                ->color('info')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
