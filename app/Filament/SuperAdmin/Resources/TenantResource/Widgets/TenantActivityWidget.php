<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Widgets;

use App\Models\SubscriptionHistory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class TenantActivityWidget extends BaseWidget
{
    public ?Model $record = null;

    protected static ?string $heading = 'سجل النشاط';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SubscriptionHistory::query()
                    ->whereHas('subscription', fn ($q) =>
                        $q->where('tenant_id', $this->record?->id)
                    )
                    ->with(['oldPlan', 'newPlan'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->label('الإجراء')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('oldPlan.name_ar')
                    ->label('من باقة')
                    ->default('-'),

                Tables\Columns\TextColumn::make('newPlan.name_ar')
                    ->label('إلى باقة')
                    ->default('-'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('السبب')
                    ->limit(30)
                    ->default('-'),

                Tables\Columns\TextColumn::make('changed_by_type')
                    ->label('بواسطة')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'super_admin' => 'مشرف',
                        'tenant' => 'العميل',
                        'system' => 'النظام',
                        default => $state ?? '-',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
