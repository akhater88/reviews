<?php

namespace App\Filament\SuperAdmin\Widgets\InternalCompetition;

use App\Enums\InternalCompetition\PrizeStatus;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Widgets\ChartWidget;

class PrizeDeliveryStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'حالة تسليم الجوائز';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = InternalCompetitionWinner::selectRaw('prize_status, COUNT(*) as count')
            ->whereNotNull('prize_id')
            ->groupBy('prize_status')
            ->pluck('count', 'prize_status')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        foreach (PrizeStatus::cases() as $status) {
            $count = $statuses[$status->value] ?? 0;
            if ($count > 0) {
                $labels[] = $status->getLabel();
                $data[] = $count;
                $colors[] = match ($status) {
                    PrizeStatus::ANNOUNCED => 'rgba(156, 163, 175, 0.8)',
                    PrizeStatus::CLAIMED => 'rgba(59, 130, 246, 0.8)',
                    PrizeStatus::PROCESSING => 'rgba(245, 158, 11, 0.8)',
                    PrizeStatus::DELIVERED => 'rgba(34, 197, 94, 0.8)',
                };
            }
        }

        return [
            'datasets' => [['data' => $data, 'backgroundColor' => $colors]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
