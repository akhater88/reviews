<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringSoonWidget extends BaseWidget
{
    protected static ?string $heading = 'اشتراكات تنتهي قريباً';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 3;
    protected static ?string $maxHeight = '400px';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->with(['tenant', 'plan'])
                    ->whereIn('status', [
                        SubscriptionStatus::ACTIVE,
                        SubscriptionStatus::TRIAL,
                    ])
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now()->addDays(7))
                    ->where('expires_at', '>', now())
                    ->orderBy('expires_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name_ar')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn ($record) => $record->plan?->color ?? 'primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('ينتهي في')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->description(fn ($record) => $this->getDaysUntilExpiry($record) . ' يوم متبقي')
                    ->color(fn ($record) => $this->getDaysUntilExpiry($record) <= 3 ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('extend')
                    ->label('تمديد')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn ($record) => route('filament.super-admin.resources.subscriptions.edit', $record)),

                Tables\Actions\Action::make('view_tenant')
                    ->label('العميل')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn ($record) => route('filament.super-admin.resources.tenants.view', $record->tenant_id)),
            ])
            ->emptyStateHeading('لا توجد اشتراكات تنتهي قريباً')
            ->emptyStateDescription('جميع الاشتراكات في حالة جيدة')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }

    private function getDaysUntilExpiry($record): int
    {
        if (!$record->expires_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($record->expires_at, false));
    }
}
