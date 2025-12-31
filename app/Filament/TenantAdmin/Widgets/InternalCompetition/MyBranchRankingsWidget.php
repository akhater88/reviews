<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionScope;
use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranchScore;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyBranchRankingsWidget extends BaseWidget
{
    protected static ?string $heading = 'ترتيب فروعي';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

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

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;
        $activeCompetitionIds = InternalCompetition::active()->forTenant($tenantId)->pluck('id');

        return $table
            ->query(InternalCompetitionBranchScore::query()
                ->whereIn('competition_id', $activeCompetitionIds)
                ->where('tenant_id', $tenantId)
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

                        // For single-tenant: show actual rank
                        if ($competition->scope === CompetitionScope::SINGLE_TENANT) {
                            if ($record->rank === null) return '-';
                            return match ($record->rank) {
                                1 => '🥇 الأول',
                                2 => '🥈 الثاني',
                                3 => '🥉 الثالث',
                                default => "#{$record->rank}"
                            };
                        }

                        // For multi-tenant: show performance hint
                        $totalParticipants = InternalCompetitionBranchScore::where('competition_id', $record->competition_id)
                            ->where('metric_type', $record->metric_type)
                            ->count();

                        return $this->getPerformanceHint($record->rank, $totalParticipants);
                    }),
                Tables\Columns\TextColumn::make('score')
                    ->label('النقاط')
                    ->numeric(2),
            ])
            ->emptyStateHeading('لا توجد نتائج')
            ->paginated(false);
    }
}
