<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SentimentOverview extends ChartWidget
{
    protected static ?string $heading = 'تحليل المشاعر';
    
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $user = Auth::user();

        // Filter reviews based on user access level
        if ($user && $user->isManager()) {
            $accessibleBranchIds = $user->branches()->pluck('branches.id');
            $positive = Review::whereIn('branch_id', $accessibleBranchIds)->where('sentiment', 'positive')->count();
            $neutral = Review::whereIn('branch_id', $accessibleBranchIds)->where('sentiment', 'neutral')->count();
            $negative = Review::whereIn('branch_id', $accessibleBranchIds)->where('sentiment', 'negative')->count();
        } else {
            // Admin sees all reviews
            $positive = Review::whereHas('branch')->where('sentiment', 'positive')->count();
            $neutral = Review::whereHas('branch')->where('sentiment', 'neutral')->count();
            $negative = Review::whereHas('branch')->where('sentiment', 'negative')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'المشاعر',
                    'data' => [$positive, $neutral, $negative],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // green for positive
                        'rgb(251, 191, 36)',  // yellow for neutral
                        'rgb(239, 68, 68)',   // red for negative
                    ],
                ],
            ],
            'labels' => ['إيجابي', 'محايد', 'سلبي'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
