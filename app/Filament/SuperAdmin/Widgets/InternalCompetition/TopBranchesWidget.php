<?php

namespace App\Filament\SuperAdmin\Widgets\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use Filament\Widgets\ChartWidget;

class TopBranchesWidget extends ChartWidget
{
    protected static ?string $heading = 'أفضل الفروع (رضا العملاء)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $competition = InternalCompetition::active()->latest()->first();
        if (!$competition) {
            return ['datasets' => [], 'labels' => []];
        }

        $scores = InternalCompetitionBranchScore::where('competition_id', $competition->id)
            ->where('metric_type', CompetitionMetric::CUSTOMER_SATISFACTION)
            ->with('branch')->orderBy('rank')->limit(5)->get();

        return [
            'datasets' => [[
                'label' => 'النقاط',
                'data' => $scores->pluck('score')->toArray(),
                'backgroundColor' => [
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(192, 192, 192, 0.8)',
                    'rgba(205, 127, 50, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                ],
            ]],
            'labels' => $scores->map(fn ($s) => $s->branch?->name ?? 'غير معروف')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
