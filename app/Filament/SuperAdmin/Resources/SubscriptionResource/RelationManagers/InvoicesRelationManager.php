<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Filament\SuperAdmin\Resources\InvoiceResource;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'الفواتير';
    protected static ?string $modelLabel = 'فاتورة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state, 2)
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('Y-m-d H:i')
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record])),

                Tables\Actions\Action::make('mark_paid')
                    ->label('تم الدفع')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => InvoiceStatus::PAID,
                            'paid_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تم تحديث حالة الفاتورة')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === InvoiceStatus::PENDING),
            ]);
    }
}
