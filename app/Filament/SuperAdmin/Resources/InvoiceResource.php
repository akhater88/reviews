<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\SuperAdmin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'الفواتير';
    protected static ?string $modelLabel = 'فاتورة';
    protected static ?string $pluralModelLabel = 'الفواتير';
    protected static ?string $navigationGroup = 'الاشتراكات';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', InvoiceStatus::PENDING)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الفاتورة')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('tenant_id')
                            ->label('العميل')
                            ->relationship('tenant', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn ($s) => [
                                $s->value => $s->label()
                            ]))
                            ->native(false),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('المبالغ')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('المبلغ الفرعي')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency === 'SAR' ? 'ر.س' : '$'),

                        Forms\Components\TextInput::make('discount_amount')
                            ->label('الخصم')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency === 'SAR' ? 'ر.س' : '$'),

                        Forms\Components\TextInput::make('tax_amount')
                            ->label('الضريبة')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency === 'SAR' ? 'ر.س' : '$'),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('الإجمالي')
                            ->numeric()
                            ->prefix(fn ($record) => $record?->currency === 'SAR' ? 'ر.س' : '$'),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('ملاحظات')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => TenantResource::getUrl('view', ['record' => $record->tenant_id])),

                Tables\Columns\TextColumn::make('subscription.plan.name_ar')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn ($record) => $record->subscription?->plan?->color ?? 'gray'),

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
                    ->label('الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->status === InvoiceStatus::PENDING && $record->due_date && $record->due_date->isPast()
                            ? 'danger'
                            : null
                    ),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('الإنشاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(InvoiceStatus::cases())->mapWithKeys(fn ($s) => [
                        $s->value => $s->label()
                    ]))
                    ->multiple(),

                Tables\Filters\Filter::make('overdue')
                    ->label('متأخرة')
                    ->query(fn (Builder $query) => $query
                        ->where('status', InvoiceStatus::PENDING)
                        ->where('due_date', '<', now())
                    ),

                Tables\Filters\Filter::make('this_month')
                    ->label('هذا الشهر')
                    ->query(fn (Builder $query) => $query
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_paid')
                    ->label('تم الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->default(now())
                            ->native(false),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('مرجع الدفع')
                            ->placeholder('رقم العملية أو المرجع'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => InvoiceStatus::PAID,
                            'paid_at' => $data['paid_at'],
                            'payment_reference' => $data['payment_reference'] ?? null,
                        ]);

                        Notification::make()
                            ->title('تم تحديث حالة الفاتورة')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === InvoiceStatus::PENDING),

                Tables\Actions\Action::make('send_reminder')
                    ->label('إرسال تذكير')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Send reminder email logic would go here
                        // Mail::to($record->tenant->billing_email)->send(new InvoiceReminderMail($record));

                        Notification::make()
                            ->title('تم إرسال التذكير')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === InvoiceStatus::PENDING),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('تعليم كمدفوع')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update([
                            'status' => InvoiceStatus::PAID,
                            'paid_at' => now(),
                        ])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات الفاتورة')
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->copyable()
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                        Infolists\Components\TextEntry::make('tenant.name')
                            ->label('العميل')
                            ->url(fn ($record) => TenantResource::getUrl('view', ['record' => $record->tenant_id])),

                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->label())
                            ->color(fn ($state) => $state->color()),

                        Infolists\Components\TextEntry::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->date('Y-m-d'),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->dateTime('Y-m-d H:i')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('billing_period')
                            ->label('فترة الفوترة')
                            ->state(fn ($record) =>
                                ($record->period_start?->format('Y-m-d') ?? '-') . ' - ' .
                                ($record->period_end?->format('Y-m-d') ?? '-')
                            ),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('تفاصيل المبالغ')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('المبلغ الفرعي')
                            ->formatStateUsing(fn ($state, $record) =>
                                ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state, 2)
                            ),

                        Infolists\Components\TextEntry::make('discount_amount')
                            ->label('الخصم')
                            ->formatStateUsing(fn ($state, $record) =>
                                ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state ?? 0, 2)
                            )
                            ->visible(fn ($record) => $record->discount_amount > 0),

                        Infolists\Components\TextEntry::make('tax_amount')
                            ->label('الضريبة (15%)')
                            ->formatStateUsing(fn ($state, $record) =>
                                ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state ?? 0, 2)
                            ),

                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('الإجمالي')
                            ->formatStateUsing(fn ($state, $record) =>
                                ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state, 2)
                            )
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('البنود')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('description_ar')
                                    ->label('الوصف'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('الكمية'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('سعر الوحدة'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('الإجمالي'),
                            ])
                            ->columns(4),
                    ])
                    ->visible(fn ($record) => !empty($record->items)),

                Infolists\Components\Section::make('ملاحظات')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->default('-'),
                    ])
                    ->visible(fn ($record) => $record->notes),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
