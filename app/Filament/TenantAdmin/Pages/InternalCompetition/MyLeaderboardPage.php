<?php

namespace App\Filament\TenantAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class MyLeaderboardPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'ترتيبي في المسابقات';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.internal-competition.my-leaderboard';

    public ?int $selectedCompetitionId = null;
    public ?string $selectedMetric = null;
    public ?InternalCompetition $competition = null;

    public function mount(): void
    {
        $tenantId = filament()->getTenant()?->id;
        $this->selectedCompetitionId = InternalCompetition::active()->forTenant($tenantId)->latest()->value('id');
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
        $tenantId = filament()->getTenant()?->id;

        return [
            Action::make('selectCompetition')
                ->label('اختر المسابقة')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')
                        ->label('المسابقة')
                        ->options(InternalCompetition::whereIn('status', [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                            ->forTenant($tenantId)
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->display_name]))
                        ->required()
                        ->searchable()
                        ->default($this->selectedCompetitionId),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->loadCompetition();
                    $this->resetTable();
                }),
        ];
    }

    public function selectMetric(string $metric): void
    {
        $this->selectedMetric = $metric;
        $this->resetTable();
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $tenantId = filament()->getTenant()?->id;

        return $table
            ->query(fn () => InternalCompetitionBranchScore::query()
                ->where('competition_id', $this->selectedCompetitionId)
                ->where('metric_type', $this->selectedMetric)
                ->where('tenant_id', $tenantId)
                ->with(['branch'])
                ->orderBy('rank'))
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('ترتيبي')
                    ->formatStateUsing(fn ($state) => match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$state}" })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('score')
                    ->label('النقاط')
                    ->numeric(2),
            ])
            ->striped();
    }
}
