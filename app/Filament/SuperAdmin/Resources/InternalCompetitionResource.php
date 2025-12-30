<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionPeriod;
use App\Enums\InternalCompetition\CompetitionScope;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\LeaderboardVisibility;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource\RelationManagers;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\CompetitionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InternalCompetitionResource extends Resource
{
    protected static ?string $model = InternalCompetition::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'المسابقات';

    protected static ?string $navigationLabel = 'المسابقات الداخلية';

    protected static ?string $modelLabel = 'مسابقة داخلية';

    protected static ?string $pluralModelLabel = 'المسابقات الداخلية';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('المسابقة')
                    ->tabs([
                        // Basic Info Tab
                        Forms\Components\Tabs\Tab::make('المعلومات الأساسية')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('اسم المسابقة (بالإنجليزية)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Q1 Performance Competition'),

                                                Forms\Components\TextInput::make('name_ar')
                                                    ->label('اسم المسابقة (بالعربية)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('مسابقة الأداء للربع الأول'),
                                            ]),

                                        Forms\Components\Textarea::make('description')
                                            ->label('الوصف')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('وصف تفصيلي للمسابقة وأهدافها...'),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('scope')
                                                    ->label('نطاق المسابقة')
                                                    ->options(CompetitionScope::class)
                                                    ->required()
                                                    ->default(CompetitionScope::SINGLE_TENANT)
                                                    ->live()
                                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('tenant_id', null)),

                                                Forms\Components\Select::make('tenant_id')
                                                    ->label('المستأجر')
                                                    ->relationship('tenant', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->visible(fn (Forms\Get $get) => $get('scope') === CompetitionScope::SINGLE_TENANT->value || $get('scope') === CompetitionScope::SINGLE_TENANT),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('period')
                                                    ->label('مدة المسابقة')
                                                    ->options(CompetitionPeriod::class)
                                                    ->required()
                                                    ->default(CompetitionPeriod::MONTHLY)
                                                    ->live(),

                                                Forms\Components\DatePicker::make('start_date')
                                                    ->label('تاريخ البدء')
                                                    ->required()
                                                    ->native(false)
                                                    ->minDate(now())
                                                    ->live()
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                                        if ($state && $get('period')) {
                                                            $period = CompetitionPeriod::from($get('period'));
                                                            $endDate = \Carbon\Carbon::parse($state)
                                                                ->addDays($period->getDurationInDays())
                                                                ->subDay();
                                                            $set('end_date', $endDate->format('Y-m-d'));
                                                        }
                                                    }),

                                                Forms\Components\DatePicker::make('end_date')
                                                    ->label('تاريخ الانتهاء')
                                                    ->required()
                                                    ->native(false)
                                                    ->minDate(fn (Forms\Get $get) => $get('start_date')),
                                            ]),
                                    ]),
                            ]),

                        // Metrics Tab
                        Forms\Components\Tabs\Tab::make('معايير التقييم')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Section::make('المعايير المفعّلة')
                                    ->description('اختر المعايير التي سيتم تقييم المشاركين بناءً عليها')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('metrics_config.enabled_metrics')
                                            ->label('')
                                            ->options([
                                                CompetitionMetric::CUSTOMER_SATISFACTION->value => '⭐ رضا العملاء - بناءً على التقييمات والمشاعر',
                                                CompetitionMetric::RESPONSE_TIME->value => '⚡ سرعة الاستجابة - بناءً على وقت الرد على المراجعات',
                                                CompetitionMetric::EMPLOYEE_MENTIONS->value => '👤 ذكر الموظفين - بناءً على ذكر الموظفين في المراجعات',
                                            ])
                                            ->required()
                                            ->columns(1)
                                            ->bulkToggleable(),

                                        Forms\Components\Fieldset::make('أوزان المعايير')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('metrics_config.weights.customer_satisfaction')
                                                            ->label('وزن رضا العملاء')
                                                            ->numeric()
                                                            ->default(1.0)
                                                            ->minValue(0)
                                                            ->maxValue(5)
                                                            ->step(0.1)
                                                            ->suffix('×'),

                                                        Forms\Components\TextInput::make('metrics_config.weights.response_time')
                                                            ->label('وزن سرعة الاستجابة')
                                                            ->numeric()
                                                            ->default(1.0)
                                                            ->minValue(0)
                                                            ->maxValue(5)
                                                            ->step(0.1)
                                                            ->suffix('×'),

                                                        Forms\Components\TextInput::make('metrics_config.weights.employee_mentions')
                                                            ->label('وزن ذكر الموظفين')
                                                            ->numeric()
                                                            ->default(1.0)
                                                            ->minValue(0)
                                                            ->maxValue(5)
                                                            ->step(0.1)
                                                            ->suffix('×'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Settings Tab
                        Forms\Components\Tabs\Tab::make('الإعدادات')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('إعدادات لوحة المتصدرين')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('leaderboard_visibility')
                                                    ->label('عرض لوحة المتصدرين')
                                                    ->options(LeaderboardVisibility::class)
                                                    ->required()
                                                    ->default(LeaderboardVisibility::AFTER_END),

                                                Forms\Components\Toggle::make('show_progress_hints')
                                                    ->label('عرض تلميحات التقدم')
                                                    ->helperText('عرض تلميحات للمشاركين عن ترتيبهم دون الكشف عن التفاصيل')
                                                    ->default(true),
                                            ]),

                                        Forms\Components\Toggle::make('public_showcase')
                                            ->label('عرض الفائزين علنياً')
                                            ->helperText('السماح بعرض الفائزين في صفحة عامة بعد انتهاء المسابقة')
                                            ->default(false),
                                    ]),

                                Forms\Components\Section::make('إعدادات الإشعارات')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('notification_settings.whatsapp.enabled')
                                                    ->label('تفعيل إشعارات واتساب')
                                                    ->default(true),

                                                Forms\Components\Toggle::make('notification_settings.email.enabled')
                                                    ->label('تفعيل إشعارات البريد الإلكتروني')
                                                    ->default(true),
                                            ]),

                                        Forms\Components\CheckboxList::make('notification_settings.events')
                                            ->label('أحداث الإشعارات')
                                            ->options([
                                                'start' => 'عند بدء المسابقة',
                                                'reminder' => 'تذكيرات دورية',
                                                'ending_soon' => 'قبل انتهاء المسابقة',
                                                'ended' => 'عند انتهاء المسابقة',
                                                'winner' => 'للفائزين',
                                            ])
                                            ->default(['start', 'ended', 'winner'])
                                            ->columns(3),

                                        Forms\Components\TagsInput::make('notification_settings.reminder_days')
                                            ->label('أيام التذكير')
                                            ->helperText('أيام قبل انتهاء المسابقة لإرسال تذكير')
                                            ->default(['7', '3', '1'])
                                            ->placeholder('أضف عدد الأيام'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('اسم المسابقة')
                    ->searchable(['name', 'name_ar'])
                    ->sortable('name')
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('scope')
                    ->label('النطاق')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->visible(fn () => true)
                    ->placeholder('متعدد المستأجرين')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('المدة')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('البدء')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('المتبقي')
                    ->suffix(' يوم')
                    ->color(fn ($state) => $state <= 3 ? 'danger' : ($state <= 7 ? 'warning' : 'success')),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('التقدم')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'gray')),

                Tables\Columns\TextColumn::make('active_branches_count')
                    ->label('الفروع')
                    ->counts('activeBranches')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CompetitionStatus::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('scope')
                    ->label('النطاق')
                    ->options(CompetitionScope::class),

                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('المستأجر')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('active')
                    ->label('النشطة فقط')
                    ->query(fn (Builder $query) => $query->active())
                    ->toggle(),

                Tables\Filters\Filter::make('ending_soon')
                    ->label('تنتهي قريباً')
                    ->query(fn (Builder $query) => $query->active()->where('end_date', '<=', now()->addDays(7)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (InternalCompetition $record) => $record->status->canEdit()),

                    Tables\Actions\Action::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تفعيل المسابقة')
                        ->modalDescription('هل أنت متأكد من تفعيل هذه المسابقة؟ لن يمكنك التعديل على البيانات الأساسية بعد التفعيل.')
                        ->visible(fn (InternalCompetition $record) => $record->status->canActivate())
                        ->action(function (InternalCompetition $record) {
                            try {
                                app(CompetitionService::class)->activate($record);
                                Notification::make()
                                    ->title('تم تفعيل المسابقة بنجاح')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في تفعيل المسابقة')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('end')
                        ->label('إنهاء')
                        ->icon('heroicon-o-stop')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('إنهاء المسابقة')
                        ->modalDescription('هل أنت متأكد من إنهاء هذه المسابقة الآن؟ سيتم حساب النتائج النهائية وتحديد الفائزين.')
                        ->visible(fn (InternalCompetition $record) => $record->status === CompetitionStatus::ACTIVE)
                        ->action(function (InternalCompetition $record) {
                            try {
                                app(CompetitionService::class)->end($record);
                                Notification::make()
                                    ->title('تم إنهاء المسابقة')
                                    ->body('جاري حساب النتائج النهائية...')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في إنهاء المسابقة')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('publish')
                        ->label('نشر النتائج')
                        ->icon('heroicon-o-megaphone')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('نشر نتائج المسابقة')
                        ->modalDescription('سيتم نشر النتائج وإرسال إشعارات للفائزين.')
                        ->visible(fn (InternalCompetition $record) => $record->status === CompetitionStatus::ENDED)
                        ->action(function (InternalCompetition $record) {
                            try {
                                app(CompetitionService::class)->publish($record);
                                Notification::make()
                                    ->title('تم نشر النتائج بنجاح')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في نشر النتائج')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('cancel')
                        ->label('إلغاء')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('إلغاء المسابقة')
                        ->modalDescription('هل أنت متأكد من إلغاء هذه المسابقة؟ هذا الإجراء لا يمكن التراجع عنه.')
                        ->visible(fn (InternalCompetition $record) => $record->status->canCancel())
                        ->action(function (InternalCompetition $record) {
                            try {
                                app(CompetitionService::class)->cancel($record);
                                Notification::make()
                                    ->title('تم إلغاء المسابقة')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في إلغاء المسابقة')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('duplicate')
                        ->label('نسخ')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (InternalCompetition $record) {
                            try {
                                $newCompetition = app(CompetitionService::class)->duplicate($record);
                                Notification::make()
                                    ->title('تم نسخ المسابقة')
                                    ->body("المسابقة الجديدة: {$newCompetition->display_name}")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('خطأ في نسخ المسابقة')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (InternalCompetition $record) => in_array($record->status, [CompetitionStatus::DRAFT, CompetitionStatus::CANCELLED])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => true),
                ]),
            ])
            ->emptyStateHeading('لا توجد مسابقات')
            ->emptyStateDescription('ابدأ بإنشاء مسابقة جديدة لتحفيز فروعك')
            ->emptyStateIcon('heroicon-o-trophy')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('إنشاء مسابقة جديدة'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المسابقة')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('display_name')
                                    ->label('اسم المسابقة'),

                                Infolists\Components\TextEntry::make('scope')
                                    ->label('النطاق')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge(),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->date(),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->date(),

                                Infolists\Components\TextEntry::make('duration_in_days')
                                    ->label('المدة')
                                    ->suffix(' يوم'),

                                Infolists\Components\TextEntry::make('remaining_days')
                                    ->label('المتبقي')
                                    ->suffix(' يوم')
                                    ->visible(fn (InternalCompetition $record) => $record->status === CompetitionStatus::ACTIVE),
                            ]),
                    ]),

                Infolists\Components\Section::make('إحصائيات')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('active_tenants_count')
                                    ->label('المستأجرين')
                                    ->state(fn (InternalCompetition $record) => $record->activeTenants()->count()),

                                Infolists\Components\TextEntry::make('active_branches_count')
                                    ->label('الفروع')
                                    ->state(fn (InternalCompetition $record) => $record->activeBranches()->count()),

                                Infolists\Components\TextEntry::make('prizes_count')
                                    ->label('الجوائز')
                                    ->state(fn (InternalCompetition $record) => $record->prizes()->count()),

                                Infolists\Components\TextEntry::make('winners_count')
                                    ->label('الفائزين')
                                    ->state(fn (InternalCompetition $record) => $record->winners()->count()),
                            ]),
                    ]),

                Infolists\Components\Section::make('المعايير المفعّلة')
                    ->schema([
                        Infolists\Components\TextEntry::make('enabled_metrics')
                            ->label('')
                            ->state(function (InternalCompetition $record) {
                                return collect($record->enabled_metrics)
                                    ->map(fn ($metric) => $metric->getLabel())
                                    ->join('، ');
                            }),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TenantsRelationManager::class,
            RelationManagers\BranchesRelationManager::class,
            RelationManagers\PrizesRelationManager::class,
            RelationManagers\WinnersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternalCompetitions::route('/'),
            'create' => Pages\CreateInternalCompetition::route('/create'),
            'view' => Pages\ViewInternalCompetition::route('/{record}'),
            'edit' => Pages\EditInternalCompetition::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
