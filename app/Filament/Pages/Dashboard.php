<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Review;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'لوحة التحكم';
    
    protected static ?string $title = 'لوحة التحكم';

    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\SentimentOverview::class,
            \App\Filament\Widgets\RecentReviewsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
