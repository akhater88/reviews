<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\FeatureCategory;
use App\Filament\SuperAdmin\Resources\PlanResource\Pages;
use App\Filament\SuperAdmin\Resources\PlanResource\RelationManagers;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanLimit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'الباقات';
    protected static ?string $modelLabel = 'باقة';
    protected static ?string $pluralModelLabel = 'الباقات';
    protected static ?string $navigationGroup = 'الاشتراكات';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Plan')
                    ->tabs([
                        // Tab 1: Basic Information
                        Forms\Components\Tabs\Tab::make('المعلومات الأساسية')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('الاسم (English)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Professional')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                                        $set('slug', Str::slug($state))
                                                    ),

                                                Forms\Components\TextInput::make('name_ar')
                                                    ->label('الاسم (عربي)')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('الاحترافي'),

                                                Forms\Components\TextInput::make('slug')
                                                    ->label('المعرف (Slug)')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->placeholder('professional')
                                                    ->helperText('يستخدم في الروابط'),

                                                Forms\Components\Select::make('color')
                                                    ->label('اللون')
                                                    ->options([
                                                        'gray' => 'رمادي',
                                                        'primary' => 'أزرق',
                                                        'info' => 'سماوي',
                                                        'success' => 'أخضر',
                                                        'warning' => 'أصفر',
                                                        'danger' => 'أحمر',
                                                    ])
                                                    ->default('primary')
                                                    ->native(false),
                                            ]),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Textarea::make('description')
                                                    ->label('الوصف (English)')
                                                    ->rows(2)
                                                    ->maxLength(500)
                                                    ->placeholder('Perfect for growing restaurant chains'),

                                                Forms\Components\Textarea::make('description_ar')
                                                    ->label('الوصف (عربي)')
                                                    ->rows(2)
                                                    ->maxLength(500)
                                                    ->placeholder('مثالي لسلاسل المطاعم النامية'),
                                            ]),

                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('نشط')
                                                    ->default(true)
                                                    ->helperText('إظهار الباقة للعملاء'),

                                                Forms\Components\Toggle::make('is_popular')
                                                    ->label('الأكثر شعبية')
                                                    ->default(false)
                                                    ->helperText('إبراز الباقة كخيار مميز'),

                                                Forms\Components\Toggle::make('is_free')
                                                    ->label('مجانية')
                                                    ->default(false)
                                                    ->helperText('باقة بدون رسوم')
                                                    ->live(),

                                                Forms\Components\Toggle::make('is_custom')
                                                    ->label('مخصصة')
                                                    ->default(false)
                                                    ->helperText('باقة خاصة للمؤسسات'),
                                            ]),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('الترتيب')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('الأرقام الأصغر تظهر أولاً'),

                                        Forms\Components\TextInput::make('icon')
                                            ->label('الأيقونة')
                                            ->placeholder('heroicon-o-star')
                                            ->helperText('اسم أيقونة Heroicon (اختياري)'),
                                    ]),
                            ]),

                        // Tab 2: Pricing
                        Forms\Components\Tabs\Tab::make('التسعير')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('الأسعار بالريال السعودي (SAR)')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('price_monthly_sar')
                                                    ->label('السعر الشهري')
                                                    ->numeric()
                                                    ->prefix('ر.س')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->disabled(fn ($get) => $get('is_free')),

                                                Forms\Components\TextInput::make('price_yearly_sar')
                                                    ->label('السعر السنوي')
                                                    ->numeric()
                                                    ->prefix('ر.س')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->helperText(function ($get) {
                                                        $monthly = floatval($get('price_monthly_sar'));
                                                        $yearly = floatval($get('price_yearly_sar'));
                                                        if ($monthly > 0 && $yearly > 0) {
                                                            $monthlyTotal = $monthly * 12;
                                                            $discount = round((($monthlyTotal - $yearly) / $monthlyTotal) * 100);
                                                            return $discount > 0 ? "توفير {$discount}%" : '';
                                                        }
                                                        return '';
                                                    })
                                                    ->disabled(fn ($get) => $get('is_free')),
                                            ]),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('الأسعار بالدولار الأمريكي (USD)')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('price_monthly_usd')
                                                    ->label('السعر الشهري')
                                                    ->numeric()
                                                    ->prefix('$')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->disabled(fn ($get) => $get('is_free')),

                                                Forms\Components\TextInput::make('price_yearly_usd')
                                                    ->label('السعر السنوي')
                                                    ->numeric()
                                                    ->prefix('$')
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->disabled(fn ($get) => $get('is_free')),
                                            ]),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Forms\Components\Section::make('حاسبة التسعير')
                                    ->schema([
                                        Forms\Components\Placeholder::make('pricing_helper')
                                            ->content(function ($get) {
                                                $monthlySar = floatval($get('price_monthly_sar'));
                                                $yearlySar = floatval($get('price_yearly_sar'));

                                                if ($monthlySar <= 0) {
                                                    return 'أدخل السعر الشهري لرؤية الحسابات';
                                                }

                                                $suggestedYearly = $monthlySar * 10; // 2 months free
                                                $suggestedUsdMonthly = round($monthlySar / 3.75, 2);
                                                $suggestedUsdYearly = round($suggestedYearly / 3.75, 2);

                                                return view('filament.super-admin.components.pricing-calculator', [
                                                    'monthlySar' => $monthlySar,
                                                    'yearlySar' => $yearlySar,
                                                    'suggestedYearly' => $suggestedYearly,
                                                    'suggestedUsdMonthly' => $suggestedUsdMonthly,
                                                    'suggestedUsdYearly' => $suggestedUsdYearly,
                                                ]);
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        // Tab 3: Limits
                        Forms\Components\Tabs\Tab::make('الحدود')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Forms\Components\Section::make('حدود الموارد')
                                    ->description('القيمة -1 تعني غير محدود')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('limits.max_branches')
                                                    ->label('الفروع الخاصة')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(-1)
                                                    ->helperText('-1 = غير محدود')
                                                    ->suffixIcon('heroicon-o-building-office'),

                                                Forms\Components\TextInput::make('limits.max_competitors')
                                                    ->label('فروع المنافسين')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(-1)
                                                    ->helperText('-1 = غير محدود')
                                                    ->suffixIcon('heroicon-o-flag'),

                                                Forms\Components\TextInput::make('limits.max_users')
                                                    ->label('المستخدمين')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(-1)
                                                    ->helperText('-1 = غير محدود')
                                                    ->suffixIcon('heroicon-o-users'),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('حدود الاستخدام الشهري')
                                    ->description('يتم إعادة تعيين هذه الحدود شهرياً')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('limits.max_reviews_sync')
                                                    ->label('مزامنة المراجعات')
                                                    ->numeric()
                                                    ->default(100)
                                                    ->minValue(-1)
                                                    ->helperText('عدد المراجعات شهرياً')
                                                    ->suffixIcon('heroicon-o-arrow-path'),

                                                Forms\Components\TextInput::make('limits.max_ai_replies')
                                                    ->label('ردود AI')
                                                    ->numeric()
                                                    ->default(10)
                                                    ->minValue(-1)
                                                    ->helperText('ردود الذكاء الاصطناعي')
                                                    ->suffixIcon('heroicon-o-sparkles'),

                                                Forms\Components\TextInput::make('limits.max_ai_tokens')
                                                    ->label('رموز AI')
                                                    ->numeric()
                                                    ->default(10000)
                                                    ->minValue(-1)
                                                    ->helperText('إجمالي الرموز شهرياً')
                                                    ->suffixIcon('heroicon-o-cpu-chip'),

                                                Forms\Components\TextInput::make('limits.max_api_calls')
                                                    ->label('طلبات API')
                                                    ->numeric()
                                                    ->default(100)
                                                    ->minValue(-1)
                                                    ->helperText('طلبات الواجهة البرمجية')
                                                    ->suffixIcon('heroicon-o-code-bracket'),

                                                Forms\Components\TextInput::make('limits.max_analysis_runs')
                                                    ->label('تشغيل التحليل')
                                                    ->numeric()
                                                    ->default(5)
                                                    ->minValue(-1)
                                                    ->helperText('تحليلات شهرية')
                                                    ->suffixIcon('heroicon-o-chart-bar'),

                                                Forms\Components\TextInput::make('limits.analysis_retention_days')
                                                    ->label('حفظ البيانات')
                                                    ->numeric()
                                                    ->default(30)
                                                    ->minValue(-1)
                                                    ->suffix('يوم')
                                                    ->helperText('مدة الاحتفاظ بالتحليلات')
                                                    ->suffixIcon('heroicon-o-clock'),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('قوالب الحدود السريعة')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('preset_free')
                                                ->label('مجانية')
                                                ->color('gray')
                                                ->action(function ($set) {
                                                    $set('limits.max_branches', 1);
                                                    $set('limits.max_competitors', 0);
                                                    $set('limits.max_users', 1);
                                                    $set('limits.max_reviews_sync', 100);
                                                    $set('limits.max_ai_replies', 10);
                                                    $set('limits.max_ai_tokens', 10000);
                                                    $set('limits.max_api_calls', 100);
                                                    $set('limits.max_analysis_runs', 2);
                                                    $set('limits.analysis_retention_days', 30);
                                                }),

                                            Forms\Components\Actions\Action::make('preset_starter')
                                                ->label('مبتدئ')
                                                ->color('info')
                                                ->action(function ($set) {
                                                    $set('limits.max_branches', 3);
                                                    $set('limits.max_competitors', 2);
                                                    $set('limits.max_users', 3);
                                                    $set('limits.max_reviews_sync', 500);
                                                    $set('limits.max_ai_replies', 50);
                                                    $set('limits.max_ai_tokens', 50000);
                                                    $set('limits.max_api_calls', 500);
                                                    $set('limits.max_analysis_runs', 10);
                                                    $set('limits.analysis_retention_days', 90);
                                                }),

                                            Forms\Components\Actions\Action::make('preset_pro')
                                                ->label('احترافي')
                                                ->color('primary')
                                                ->action(function ($set) {
                                                    $set('limits.max_branches', 10);
                                                    $set('limits.max_competitors', 5);
                                                    $set('limits.max_users', 10);
                                                    $set('limits.max_reviews_sync', 2000);
                                                    $set('limits.max_ai_replies', 200);
                                                    $set('limits.max_ai_tokens', 200000);
                                                    $set('limits.max_api_calls', 2000);
                                                    $set('limits.max_analysis_runs', 30);
                                                    $set('limits.analysis_retention_days', 365);
                                                }),

                                            Forms\Components\Actions\Action::make('preset_enterprise')
                                                ->label('مؤسسات')
                                                ->color('warning')
                                                ->action(function ($set) {
                                                    $set('limits.max_branches', -1);
                                                    $set('limits.max_competitors', 20);
                                                    $set('limits.max_users', -1);
                                                    $set('limits.max_reviews_sync', -1);
                                                    $set('limits.max_ai_replies', -1);
                                                    $set('limits.max_ai_tokens', -1);
                                                    $set('limits.max_api_calls', -1);
                                                    $set('limits.max_analysis_runs', -1);
                                                    $set('limits.analysis_retention_days', -1);
                                                }),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        // Tab 4: Features
                        Forms\Components\Tabs\Tab::make('الميزات')
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Forms\Components\Section::make('تحديد الميزات المتاحة')
                                    ->description('اختر الميزات التي سيحصل عليها المشتركون في هذه الباقة')
                                    ->schema([
                                        Forms\Components\Repeater::make('plan_features_data')
                                            ->label('')
                                            ->schema([
                                                Forms\Components\Select::make('feature_id')
                                                    ->label('الميزة')
                                                    ->options(Feature::active()->pluck('name_ar', 'id'))
                                                    ->required()
                                                    ->searchable()
                                                    ->native(false)
                                                    ->columnSpan(2),

                                                Forms\Components\Toggle::make('is_enabled')
                                                    ->label('مفعّلة')
                                                    ->default(true)
                                                    ->inline(false),

                                                Forms\Components\TextInput::make('limit_value')
                                                    ->label('حد مخصص')
                                                    ->numeric()
                                                    ->placeholder('اختياري')
                                                    ->helperText('قيمة خاصة بهذه الميزة'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->addActionLabel('إضافة ميزة')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string =>
                                                Feature::find($state['feature_id'])?->name_ar ?? 'ميزة جديدة'
                                            ),
                                    ]),

                                Forms\Components\Section::make('إضافة سريعة')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('add_reviews_features')
                                                ->label('ميزات المراجعات')
                                                ->icon('heroicon-o-chat-bubble-left-right')
                                                ->color('info')
                                                ->action(function ($get, $set) {
                                                    $features = Feature::where('category', 'reviews')
                                                        ->pluck('id')
                                                        ->map(fn ($id) => [
                                                            'feature_id' => $id,
                                                            'is_enabled' => true,
                                                            'limit_value' => null,
                                                        ])
                                                        ->toArray();

                                                    $existing = $get('plan_features_data') ?? [];
                                                    $set('plan_features_data', array_merge($existing, $features));
                                                }),

                                            Forms\Components\Actions\Action::make('add_analytics_features')
                                                ->label('ميزات التحليلات')
                                                ->icon('heroicon-o-chart-bar')
                                                ->color('success')
                                                ->action(function ($get, $set) {
                                                    $features = Feature::where('category', 'analytics')
                                                        ->pluck('id')
                                                        ->map(fn ($id) => [
                                                            'feature_id' => $id,
                                                            'is_enabled' => true,
                                                            'limit_value' => null,
                                                        ])
                                                        ->toArray();

                                                    $existing = $get('plan_features_data') ?? [];
                                                    $set('plan_features_data', array_merge($existing, $features));
                                                }),

                                            Forms\Components\Actions\Action::make('add_ai_features')
                                                ->label('ميزات AI')
                                                ->icon('heroicon-o-sparkles')
                                                ->color('warning')
                                                ->action(function ($get, $set) {
                                                    $features = Feature::where('category', 'ai')
                                                        ->pluck('id')
                                                        ->map(fn ($id) => [
                                                            'feature_id' => $id,
                                                            'is_enabled' => true,
                                                            'limit_value' => null,
                                                        ])
                                                        ->toArray();

                                                    $existing = $get('plan_features_data') ?? [];
                                                    $set('plan_features_data', array_merge($existing, $features));
                                                }),

                                            Forms\Components\Actions\Action::make('add_all_features')
                                                ->label('جميع الميزات')
                                                ->icon('heroicon-o-check-circle')
                                                ->color('primary')
                                                ->action(function ($set) {
                                                    $features = Feature::active()
                                                        ->pluck('id')
                                                        ->map(fn ($id) => [
                                                            'feature_id' => $id,
                                                            'is_enabled' => true,
                                                            'limit_value' => null,
                                                        ])
                                                        ->toArray();

                                                    $set('plan_features_data', $features);
                                                }),

                                            Forms\Components\Actions\Action::make('clear_features')
                                                ->label('مسح الكل')
                                                ->icon('heroicon-o-trash')
                                                ->color('danger')
                                                ->requiresConfirmation()
                                                ->action(fn ($set) => $set('plan_features_data', [])),
                                        ]),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name)
                    ->weight('bold')
                    ->color(fn ($record) => $record->color),

                Tables\Columns\TextColumn::make('price_monthly_sar')
                    ->label('شهري (SAR)')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->is_free ? 'مجاني' : 'ر.س ' . number_format($state, 0)
                    )
                    ->color(fn ($record) => $record->is_free ? 'success' : null),

                Tables\Columns\TextColumn::make('price_yearly_sar')
                    ->label('سنوي (SAR)')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->is_free ? 'مجاني' : 'ر.س ' . number_format($state, 0)
                    )
                    ->color(fn ($record) => $record->is_free ? 'success' : null),

                Tables\Columns\TextColumn::make('limits.max_branches')
                    ->label('الفروع')
                    ->formatStateUsing(fn ($state) => $state == -1 ? '∞' : $state)
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('limits.max_competitors')
                    ->label('المنافسين')
                    ->formatStateUsing(fn ($state) => $state == -1 ? '∞' : $state)
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('المشتركين')
                    ->counts('subscriptions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label('مميزة')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشطة')
                    ->falseLabel('غير نشطة'),

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('النوع')
                    ->trueLabel('مجانية')
                    ->falseLabel('مدفوعة'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),

                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),

                    Tables\Actions\Action::make('duplicate')
                        ->label('نسخ')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('name')
                                ->label('الاسم الجديد (English)')
                                ->required()
                                ->default(fn ($record) => $record->name . ' - Copy'),

                            Forms\Components\TextInput::make('name_ar')
                                ->label('الاسم الجديد (عربي)')
                                ->required()
                                ->default(fn ($record) => $record->name_ar . ' - نسخة'),
                        ])
                        ->action(function ($record, array $data) {
                            $newPlan = $record->replicate();
                            $newPlan->name = $data['name'];
                            $newPlan->name_ar = $data['name_ar'];
                            $newPlan->slug = Str::slug($data['name']);
                            $newPlan->is_popular = false;
                            $newPlan->save();

                            // Copy limits
                            if ($record->limits) {
                                $newLimits = $record->limits->replicate();
                                $newLimits->plan_id = $newPlan->id;
                                $newLimits->save();
                            }

                            // Copy features
                            foreach ($record->planFeatures as $pf) {
                                $newPlan->planFeatures()->create([
                                    'feature_id' => $pf->feature_id,
                                    'is_enabled' => $pf->is_enabled,
                                    'limit_value' => $pf->limit_value,
                                ]);
                            }

                            Notification::make()
                                ->title('تم نسخ الباقة')
                                ->success()
                                ->send();

                            return redirect()->route('filament.super-admin.resources.plans.edit', $newPlan);
                        }),

                    Tables\Actions\Action::make('toggle_popular')
                        ->label(fn ($record) => $record->is_popular ? 'إلغاء التمييز' : 'تمييز كشعبية')
                        ->icon(fn ($record) => $record->is_popular ? 'heroicon-o-star' : 'heroicon-o-star')
                        ->color(fn ($record) => $record->is_popular ? 'gray' : 'warning')
                        ->action(function ($record) {
                            // Only one plan can be popular
                            if (!$record->is_popular) {
                                Plan::where('is_popular', true)->update(['is_popular' => false]);
                            }
                            $record->update(['is_popular' => !$record->is_popular]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('حذف')
                        ->before(function ($record, Tables\Actions\DeleteAction $action) {
                            if ($record->subscriptions()->exists()) {
                                Notification::make()
                                    ->title('لا يمكن حذف الباقة')
                                    ->body('هناك مشتركين مرتبطين بهذه الباقة')
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('تعطيل')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف'),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('معلومات الباقة')
                        ->schema([
                            Infolists\Components\TextEntry::make('name_ar')
                                ->label('الاسم (عربي)')
                                ->weight('bold')
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                            Infolists\Components\TextEntry::make('name')
                                ->label('الاسم (English)'),

                            Infolists\Components\TextEntry::make('slug')
                                ->label('المعرف')
                                ->badge()
                                ->color('gray'),

                            Infolists\Components\TextEntry::make('description_ar')
                                ->label('الوصف')
                                ->columnSpanFull(),

                            Infolists\Components\Grid::make(4)
                                ->schema([
                                    Infolists\Components\IconEntry::make('is_active')
                                        ->label('نشطة')
                                        ->boolean(),

                                    Infolists\Components\IconEntry::make('is_popular')
                                        ->label('مميزة')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-star')
                                        ->trueColor('warning'),

                                    Infolists\Components\IconEntry::make('is_free')
                                        ->label('مجانية')
                                        ->boolean(),

                                    Infolists\Components\IconEntry::make('is_custom')
                                        ->label('مخصصة')
                                        ->boolean(),
                                ]),
                        ])
                        ->columns(2),

                    Infolists\Components\Section::make('التسعير')
                        ->schema([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('price_monthly_sar')
                                        ->label('شهري (SAR)')
                                        ->formatStateUsing(fn ($state, $record) =>
                                            $record->is_free ? 'مجاني' : 'ر.س ' . number_format($state, 2)
                                        ),

                                    Infolists\Components\TextEntry::make('price_yearly_sar')
                                        ->label('سنوي (SAR)')
                                        ->formatStateUsing(fn ($state, $record) =>
                                            $record->is_free ? 'مجاني' : 'ر.س ' . number_format($state, 2)
                                        ),

                                    Infolists\Components\TextEntry::make('price_monthly_usd')
                                        ->label('شهري (USD)')
                                        ->formatStateUsing(fn ($state, $record) =>
                                            $record->is_free ? 'Free' : '$ ' . number_format($state, 2)
                                        ),

                                    Infolists\Components\TextEntry::make('price_yearly_usd')
                                        ->label('سنوي (USD)')
                                        ->formatStateUsing(fn ($state, $record) =>
                                            $record->is_free ? 'Free' : '$ ' . number_format($state, 2)
                                        ),
                                ]),
                        ]),
                ])
                ->columnSpan(2),

                Infolists\Components\Group::make([
                    Infolists\Components\Section::make('الحدود')
                        ->schema([
                            Infolists\Components\TextEntry::make('limits.max_branches')
                                ->label('الفروع')
                                ->formatStateUsing(fn ($state) => $state == -1 ? 'غير محدود' : $state)
                                ->icon('heroicon-o-building-office'),

                            Infolists\Components\TextEntry::make('limits.max_competitors')
                                ->label('المنافسين')
                                ->formatStateUsing(fn ($state) => $state == -1 ? 'غير محدود' : $state)
                                ->icon('heroicon-o-flag'),

                            Infolists\Components\TextEntry::make('limits.max_users')
                                ->label('المستخدمين')
                                ->formatStateUsing(fn ($state) => $state == -1 ? 'غير محدود' : $state)
                                ->icon('heroicon-o-users'),

                            Infolists\Components\TextEntry::make('limits.max_ai_replies')
                                ->label('ردود AI')
                                ->formatStateUsing(fn ($state) => $state == -1 ? 'غير محدود' : $state)
                                ->icon('heroicon-o-sparkles'),

                            Infolists\Components\TextEntry::make('limits.analysis_retention_days')
                                ->label('حفظ البيانات')
                                ->formatStateUsing(fn ($state) => $state == -1 ? 'غير محدود' : $state . ' يوم')
                                ->icon('heroicon-o-clock'),
                        ]),

                    Infolists\Components\Section::make('الإحصائيات')
                        ->schema([
                            Infolists\Components\TextEntry::make('subscriptions_count')
                                ->label('المشتركين')
                                ->state(fn ($record) => $record->subscriptions()->count())
                                ->badge()
                                ->color('success'),

                            Infolists\Components\TextEntry::make('active_subscriptions')
                                ->label('النشطين')
                                ->state(fn ($record) => $record->subscriptions()
                                    ->whereIn('status', ['active', 'trial'])
                                    ->count()
                                )
                                ->badge()
                                ->color('primary'),

                            Infolists\Components\TextEntry::make('features_count')
                                ->label('الميزات')
                                ->state(fn ($record) => $record->planFeatures()
                                    ->where('is_enabled', true)
                                    ->count()
                                )
                                ->badge()
                                ->color('info'),
                        ]),
                ])
                ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FeaturesRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'view' => Pages\ViewPlan::route('/{record}'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
