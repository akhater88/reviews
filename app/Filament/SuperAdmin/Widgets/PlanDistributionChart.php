<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Plan;
use Filament\Widgets\ChartWidget;

class PlanDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع العملاء على الباقات';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 3;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $plans = Plan::withCount(['subscriptions' => function ($query) {
            $query->whereIn('status', ['active', 'trial', 'grace_period']);
        }])->orderBy('sort_order')->get();

        $colors = [
            'gray' => ['bg' => 'rgba(107, 114, 128, 0.8)', 'border' => 'rgb(107, 114, 128)'],
            'info' => ['bg' => 'rgba(14, 165, 233, 0.8)', 'border' => 'rgb(14, 165, 233)'],
            'primary' => ['bg' => 'rgba(99, 102, 241, 0.8)', 'border' => 'rgb(99, 102, 241)'],
            'warning' => ['bg' => 'rgba(245, 158, 11, 0.8)', 'border' => 'rgb(245, 158, 11)'],
            'success' => ['bg' => 'rgba(16, 185, 129, 0.8)', 'border' => 'rgb(16, 185, 129)'],
            'danger' => ['bg' => 'rgba(239, 68, 68, 0.8)', 'border' => 'rgb(239, 68, 68)'],
        ];

        $backgroundColors = [];
        $borderColors = [];

        foreach ($plans as $plan) {
            $color = $colors[$plan->color] ?? $colors['primary'];
            $backgroundColors[] = $color['bg'];
            $borderColors[] = $color['border'];
        }

        return [
            'datasets' => [
                [
                    'data' => $plans->pluck('subscriptions_count')->toArray(),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $plans->pluck('name_ar')->toArray(),
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
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
