<?php

namespace App\Filament\SuperAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\BenchmarkService;
use App\Services\InternalCompetition\WinnerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CompetitionDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?string $navigationLabel = 'لوحة التحكم';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.internal-competition.competition-dashboard';

    public ?int $selectedCompetitionId = null;
    public ?InternalCompetition $competition = null;
    public array $stats = [];
    public array $benchmarkSummary = [];
    public array $deliverySummary = [];

    public function mount(): void
    {
        $this->selectedCompetitionId = InternalCompetition::active()
            ->latest()->value('id') ?? InternalCompetition::latest()->value('id');
        $this->loadCompetitionData();
    }

    public function getTitle(): string|Htmlable
    {
        return 'لوحة تحكم المسابقات';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('selectCompetition')
                ->label('اختر المسابقة')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')
                        ->label('المسابقة')
                        ->options(InternalCompetition::orderByDesc('created_at')->limit(20)->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->display_name . ' (' . $c->status->getLabel() . ')']))
                        ->required()->searchable(),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->loadCompetitionData();
                }),
            Action::make('export')
                ->label('تصدير التقرير')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportReport())
                ->visible(fn () => $this->competition !== null),
        ];
    }

    public function loadCompetitionData(): void
    {
        if (!$this->selectedCompetitionId) {
            return;
        }

        $this->competition = InternalCompetition::with(['tenant', 'prizes', 'winners', 'activeBranches', 'activeTenants'])
            ->find($this->selectedCompetitionId);

        if (!$this->competition) {
            return;
        }

        $this->stats = [
            'total_tenants' => $this->competition->activeTenants()->count(),
            'total_branches' => $this->competition->activeBranches()->count(),
            'total_prizes' => $this->competition->prizes()->count(),
            'total_winners' => $this->competition->winners()->count(),
            'total_prize_value' => $this->competition->prizes()->sum('estimated_value'),
            'progress' => $this->competition->progress_percentage,
            'remaining_days' => $this->competition->remaining_days,
            'duration_days' => $this->competition->duration_in_days,
            'status' => $this->competition->status,
        ];

        if (in_array($this->competition->status, [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])) {
            try {
                $this->benchmarkSummary = app(BenchmarkService::class)->getROISummary($this->competition);
            } catch (\Exception $e) {
                $this->benchmarkSummary = [];
            }
        }

        if (in_array($this->competition->status, [CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])) {
            try {
                $this->deliverySummary = app(WinnerService::class)->getDeliverySummary($this->competition);
            } catch (\Exception $e) {
                $this->deliverySummary = [];
            }
        }
    }

    public function exportReport()
    {
        $filename = "competition-report-{$this->competition->id}-" . now()->format('Y-m-d') . ".csv";
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['تقرير المسابقة: ' . $this->competition->display_name]);
            fputcsv($file, ['تاريخ التقرير: ' . now()->format('Y-m-d H:i')]);
            fputcsv($file, []);
            fputcsv($file, ['إحصائيات عامة']);
            fputcsv($file, ['عدد المستأجرين', $this->stats['total_tenants']]);
            fputcsv($file, ['عدد الفروع', $this->stats['total_branches']]);
            fputcsv($file, ['عدد الجوائز', $this->stats['total_prizes']]);
            fputcsv($file, ['عدد الفائزين', $this->stats['total_winners']]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
