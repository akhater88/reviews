<?php

namespace App\Filament\TenantAdmin\Pages\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Pages\Page;

class MyCompetitionDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'لوحة المسابقات';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.internal-competition.my-competition-dashboard';

    public array $activeCompetitions = [];
    public array $myRankings = [];
    public array $myWinnings = [];

    public function mount(): void
    {
        $tenantId = filament()->getTenant()?->id;
        if (!$tenantId) {
            return;
        }

        $this->activeCompetitions = InternalCompetition::active()
            ->forTenant($tenantId)
            ->with(['prizes'])
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->display_name,
                'remaining_days' => $c->remaining_days,
                'progress' => $c->progress_percentage,
                'my_branches' => $c->activeBranches()->where('tenant_id', $tenantId)->count(),
            ])->toArray();

        $activeCompetitionIds = InternalCompetition::active()->forTenant($tenantId)->pluck('id');

        $this->myRankings = InternalCompetitionBranchScore::whereIn('competition_id', $activeCompetitionIds)
            ->where('tenant_id', $tenantId)
            ->with(['branch', 'competition'])
            ->orderBy('rank')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'competition' => $s->competition?->display_name,
                'branch' => $s->branch?->name,
                'metric' => $s->metric_type->getLabel(),
                'rank' => $s->rank,
                'score' => $s->score,
            ])->toArray();

        $this->myWinnings = InternalCompetitionWinner::where('tenant_id', $tenantId)
            ->with(['competition', 'branch', 'prize'])
            ->orderByDesc('announced_at')
            ->limit(5)
            ->get()
            ->map(fn ($w) => [
                'competition' => $w->competition?->display_name,
                'branch' => $w->branch?->name,
                'rank' => $w->final_rank,
                'metric' => $w->metric_type->getLabel(),
                'prize' => $w->prize?->display_name,
                'prize_status' => $w->prize_status->getLabel(),
            ])->toArray();
    }
}
