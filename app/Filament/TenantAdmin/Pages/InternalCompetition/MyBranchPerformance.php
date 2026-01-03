<?php

namespace App\Filament\TenantAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\Branch;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\User;
use App\Services\InternalCompetition\BenchmarkService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;

class MyBranchPerformance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'أداء فروعي';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.internal-competition.my-branch-performance';

    public ?int $selectedCompetitionId = null;
    public ?int $selectedBranchId = null;
    public ?InternalCompetition $competition = null;
    public ?Branch $branch = null;
    public array $scores = [];
    public array $benchmark = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $tenantId = $user?->tenant_id;

        // Get the most recent active competition (ordered by start_date descending)
        $this->selectedCompetitionId = InternalCompetition::active()
            ->forTenant($tenantId)
            ->orderByDesc('start_date')
            ->value('id');

        // Get the first accessible branch for the current user
        $this->selectedBranchId = $user->accessibleBranches()->value('branches.id');
        $this->loadData();
    }

    protected function getHeaderActions(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $tenantId = $user?->tenant_id;

        // Get only accessible branches for the current user
        $accessibleBranches = $user->accessibleBranches()->pluck('name', 'branches.id');

        return [
            Action::make('selectFilters')
                ->label('تصفية')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')
                        ->label('المسابقة')
                        ->options(InternalCompetition::whereIn('status', [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                            ->forTenant($tenantId)
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->display_name]))
                        ->required()
                        ->default($this->selectedCompetitionId),
                    Select::make('branch_id')
                        ->label('الفرع')
                        ->options($accessibleBranches)
                        ->required()
                        ->default($this->selectedBranchId),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->selectedBranchId = $data['branch_id'];
                    $this->loadData();
                }),
        ];
    }

    public function loadData(): void
    {
        if (!$this->selectedCompetitionId || !$this->selectedBranchId) {
            return;
        }

        $this->competition = InternalCompetition::find($this->selectedCompetitionId);
        $this->branch = Branch::find($this->selectedBranchId);

        if (!$this->competition || !$this->branch) {
            return;
        }

        $this->scores = InternalCompetitionBranchScore::where('competition_id', $this->selectedCompetitionId)
            ->where('branch_id', $this->selectedBranchId)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->metric_type->value => [
                'metric' => $s->metric_type->getLabel(),
                'score' => $s->score,
                'rank' => $s->rank,
                'breakdown' => $s->score_breakdown,
            ]])->toArray();

        try {
            $this->benchmark = app(BenchmarkService::class)->getBranchBenchmark($this->competition, $this->selectedBranchId) ?? [];
        } catch (\Exception $e) {
            $this->benchmark = [];
        }
    }
}
