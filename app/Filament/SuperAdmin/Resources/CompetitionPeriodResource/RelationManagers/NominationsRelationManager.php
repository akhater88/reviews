<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NominationsRelationManager extends RelationManager
{
    protected static string $relationship = 'nominations';

    protected static ?string $title = 'الترشيحات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('participant.name')
                    ->label('المشارك')
                    ->searchable(),

                Tables\Columns\TextColumn::make('competitionBranch.name')
                    ->label('المطعم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominated_at')
                    ->label('تاريخ الترشيح')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_valid')
                    ->label('صالح')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_winner')
                    ->label('فائز')
                    ->boolean(),

                Tables\Columns\TextColumn::make('winnerRecord.prize_amount')
                    ->label('الجائزة')
                    ->money('SAR')
                    ->placeholder('-'),
            ])
            ->defaultSort('nominated_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_winner')
                    ->label('الفائزين'),

                Tables\Filters\TernaryFilter::make('is_valid')
                    ->label('صالح'),
            ])
            ->actions([
                Tables\Actions\Action::make('markWinner')
                    ->label('تعيين فائز')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('prize_amount')
                            ->label('مبلغ الجائزة')
                            ->numeric()
                            ->required()
                            ->prefix('ر.س'),
                    ])
                    ->visible(fn ($record) => ! $record->is_winner && $record->is_valid)
                    ->action(function ($record, array $data) {
                        $record->update(['is_winner' => true]);
                        $record->winnerRecord()->create([
                            'competition_period_id' => $record->competition_period_id,
                            'competition_branch_id' => $record->competition_branch_id,
                            'participant_id' => $record->participant_id,
                            'prize_amount' => $data['prize_amount'],
                            'prize_currency' => 'SAR',
                        ]);
                    }),

                Tables\Actions\Action::make('invalidate')
                    ->label('إبطال')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('سبب الإبطال')
                            ->required(),
                    ])
                    ->visible(fn ($record) => $record->is_valid)
                    ->action(function ($record, array $data) {
                        $record->invalidate($data['reason']);
                    }),
            ]);
    }
}
