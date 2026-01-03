<?php

namespace App\Filament\TenantAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MyLeaderboardPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'ترتيبي في المسابقات';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.internal-competition.my-leaderboard';

    public ?int $selectedCompetitionId = null;
    public ?string $selectedMetric = null;
    public ?InternalCompetition $competition = null;

    public function mount(): void
    {
        $tenantId = auth()->user()?->tenant_id;
        // Get the most recent active competition (ordered by start_date descending)
        $this->selectedCompetitionId = InternalCompetition::active()
            ->forTenant($tenantId)
            ->orderByDesc('start_date')
            ->value('id');
        $this->loadCompetition();
        if ($this->competition) {
            $this->selectedMetric = $this->competition->enabled_metrics[0]?->value ?? CompetitionMetric::CUSTOMER_SATISFACTION->value;
        }
    }

    protected function loadCompetition(): void
    {
        $this->competition = InternalCompetition::find($this->selectedCompetitionId);
    }

    /**
     * Get the IDs of branches accessible to the current user.
     * Managers only see branches they manage, admins see all tenant branches.
     */
    protected function getAccessibleBranchIds(): Collection
    {
        /** @var User $user */
        $user = auth()->user();
        return $user->accessibleBranches()->pluck('branches.id');
    }

    public function isMultiTenantCompetition(): bool
    {
        return $this->competition?->scope === CompetitionScope::MULTI_TENANT;
    }

    protected function getPerformanceHint(?int $rank, int $totalParticipants): string
    {
        if ($rank === null || $totalParticipants <= 0) {
            return 'غير محدد';
        }

        $percentile = ($rank / $totalParticipants) * 100;

        return match (true) {
            $percentile <= 10 => '🌟 متميز جداً',
            $percentile <= 25 => '⭐ متميز',
            $percentile <= 50 => '📈 فوق المتوسط',
            $percentile <= 75 => '📊 متوسط',
            default => '📉 يحتاج تحسين',
        };
    }

    protected function getHeaderActions(): array
    {
        $tenantId = auth()->user()?->tenant_id;

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
        $accessibleBranchIds = $this->getAccessibleBranchIds();
        $isMultiTenant = $this->isMultiTenantCompetition();

        // Get total participants for hint calculation
        $totalParticipants = InternalCompetitionBranchScore::where('competition_id', $this->selectedCompetitionId)
            ->where('metric_type', $this->selectedMetric)
            ->count();

        return $table
            ->query(fn () => InternalCompetitionBranchScore::query()
                ->where('competition_id', $this->selectedCompetitionId)
                ->where('metric_type', $this->selectedMetric)
                ->whereIn('branch_id', $accessibleBranchIds)
                ->with(['branch'])
                ->orderBy('rank'))
            ->columns([
                // For single-tenant: show actual rank for manager's branches
                Tables\Columns\TextColumn::make('rank')
                    ->label('الترتيب')
                    ->formatStateUsing(fn ($state) => $state === null ? '-' : match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$state}" })
                    ->alignCenter()
                    ->visible(!$isMultiTenant),

                // For multi-tenant: show performance hint instead of rank
                Tables\Columns\TextColumn::make('performance_hint')
                    ->label('مستوى الأداء')
                    ->state(fn ($record) => $this->getPerformanceHint($record->rank, $totalParticipants))
                    ->alignCenter()
                    ->visible($isMultiTenant),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('score')
                    ->label('النقاط')
                    ->numeric(2),

                // For multi-tenant: show relative position hint
                Tables\Columns\TextColumn::make('relative_position')
                    ->label('الموقع النسبي')
                    ->state(function ($record) use ($totalParticipants) {
                        if ($record->rank === null || $totalParticipants <= 0) return '-';
                        $percentile = 100 - (($record->rank / $totalParticipants) * 100);
                        return 'أفضل من ' . round($percentile) . '% من المشاركين';
                    })
                    ->color('gray')
                    ->visible($isMultiTenant),
            ])
            ->striped();
    }
}
