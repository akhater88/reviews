<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';
    protected static ?string $title = 'المشتركين';
    protected static ?string $modelLabel = 'اشتراك';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.super-admin.resources.tenants.view', $record->tenant_id)),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('الدورة')
                    ->formatStateUsing(fn ($state) => $state->label()),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('البدء')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('الانتهاء')
                    ->date('Y-m-d')
                    ->color(fn ($record) => $record->isExpiringSoon() ? 'warning' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'trial' => 'تجريبي',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.super-admin.resources.tenants.view', $record->tenant_id)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
