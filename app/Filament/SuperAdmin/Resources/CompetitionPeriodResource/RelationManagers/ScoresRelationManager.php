<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ScoresRelationManager extends RelationManager
{
    protected static string $relationship = 'scores';

    protected static ?string $title = 'الترتيب';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('rank_position')
                    ->label('#')
                    ->sortable()
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

                Tables\Columns\TextColumn::make('competitionBranch.name')
                    ->label('المطعم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('competition_score')
                    ->label('النقاط')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomination_count')
                    ->label('المرشحين')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_analyzed_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m H:i'),
            ])
            ->defaultSort('rank_position', 'asc');
    }
}
