<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\ChartWidget;

class SentimentOverview extends ChartWidget
{
    protected static ?string $heading = 'تحليل المشاعر';
    
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $positive = Review::where('sentiment', 'positive')->count();
        $neutral = Review::where('sentiment', 'neutral')->count();
        $negative = Review::where('sentiment', 'negative')->count();

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
