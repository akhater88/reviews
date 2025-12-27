<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Total Tenants
        $totalTenants = Tenant::count();
        $newTenantsThisMonth = Tenant::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Active Subscriptions
        $activeSubscriptions = Subscription::whereIn('status', [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::TRIAL,
            SubscriptionStatus::GRACE_PERIOD,
        ])->count();

        // Trial Users
        $trialUsers = Subscription::where('status', SubscriptionStatus::TRIAL)->count();

        // Monthly Revenue (current month)
        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Last month revenue for comparison
        $lastMonthRevenue = Payment::where('status', 'completed')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Pending Invoices
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();

        // Expiring Soon (next 7 days)
        $expiringSoon = Subscription::whereIn('status', [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::TRIAL,
        ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(7))
            ->where('expires_at', '>', now())
            ->count();

        return [
            Stat::make('إجمالي العملاء', number_format($totalTenants))
                ->description("جديد هذا الشهر: {$newTenantsThisMonth}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart($this->getTenantsChartData()),

            Stat::make('الاشتراكات النشطة', number_format($activeSubscriptions))
                ->description("تجريبي: {$trialUsers}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الإيرادات الشهرية', 'ر.س ' . number_format($monthlyRevenue, 2))
                ->description($revenueChange >= 0 ? "+{$revenueChange}%" : "{$revenueChange}%")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChartData()),

            Stat::make('فواتير معلقة', number_format($pendingInvoices))
                ->description($overdueInvoices > 0 ? "متأخرة: {$overdueInvoices}" : 'لا يوجد متأخرات')
                ->descriptionIcon($overdueInvoices > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check')
                ->color($overdueInvoices > 0 ? 'warning' : 'success'),

            Stat::make('تنتهي قريباً', number_format($expiringSoon))
                ->description('خلال 7 أيام')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'gray'),
        ];
    }

    private function getTenantsChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = Tenant::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }

        return $data;
    }

    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = Payment::where('status', 'completed')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');
        }

        return $data;
    }
}
