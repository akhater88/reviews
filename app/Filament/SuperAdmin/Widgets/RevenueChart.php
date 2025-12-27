<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'الإيرادات الشهرية';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 3;
    protected static ?string $maxHeight = '300px';
    public ?string $filter = '6months';

    protected function getFilters(): ?array
    {
        return [
            '3months' => 'آخر 3 أشهر',
            '6months' => 'آخر 6 أشهر',
            '12months' => 'آخر سنة',
        ];
    }

    protected function getData(): array
    {
        $months = match ($this->filter) {
            '3months' => 3,
            '12months' => 12,
            default => 6,
        };

        $sarData = $this->getRevenueData('SAR', $months);
        $usdData = $this->getRevenueData('USD', $months);

        return [
            'datasets' => [
                [
                    'label' => 'ريال سعودي (SAR)',
                    'data' => $sarData['values'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
                [
                    'label' => 'دولار أمريكي (USD)',
                    'data' => $usdData['values'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                ],
            ],
            'labels' => $sarData['labels'],
        ];
    }

    private function getRevenueData(string $currency, int $months): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $values[] = Payment::where('status', 'completed')
                ->where('currency', $currency)
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
