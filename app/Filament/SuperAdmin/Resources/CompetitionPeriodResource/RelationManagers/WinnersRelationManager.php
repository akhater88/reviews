<?php

namespace App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WinnersRelationManager extends RelationManager
{
    protected static string $relationship = 'winners';

    protected static ?string $title = 'الفائزون';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('participant.name')
                    ->label('الفائز')
                    ->searchable(),

                Tables\Columns\TextColumn::make('participant.phone')
                    ->label('الجوال')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 4) . '****' . substr($state, -4)),

                Tables\Columns\TextColumn::make('competitionBranch.name')
                    ->label('المطعم'),

                Tables\Columns\TextColumn::make('prize_rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => "#{$state}"),

                Tables\Columns\TextColumn::make('prize_amount')
                    ->label('الجائزة')
                    ->money('SAR'),

                Tables\Columns\IconColumn::make('prize_claimed')
                    ->label('تم الاستلام')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_notified')
                    ->label('تم الإشعار')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الفوز')
                    ->dateTime('d/m/Y'),
            ])
            ->defaultSort('prize_rank', 'asc')
            ->actions([
                Tables\Actions\Action::make('markClaimed')
                    ->label('تم الاستلام')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->prize_claimed)
                    ->action(fn ($record) => $record->claim()),

                Tables\Actions\Action::make('markNotified')
                    ->label('تم الإشعار')
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->visible(fn ($record) => ! $record->is_notified)
                    ->action(fn ($record) => $record->markAsNotified()),
            ]);
    }
}
