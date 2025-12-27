<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';
    protected static ?string $title = 'سجل التغييرات';
    protected static ?string $modelLabel = 'تغيير';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('الإجراء')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->icon(fn ($state) => $state->icon()),

                Tables\Columns\TextColumn::make('oldPlan.name_ar')
                    ->label('من باقة')
                    ->default('-'),

                Tables\Columns\TextColumn::make('newPlan.name_ar')
                    ->label('إلى باقة')
                    ->default('-'),

                Tables\Columns\TextColumn::make('old_status')
                    ->label('الحالة السابقة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                    ->color(fn ($state) => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('new_status')
                    ->label('الحالة الجديدة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                    ->color(fn ($state) => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('changed_by_type')
                    ->label('بواسطة')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'super_admin' => 'مشرف',
                        'tenant' => 'العميل',
                        'system' => 'النظام',
                        default => $state ?? '-',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'super_admin' => 'primary',
                        'tenant' => 'info',
                        'system' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('reason')
                    ->label('السبب')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->default('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
