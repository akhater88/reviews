<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalBranches = Branch::count();
        $totalReviews = Review::count();
        $avgRating = Review::avg('rating') ?? 0;
        $pendingReplies = Review::where('needs_reply', true)->count();

        return [
            Stat::make('إجمالي الفروع', $totalBranches)
                ->description('الفروع المسجلة في النظام')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary')
                ->chart([7, 4, 6, 8, 5, 6, $totalBranches]),

            Stat::make('إجمالي المراجعات', number_format($totalReviews))
                ->description('من جميع الفروع')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('success'),

            Stat::make('متوسط التقييم', number_format($avgRating, 1) . ' ★')
                ->description('على مستوى جميع الفروع')
                ->descriptionIcon('heroicon-o-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make('بحاجة للرد', $pendingReplies)
                ->description('مراجعات تنتظر الرد')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingReplies > 10 ? 'danger' : ($pendingReplies > 5 ? 'warning' : 'success')),
        ];
    }
}
