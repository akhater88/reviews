<?php

namespace App\Filament\SuperAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\BenchmarkService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;

class BenchmarkReportsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?string $navigationLabel = 'تقارير المقارنة';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.internal-competition.benchmark-reports';

    public ?int $selectedCompetitionId = null;
    public ?InternalCompetition $competition = null;
    public array $overallBenchmark = [];
    public array $roiSummary = [];

    public function mount(): void
    {
        $this->selectedCompetitionId = InternalCompetition::whereIn('status', [
            CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED,
        ])->latest()->value('id');
        $this->loadData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('selectCompetition')->label('اختر المسابقة')->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')->label('المسابقة')
                        ->options(InternalCompetition::whereIn('status', [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                            ->orderByDesc('created_at')->get()->mapWithKeys(fn ($c) => [$c->id => $c->display_name]))
                        ->required()->searchable(),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->loadData();
                }),
            Action::make('recalculate')->label('إعادة الحساب')->icon('heroicon-o-arrow-path')->color('warning')
                ->requiresConfirmation()->action(function () {
                    if ($this->competition) {
                        app(BenchmarkService::class)->recalculateBenchmarks($this->competition);
                        $this->loadData();
                    }
                }),
        ];
    }

    public function loadData(): void
    {
        if (!$this->selectedCompetitionId) {
            return;
        }
        $this->competition = InternalCompetition::find($this->selectedCompetitionId);
        if (!$this->competition) {
            return;
        }

        $benchmarkService = app(BenchmarkService::class);
        try {
            $allBranchIds = $this->competition->activeBranches()->pluck('branch_id')->toArray();
            $this->overallBenchmark = $benchmarkService->calculateOverallBenchmark($this->competition, $allBranchIds);
            $this->roiSummary = $benchmarkService->getROISummary($this->competition);
        } catch (\Exception $e) {
            logger()->error('Failed to load benchmarks', ['error' => $e->getMessage()]);
        }
    }
}
