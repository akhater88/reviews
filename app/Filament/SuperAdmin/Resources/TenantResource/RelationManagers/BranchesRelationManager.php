<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';
    protected static ?string $title = 'الفروع';
    protected static ?string $modelLabel = 'فرع';
    protected static ?string $pluralModelLabel = 'الفروع';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الفرع')
                    ->searchable(),

                Tables\Columns\TextColumn::make('google_place_id')
                    ->label('Google Place ID')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('city')
                    ->label('المدينة'),

                Tables\Columns\TextColumn::make('reviews_count')
                    ->label('المراجعات')
                    ->counts('reviews')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('متوسط التقييم')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . ' ⭐' : '-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_competitor')
                    ->label('منافس')
                    ->boolean()
                    ->trueIcon('heroicon-o-flag')
                    ->falseIcon('heroicon-o-building-office'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_competitor')
                    ->label('النوع')
                    ->trueLabel('منافسين')
                    ->falseLabel('فروع خاصة')
                    ->placeholder('الكل'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
