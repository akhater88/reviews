<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\SuperAdmin\Widgets\ExpiringSoonWidget;
use App\Filament\SuperAdmin\Widgets\PlanDistributionChart;
use App\Filament\SuperAdmin\Widgets\RecentActivityWidget;
use App\Filament\SuperAdmin\Widgets\RevenueChart;
use App\Filament\SuperAdmin\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'لوحة التحكم';
    protected static ?string $title = 'لوحة التحكم';
    protected static ?int $navigationSort = -2;

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 6,
            '2xl' => 6,
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RevenueChart::class,
            PlanDistributionChart::class,
            ExpiringSoonWidget::class,
            RecentActivityWidget::class,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('تحديث')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }
}
