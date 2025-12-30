<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

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

    public function table(Table $table): Table
    {
        $tenantId = filament()->getTenant()?->id;
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
                Tables\Columns\TextColumn::make('rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$state}" }),
                Tables\Columns\TextColumn::make('score')
                    ->label('النقاط')
                    ->numeric(2),
            ])
            ->emptyStateHeading('لا توجد نتائج')
            ->paginated(false);
    }
}
