<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\PaymentStatus;
use App\Filament\SuperAdmin\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'المدفوعات';

    protected static ?string $navigationGroup = 'الاشتراكات';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', PaymentStatus::PENDING)
            ->where('payment_gateway', 'manual')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('الفاتورة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state, $record) => ($record->currency === 'SAR' ? 'ر.س' : '$').' '.number_format($state, 2)),

                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label('البوابة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state->value ?? $state) {
                        'stripe' => 'Stripe',
                        'moyasar' => 'Moyasar',
                        'manual' => 'تحويل بنكي',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state->value ?? $state) {
                        'stripe' => 'info',
                        'moyasar' => 'success',
                        'manual' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),

                Tables\Filters\SelectFilter::make('payment_gateway')
                    ->label('البوابة')
                    ->options([
                        'stripe' => 'Stripe',
                        'moyasar' => 'Moyasar',
                        'manual' => 'تحويل بنكي',
                    ]),

                Tables\Filters\Filter::make('pending_manual')
                    ->label('تحويلات بانتظار التأكيد')
                    ->query(fn (Builder $query) => $query
                        ->where('payment_gateway', 'manual')
                        ->where('status', PaymentStatus::PENDING)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('confirm')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->default(now()),
                        Forms\Components\TextInput::make('bank_reference')
                            ->label('مرجع التحويل'),
                    ])
                    ->action(function ($record, array $data) {
                        app(PaymentService::class)->confirmManualPayment($record, $data);
                        Notification::make()->title('تم تأكيد الدفع')->success()->send();
                    })
                    ->visible(fn ($record) => $record->payment_gateway->value === 'manual' &&
                        $record->status === PaymentStatus::PENDING),

                Tables\Actions\Action::make('refund')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->default(fn ($record) => $record->amount),
                        Forms\Components\Textarea::make('reason')
                            ->label('السبب'),
                    ])
                    ->action(function ($record, array $data) {
                        $result = app(PaymentService::class)->refund($record, $data['amount'], $data['reason']);
                        Notification::make()
                            ->title($result['success'] ? 'تم الاسترداد' : 'فشل الاسترداد')
                            ->{$result['success'] ? 'success' : 'danger'}()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === PaymentStatus::COMPLETED)
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات الدفع')
                    ->schema([
                        Infolists\Components\TextEntry::make('gateway_payment_id')
                            ->label('معرف الدفع'),
                        Infolists\Components\TextEntry::make('tenant.name')
                            ->label('العميل'),
                        Infolists\Components\TextEntry::make('invoice.invoice_number')
                            ->label('رقم الفاتورة'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('المبلغ')
                            ->formatStateUsing(fn ($state, $record) => ($record->currency === 'SAR' ? 'ر.س' : '$').' '.number_format($state, 2)),
                        Infolists\Components\TextEntry::make('payment_gateway')
                            ->label('بوابة الدفع')
                            ->formatStateUsing(fn ($state) => match ($state->value ?? $state) {
                                'stripe' => 'Stripe',
                                'moyasar' => 'Moyasar',
                                'manual' => 'تحويل بنكي',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->label())
                            ->color(fn ($state) => $state->color()),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->dateTime('Y-m-d H:i'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('البيانات الإضافية')
                    ->schema([
                        Infolists\Components\TextEntry::make('metadata')
                            ->label('البيانات')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
