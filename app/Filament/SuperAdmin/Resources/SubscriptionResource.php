<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;
use App\Filament\SuperAdmin\Resources\SubscriptionResource\RelationManagers;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'الاشتراكات';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralModelLabel = 'الاشتراكات';
    protected static ?string $navigationGroup = 'الاشتراكات';
    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $expiringSoon = static::getModel()::expiringSoon()->count();
        return $expiringSoon > 0 ? 'warning' : 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الاشتراك')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('العميل')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($operation) => $operation === 'edit'),

                        Forms\Components\Select::make('plan_id')
                            ->label('الباقة')
                            ->options(Plan::active()->pluck('name_ar', 'id'))
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(collect(SubscriptionStatus::cases())->mapWithKeys(fn ($s) => [
                                $s->value => $s->label()
                            ]))
                            ->required()
                            ->native(false)
                            ->default(SubscriptionStatus::TRIAL->value)
                            ->disabled(fn ($operation) => $operation === 'create')
                            ->dehydrated(true),

                        Forms\Components\Select::make('billing_cycle')
                            ->label('دورة الفوترة')
                            ->options(collect(BillingCycle::cases())->mapWithKeys(fn ($c) => [
                                $c->value => $c->label()
                            ]))
                            ->required()
                            ->native(false)
                            ->default('monthly'),

                        Forms\Components\Select::make('currency')
                            ->label('العملة')
                            ->options([
                                'SAR' => 'ريال سعودي (SAR)',
                                'USD' => 'دولار أمريكي (USD)',
                            ])
                            ->required()
                            ->native(false)
                            ->default('SAR'),

                        Forms\Components\TextInput::make('price_at_renewal')
                            ->label('سعر التجديد')
                            ->numeric()
                            ->prefix(fn ($get) => $get('currency') === 'SAR' ? 'ر.س' : '$')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('التواريخ')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('انتهاء التجربة')
                            ->native(false)
                            ->visible(fn ($get) => $get('status') === 'trial'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('next_billing_date')
                            ->label('تاريخ الفوترة القادم')
                            ->native(false),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('ملاحظات')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات داخلية')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => TenantResource::getUrl('view', ['record' => $record->tenant_id])),

                Tables\Columns\TextColumn::make('plan.name_ar')
                    ->label('الباقة')
                    ->badge()
                    ->color(fn ($record) => $record->plan?->color ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->icon(fn ($state) => $state->icon()),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label('الدورة')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price_at_renewal')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state ?? 0, 2)
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('البدء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn ($record) => $record->isExpiringSoon() ? 'warning' : null)
                    ->description(fn ($record) => $record->daysUntilExpiry() > 0
                        ? $record->daysUntilExpiry() . ' يوم'
                        : 'منتهي'
                    ),

                Tables\Columns\IconColumn::make('is_current')
                    ->label('الحالي')
                    ->state(fn ($record) =>
                        $record->id === $record->tenant?->current_subscription_id
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(fn ($s) => [
                        $s->value => $s->label()
                    ]))
                    ->multiple(),

                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('الباقة')
                    ->relationship('plan', 'name_ar'),

                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->label('دورة الفوترة')
                    ->options(collect(BillingCycle::cases())->mapWithKeys(fn ($c) => [
                        $c->value => $c->label()
                    ])),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('ينتهي خلال 7 أيام')
                    ->query(fn (Builder $query) => $query->expiringSoon()),

                Tables\Filters\Filter::make('expired')
                    ->label('منتهية')
                    ->query(fn (Builder $query) => $query->expired()),

                Tables\Filters\Filter::make('trial')
                    ->label('فترة تجريبية')
                    ->query(fn (Builder $query) => $query->where('status', SubscriptionStatus::TRIAL)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),

                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),

                    Tables\Actions\Action::make('change_plan')
                        ->label('تغيير الباقة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('plan_id')
                                ->label('الباقة الجديدة')
                                ->options(fn ($record) => Plan::active()
                                    ->where('id', '!=', $record->plan_id)
                                    ->pluck('name_ar', 'id')
                                )
                                ->required()
                                ->native(false),

                            Forms\Components\Select::make('timing')
                                ->label('توقيت التغيير')
                                ->options([
                                    'immediate' => 'فوري',
                                    'end_of_period' => 'نهاية الفترة الحالية',
                                ])
                                ->default('immediate')
                                ->native(false),

                            Forms\Components\Textarea::make('reason')
                                ->label('السبب')
                                ->rows(2),
                        ])
                        ->action(function ($record, array $data) {
                            app(SubscriptionService::class)->changePlan(
                                $record,
                                Plan::find($data['plan_id']),
                                $data['timing'] === 'immediate',
                                $data['reason'],
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم تغيير الباقة')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->canAccessFeatures()),

                    Tables\Actions\Action::make('extend')
                        ->label('تمديد')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('period')
                                ->label('مدة التمديد')
                                ->options([
                                    '7' => 'أسبوع (7 أيام)',
                                    '14' => 'أسبوعين (14 يوم)',
                                    '30' => 'شهر (30 يوم)',
                                    '60' => 'شهرين (60 يوم)',
                                    '90' => '3 أشهر (90 يوم)',
                                    '180' => '6 أشهر (180 يوم)',
                                    '365' => 'سنة (365 يوم)',
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\Textarea::make('reason')
                                ->label('السبب')
                                ->rows(2)
                                ->placeholder('سبب التمديد (اختياري)'),
                        ])
                        ->action(function ($record, array $data) {
                            app(SubscriptionService::class)->extend(
                                $record,
                                (int) $data['period'],
                                $data['reason'],
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم تمديد الاشتراك')
                                ->body("تمت إضافة {$data['period']} يوم")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('renew')
                        ->label('تجديد')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('تجديد الاشتراك')
                        ->modalDescription(fn ($record) => "سيتم تجديد الاشتراك لفترة {$record->billing_cycle->label()} جديدة")
                        ->action(function ($record) {
                            app(SubscriptionService::class)->renew(
                                $record,
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم تجديد الاشتراك')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => in_array($record->status, [
                            SubscriptionStatus::EXPIRED,
                            SubscriptionStatus::GRACE_PERIOD,
                        ])),

                    Tables\Actions\Action::make('cancel')
                        ->label('إلغاء')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Select::make('timing')
                                ->label('توقيت الإلغاء')
                                ->options([
                                    'end_of_period' => 'نهاية الفترة الحالية',
                                    'immediate' => 'فوري',
                                ])
                                ->default('end_of_period')
                                ->native(false)
                                ->helperText('الإلغاء الفوري سيوقف الوصول مباشرة'),

                            Forms\Components\Textarea::make('reason')
                                ->label('سبب الإلغاء')
                                ->rows(2)
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            app(SubscriptionService::class)->cancel(
                                $record,
                                $data['timing'] === 'immediate',
                                $data['reason'],
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم إلغاء الاشتراك')
                                ->warning()
                                ->send();
                        })
                        ->visible(fn ($record) => !in_array($record->status, [
                            SubscriptionStatus::CANCELLED,
                            SubscriptionStatus::EXPIRED,
                        ])),

                    Tables\Actions\Action::make('reactivate')
                        ->label('إعادة تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            app(SubscriptionService::class)->reactivate(
                                $record,
                                'Reactivated by admin',
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم إعادة تفعيل الاشتراك')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === SubscriptionStatus::CANCELLED),

                    Tables\Actions\Action::make('convert_trial')
                        ->label('تحويل للمدفوع')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تحويل الفترة التجريبية')
                        ->modalDescription('سيتم إنهاء الفترة التجريبية وبدء الاشتراك المدفوع')
                        ->action(function ($record) {
                            app(SubscriptionService::class)->convertTrial(
                                $record,
                                'super_admin',
                                auth()->guard('super_admin')->id()
                            );

                            Notification::make()
                                ->title('تم تحويل الاشتراك للمدفوع')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === SubscriptionStatus::TRIAL),
                ])
                ->label('إجراءات')
                ->icon('heroicon-m-ellipsis-vertical')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('extend_all')
                        ->label('تمديد المحددين')
                        ->icon('heroicon-o-plus-circle')
                        ->form([
                            Forms\Components\Select::make('days')
                                ->label('عدد الأيام')
                                ->options([
                                    '7' => '7 أيام',
                                    '30' => '30 يوم',
                                    '90' => '90 يوم',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $service = app(SubscriptionService::class);
                            foreach ($records as $record) {
                                $service->extend(
                                    $record,
                                    (int) $data['days'],
                                    'Bulk extension',
                                    'super_admin',
                                    auth()->guard('super_admin')->id()
                                );
                            }

                            Notification::make()
                                ->title('تم تمديد الاشتراكات')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('معلومات الاشتراك')
                        ->schema([
                            Infolists\Components\TextEntry::make('tenant.name')
                                ->label('العميل')
                                ->url(fn ($record) => TenantResource::getUrl('view', ['record' => $record->tenant_id])),

                            Infolists\Components\TextEntry::make('plan.name_ar')
                                ->label('الباقة')
                                ->badge()
                                ->color(fn ($record) => $record->plan?->color ?? 'gray'),

                            Infolists\Components\TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state->label())
                                ->color(fn ($state) => $state->color()),

                            Infolists\Components\TextEntry::make('billing_cycle')
                                ->label('دورة الفوترة')
                                ->formatStateUsing(fn ($state) => $state->label()),

                            Infolists\Components\TextEntry::make('currency')
                                ->label('العملة'),

                            Infolists\Components\TextEntry::make('price_at_renewal')
                                ->label('سعر التجديد')
                                ->formatStateUsing(fn ($state, $record) =>
                                    ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($state ?? 0, 2)
                                ),
                        ])
                        ->columns(3),

                    Infolists\Components\Section::make('التواريخ')
                        ->schema([
                            Infolists\Components\TextEntry::make('started_at')
                                ->label('تاريخ البدء')
                                ->dateTime('Y-m-d H:i'),

                            Infolists\Components\TextEntry::make('trial_ends_at')
                                ->label('انتهاء التجربة')
                                ->dateTime('Y-m-d H:i')
                                ->default('-')
                                ->visible(fn ($record) => $record->trial_ends_at),

                            Infolists\Components\TextEntry::make('expires_at')
                                ->label('تاريخ الانتهاء')
                                ->dateTime('Y-m-d H:i')
                                ->color(fn ($record) => $record->isExpiringSoon() ? 'warning' : null),

                            Infolists\Components\TextEntry::make('days_remaining')
                                ->label('الأيام المتبقية')
                                ->state(fn ($record) => $record->daysUntilExpiry())
                                ->suffix(' يوم')
                                ->badge()
                                ->color(fn ($state) => $state <= 7 ? 'warning' : 'success'),

                            Infolists\Components\TextEntry::make('next_billing_date')
                                ->label('الفوترة القادمة')
                                ->dateTime('Y-m-d')
                                ->default('-'),

                            Infolists\Components\TextEntry::make('renewed_at')
                                ->label('آخر تجديد')
                                ->dateTime('Y-m-d H:i')
                                ->default('-'),
                        ])
                        ->columns(3),

                    Infolists\Components\Section::make('معلومات الإلغاء')
                        ->schema([
                            Infolists\Components\TextEntry::make('cancelled_at')
                                ->label('تاريخ الإلغاء')
                                ->dateTime('Y-m-d H:i'),

                            Infolists\Components\TextEntry::make('cancellation_reason')
                                ->label('سبب الإلغاء')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->visible(fn ($record) => $record->cancelled_at || $record->cancel_at_period_end),
                ])
                ->columnSpan(2),

                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('الإجراءات السريعة')
                        ->schema([
                            Infolists\Components\Actions::make([
                                Infolists\Components\Actions\Action::make('extend_7')
                                    ->label('+7 أيام')
                                    ->icon('heroicon-o-plus')
                                    ->color('success')
                                    ->action(function ($record) {
                                        app(SubscriptionService::class)->extend(
                                            $record, 7, 'Quick extend', 'super_admin',
                                            auth()->guard('super_admin')->id()
                                        );
                                        Notification::make()->title('تم التمديد')->success()->send();
                                    }),

                                Infolists\Components\Actions\Action::make('extend_30')
                                    ->label('+30 يوم')
                                    ->icon('heroicon-o-plus')
                                    ->color('success')
                                    ->action(function ($record) {
                                        app(SubscriptionService::class)->extend(
                                            $record, 30, 'Quick extend', 'super_admin',
                                            auth()->guard('super_admin')->id()
                                        );
                                        Notification::make()->title('تم التمديد')->success()->send();
                                    }),
                            ]),
                        ]),

                    Infolists\Components\Section::make('ملخص الفواتير')
                        ->schema([
                            Infolists\Components\TextEntry::make('invoices_count')
                                ->label('إجمالي الفواتير')
                                ->state(fn ($record) => $record->invoices()->count())
                                ->badge(),

                            Infolists\Components\TextEntry::make('paid_invoices')
                                ->label('المدفوعة')
                                ->state(fn ($record) => $record->invoices()->where('status', 'paid')->count())
                                ->badge()
                                ->color('success'),

                            Infolists\Components\TextEntry::make('pending_invoices')
                                ->label('المعلقة')
                                ->state(fn ($record) => $record->invoices()->where('status', 'pending')->count())
                                ->badge()
                                ->color('warning'),

                            Infolists\Components\TextEntry::make('total_paid')
                                ->label('إجمالي المدفوع')
                                ->state(function ($record) {
                                    $total = $record->invoices()
                                        ->where('status', 'paid')
                                        ->sum('total_amount');
                                    return ($record->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($total, 2);
                                }),
                        ])
                        ->columns(2),
                ])
                ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoryRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['tenant.name', 'tenant.email'];
    }
}
