<?php

namespace App\Filament\SuperAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\InternalCompetition\InternalCompetitionEmployee;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?string $navigationLabel = 'لوحة المتصدرين';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.internal-competition.leaderboard';

    public ?int $selectedCompetitionId = null;
    public ?string $selectedMetric = null;
    public ?InternalCompetition $competition = null;
    public string $viewType = 'branches';

    public function mount(): void
    {
        $this->selectedCompetitionId = request()->query('competition_id')
            ?? InternalCompetition::active()->latest()->value('id')
            ?? InternalCompetition::latest()->value('id');
        $this->loadCompetition();
        if ($this->competition) {
            $this->selectedMetric = $this->competition->enabled_metrics[0]?->value ?? CompetitionMetric::CUSTOMER_SATISFACTION->value;
        }
    }

    protected function loadCompetition(): void
    {
        $this->competition = InternalCompetition::find($this->selectedCompetitionId);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('selectCompetition')
                ->label('اختر المسابقة')->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')->label('المسابقة')
                        ->options(InternalCompetition::whereIn('status', [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                            ->orderByDesc('created_at')->get()->mapWithKeys(fn ($c) => [$c->id => $c->display_name]))
                        ->required()->searchable()->default($this->selectedCompetitionId),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->loadCompetition();
                    $this->resetTable();
                }),
            Action::make('export')->label('تصدير')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(fn () => $this->exportLeaderboard()),
        ];
    }

    public function selectMetric(string $metric): void
    {
        $this->selectedMetric = $metric;
        $this->viewType = $metric === CompetitionMetric::EMPLOYEE_MENTIONS->value ? 'employees' : 'branches';
        $this->resetTable();
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $this->viewType === 'employees' ? $this->employeesTable($table) : $this->branchesTable($table);
    }

    protected function branchesTable(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(fn (): Builder => InternalCompetitionBranchScore::query()
                ->where('competition_id', $this->selectedCompetitionId)
                ->where('metric_type', $this->selectedMetric)
                ->with(['branch', 'tenant'])->orderBy('rank'))
            ->columns([
                Tables\Columns\TextColumn::make('rank')->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$state}" })->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('tenant.name')->label('المستأجر')->toggleable(),
                Tables\Columns\TextColumn::make('score')->label('النقاط')->numeric(2)->sortable(),
                Tables\Columns\IconColumn::make('is_final')->label('نهائي')->boolean()->toggleable(),
            ])->defaultSort('rank')->striped()->paginated([10, 25, 50]);
    }

    protected function employeesTable(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(fn (): Builder => InternalCompetitionEmployee::query()
                ->where('competition_id', $this->selectedCompetitionId)
                ->with(['branch', 'tenant'])->orderBy('rank'))
            ->columns([
                Tables\Columns\TextColumn::make('rank')->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $state ? "#{$state}" : '-' })->alignCenter(),
                Tables\Columns\TextColumn::make('employee_name')->label('الموظف')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع'),
                Tables\Columns\TextColumn::make('score')->label('النقاط')->numeric(0)->sortable(),
                Tables\Columns\TextColumn::make('positive_mentions')->label('إيجابي')->badge()->color('success'),
                Tables\Columns\TextColumn::make('negative_mentions')->label('سلبي')->badge()->color('danger'),
            ])->defaultSort('rank')->striped()->paginated([10, 25, 50]);
    }

    public function exportLeaderboard()
    {
        $filename = "leaderboard-{$this->competition->id}-{$this->selectedMetric}-" . now()->format('Y-m-d') . ".csv";
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['المركز', 'الفرع/الموظف', 'النقاط']);
            if ($this->viewType === 'branches') {
                $scores = InternalCompetitionBranchScore::where('competition_id', $this->selectedCompetitionId)
                    ->where('metric_type', $this->selectedMetric)->with('branch')->orderBy('rank')->get();
                foreach ($scores as $score) {
                    fputcsv($file, [$score->rank, $score->branch?->name, $score->score]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
