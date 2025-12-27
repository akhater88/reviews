<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';
    protected static ?string $title = 'سجل الاشتراكات';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralModelLabel = 'الاشتراكات';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name_ar')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn ($record) => $record->plan?->color ?? 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('دورة الفوترة')
                    ->formatStateUsing(fn ($state) => $state->label()),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state ?? 0, 2)
                    ),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d'),

                Tables\Columns\IconColumn::make('is_current')
                    ->label('الحالي')
                    ->state(fn ($record) =>
                        $record->id === $record->tenant->current_subscription_id
                    )
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
