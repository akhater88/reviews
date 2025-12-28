<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopBranchesWidget extends BaseWidget
{
    protected static ?string $heading = 'المطاعم المتصدرة';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $currentPeriod = CompetitionPeriod::current();

        return $table
            ->query(
                CompetitionScore::query()
                    ->when($currentPeriod, fn ($q) => $q->where('competition_period_id', $currentPeriod->id))
                    ->whereNotNull('rank_position')
                    ->orderBy('rank_position')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank_position')
                    ->label('#')
                    ->formatStateUsing(function ($state) {
                        if ($state === 1) {
                            return '🥇';
                        }
                        if ($state === 2) {
                            return '🥈';
                        }
                        if ($state === 3) {
                            return '🥉';
                        }

                        return $state;
                    }),

                Tables\Columns\ImageColumn::make('competitionBranch.photo_url')
                    ->label('')
                    ->circular()
                    ->size(32)
                    ->getStateUsing(fn ($record) => $record->competitionBranch?->photos[0] ?? null),

                Tables\Columns\TextColumn::make('competitionBranch.name')
                    ->label('المطعم'),

                Tables\Columns\TextColumn::make('competitionBranch.city')
                    ->label('المدينة'),

                Tables\Columns\TextColumn::make('competition_score')
                    ->label('النقاط')
                    ->numeric(2)
                    ->color('success'),

                Tables\Columns\TextColumn::make('nomination_count')
                    ->label('المرشحين'),
            ])
            ->paginated(false);
    }
}
