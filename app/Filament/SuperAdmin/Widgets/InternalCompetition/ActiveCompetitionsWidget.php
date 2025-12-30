<?php

namespace App\Filament\SuperAdmin\Widgets\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveCompetitionsWidget extends BaseWidget
{
    protected static ?string $heading = 'المسابقات النشطة';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(InternalCompetition::query()->active()->with(['tenant', 'activeBranches'])->orderBy('end_date'))
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('المسابقة')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('scope')
                    ->label('النطاق')
                    ->badge(),
                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('المتبقي')
                    ->formatStateUsing(fn ($state) => "{$state} يوم")
                    ->color(fn ($state) => $state <= 3 ? 'danger' : ($state <= 7 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('التقدم')
                    ->formatStateUsing(fn ($state) => "{$state}%"),
                Tables\Columns\TextColumn::make('active_branches_count')
                    ->label('الفروع')
                    ->counts('activeBranches'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->url(fn ($record) => route('filament.super-admin.resources.internal-competitions.view', $record))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('لا توجد مسابقات نشطة')
            ->paginated(false);
    }
}
