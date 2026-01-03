<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class MyBranchRankingsWidget extends BaseWidget
{
    protected static ?string $heading = 'ترتيب فروعي';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

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

    protected function getPerformanceHint(?int $rank, int $totalParticipants, ?InternalCompetition $competition = null, ?int $branchId = null, ?string $metricType = null): string
    {
        // If rank is available, use percentile-based hint
        if ($rank !== null && $totalParticipants > 0) {
            $percentile = ($rank / $totalParticipants) * 100;

            return match (true) {
                $percentile <= 10 => '🌟 متميز جداً',
                $percentile <= 25 => '⭐ متميز',
                $percentile <= 50 => '📈 فوق المتوسط',
                $percentile <= 75 => '📊 متوسط',
                default => '📉 يحتاج تحسين',
            };
        }

        // Fall back to competition's progress hint for the branch
        if ($competition && $branchId && $metricType) {
            $metric = CompetitionMetric::tryFrom($metricType);
            if ($metric) {
                $hint = $competition->getProgressHintForBranch($branchId, $metric);
                if ($hint) {
                    return $hint;
                }
            }
        }

        return '📉 يحتاج تحسين';
    }

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;
        $accessibleBranchIds = $this->getAccessibleBranchIds();

        // Get the most recent active competition (ordered by start_date descending)
        $mostRecentCompetitionId = InternalCompetition::active()
            ->forTenant($tenantId)
            ->orderByDesc('start_date')
            ->value('id');

        return $table
            ->query(InternalCompetitionBranchScore::query()
                ->where('competition_id', $mostRecentCompetitionId)
                ->whereIn('branch_id', $accessibleBranchIds)
                ->with(['branch', 'competition'])
                ->orderBy('rank'))
            ->columns([
                Tables\Columns\TextColumn::make('competition.name_ar')
                    ->label('المسابقة'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع'),
                Tables\Columns\TextColumn::make('metric_type')
                    ->label('المعيار')
                    ->badge(),
                Tables\Columns\TextColumn::make('position_or_hint')
                    ->label('المركز / الأداء')
                    ->state(function ($record) {
                        $competition = $record->competition;
                        if (!$competition) return '-';

                        // Show actual rank when leaderboard is publicly visible
                        if ($competition->shouldShowLeaderboard()) {
                            if ($record->rank === null) return '-';
                            return match ($record->rank) {
                                1 => '🥇 الأول',
                                2 => '🥈 الثاني',
                                3 => '🥉 الثالث',
                                default => "#{$record->rank}"
                            };
                        }

                        // Show performance hint when leaderboard is not publicly visible
                        $totalParticipants = InternalCompetitionBranchScore::where('competition_id', $record->competition_id)
                            ->where('metric_type', $record->metric_type)
                            ->count();

                        return $this->getPerformanceHint($record->rank, $totalParticipants, $competition, $record->branch_id, $record->metric_type);
                    }),
                Tables\Columns\TextColumn::make('score')
                    ->label('النقاط')
                    ->numeric(2),
            ])
            ->emptyStateHeading('لا توجد نتائج')
            ->paginated(false);
    }
}
