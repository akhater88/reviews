<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Competition\CompetitionNomination;
use App\Models\Competition\CompetitionParticipant;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CompetitionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'المشاركات اليومية';

    protected static ?int $sort = 12;

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            return [
                'date' => $date->format('d/m'),
                'participants' => CompetitionParticipant::whereDate('created_at', $date)->count(),
                'nominations' => CompetitionNomination::whereDate('nominated_at', $date)->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'المشاركون الجدد',
                    'data' => $days->pluck('participants')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'الترشيحات',
                    'data' => $days->pluck('nominations')->toArray(),
                    'backgroundColor' => 'rgba(249, 115, 22, 0.5)',
                    'borderColor' => 'rgb(249, 115, 22)',
                ],
            ],
            'labels' => $days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
