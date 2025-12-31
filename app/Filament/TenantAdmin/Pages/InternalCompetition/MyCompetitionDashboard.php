<?php

namespace App\Filament\TenantAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Pages\Page;

class MyCompetitionDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'لوحة المسابقات';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.internal-competition.my-competition-dashboard';

    public array $activeCompetitions = [];
    public array $myRankings = [];
    public array $myWinnings = [];

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

    protected function getRankDisplay(?int $rank): string
    {
        if ($rank === null) {
            return '-';
        }

        return match ($rank) {
            1 => '🥇 الأول',
            2 => '🥈 الثاني',
            3 => '🥉 الثالث',
            default => "#{$rank}"
        };
    }

    public function mount(): void
    {
        $tenantId = auth()->user()?->tenant_id;
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
                'is_multi_tenant' => $c->scope === CompetitionScope::MULTI_TENANT,
            ])->toArray();

        $activeCompetitionIds = InternalCompetition::active()->forTenant($tenantId)->pluck('id');

        $this->myRankings = InternalCompetitionBranchScore::whereIn('competition_id', $activeCompetitionIds)
            ->where('tenant_id', $tenantId)
            ->with(['branch', 'competition'])
            ->orderBy('rank')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $competition = $s->competition;
                $isMultiTenant = $competition?->scope === CompetitionScope::MULTI_TENANT;

                // For multi-tenant: calculate performance hint
                $positionDisplay = '-';
                if ($isMultiTenant) {
                    $totalParticipants = InternalCompetitionBranchScore::where('competition_id', $s->competition_id)
                        ->where('metric_type', $s->metric_type)
                        ->count();
                    $positionDisplay = $this->getPerformanceHint($s->rank, $totalParticipants);
                } else {
                    $positionDisplay = $this->getRankDisplay($s->rank);
                }

                return [
                    'competition' => $competition?->display_name,
                    'branch' => $s->branch?->name,
                    'metric' => $s->metric_type->getLabel(),
                    'position_display' => $positionDisplay,
                    'score' => $s->score,
                    'is_multi_tenant' => $isMultiTenant,
                ];
            })->toArray();

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
