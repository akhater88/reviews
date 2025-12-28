<?php

namespace App\Filament\Resources;

use App\Enums\BranchType;
use App\Filament\Resources\BranchResource\Pages;
use App\Jobs\SyncBranchReviewsJob;
use App\Models\Branch;
use App\Services\Analysis\AnalysisPipelineService;
use App\Services\Google\PlaceSearchService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'الفروع';

    protected static ?string $modelLabel = 'فرع';

    protected static ?string $pluralModelLabel = 'الفروع';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('البحث عن الفرع')
                    ->description('ابحث عن المطعم في Google Places لملء البيانات تلقائياً')
                    ->schema([
                        Forms\Components\Select::make('selected_place')
                            ->label('ابحث عن المطعم في Google')
                            ->placeholder('اكتب اسم المطعم للبحث...')
                            ->searchable()
                            ->searchDebounce(500)
                            ->searchPrompt('اكتب اسم المطعم أو الفرع للبحث')
                            ->noSearchResultsMessage('لم يتم العثور على نتائج')
                            ->loadingMessage('جاري البحث...')
                            ->getSearchResultsUsing(function (string $search): array {
                                if (strlen($search) < 3) {
                                    return [];
                                }

                                try {
                                    $placeSearch = app(PlaceSearchService::class);
                                    $results = $placeSearch->searchPlace($search);

                                    $options = [];
                                    foreach ($results as $place) {
                                        $placeId = $place['place_id'] ?? null;
                                        if (!$placeId) continue;

                                        $name = $place['name'] ?? 'غير معروف';
                                        $address = $place['full_address'] ?? $place['address'] ?? '';
                                        $rating = isset($place['rating']) ? " ★ {$place['rating']}" : '';

                                        // Encode place data as JSON in the value
                                        $placeData = json_encode([
                                            'place_id' => $placeId,
                                            'name' => $name,
                                            'address' => $address,
                                            'city' => $place['city'] ?? null,
                                            'country' => $place['country'] ?? 'Saudi Arabia',
                                            'lat' => $place['latitude'] ?? null,
                                            'lng' => $place['longitude'] ?? null,
                                            'phone' => $place['phone'] ?? null,
                                            'website' => $place['site'] ?? $place['website'] ?? null,
                                        ]);

                                        $label = $name . $rating;
                                        if ($address) {
                                            $label .= "\n📍 " . mb_substr($address, 0, 60);
                                        }

                                        $options[$placeData] = $label;
                                    }

                                    return $options;
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    return;
                                }

                                $placeData = json_decode($state, true);
                                if (!$placeData) return;

                                $placeId = $placeData['place_id'] ?? null;

                                // Fetch full details for accurate city/country
                                if ($placeId) {
                                    try {
                                        $placeSearch = app(PlaceSearchService::class);
                                        $details = $placeSearch->getPlaceDetails($placeId);

                                        if ($details) {
                                            $set('name', $details['name'] ?? $placeData['name'] ?? null);
                                            $set('google_place_id', $details['place_id'] ?? $placeId);
                                            $set('address', $details['address'] ?? $placeData['address'] ?? null);
                                            $set('city', $details['city'] ?? null);
                                            $set('country', $details['country'] ?? null);
                                            $set('lat', $details['latitude'] ?? null);
                                            $set('lng', $details['longitude'] ?? null);
                                            $set('phone', $details['phone'] ?? null);
                                            $set('website', $details['website'] ?? $details['site'] ?? null);
                                            return;
                                        }
                                    } catch (\Exception $e) {
                                        // Fallback to search data if details fetch fails
                                    }
                                }

                                // Fallback to search result data
                                $set('name', $placeData['name'] ?? null);
                                $set('google_place_id', $placeId);
                                $set('address', $placeData['address'] ?? null);
                                $set('city', $placeData['city'] ?? null);
                                $set('country', $placeData['country'] ?? null);
                                $set('lat', $placeData['lat'] ?? null);
                                $set('lng', $placeData['lng'] ?? null);
                                $set('phone', $placeData['phone'] ?? null);
                                $set('website', $placeData['website'] ?? null);
                            })
                            ->helperText('ابحث واختر المطعم من نتائج Google Places')
                            ->columnSpanFull()
                            ->visibleOn('create'),
                    ])
                    ->visibleOn('create'),

                Forms\Components\Section::make('نوع الفرع')
                    ->description('حدد نوع الفرع (فرعي أو منافس)')
                    ->schema([
                        Forms\Components\Radio::make('branch_type')
                            ->label('نوع الفرع')
                            ->options([
                                'owned' => 'فرعي (تابع لي) - فرع من فروع مطعمي',
                                'competitor' => 'منافس - فرع لمطعم منافس أريد متابعته',
                            ])
                            ->default('owned')
                            ->required()
                            ->live()
                            ->descriptions([
                                'owned' => 'اختر هذا إذا كان الفرع تابعاً لمطعمك',
                                'competitor' => 'اختر هذا لمتابعة مراجعات المنافسين ومقارنتها بفروعك',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('linked_branch_id')
                            ->label('ربط مع فرع للمقارنة')
                            ->options(function () {
                                $tenantId = Auth::user()?->tenant_id;
                                if (!$tenantId) {
                                    return [];
                                }

                                return Branch::where('tenant_id', $tenantId)
                                    ->where('branch_type', 'owned')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('اختر فرع للمقارنة')
                            ->helperText('اختر أحد فروعك لمقارنة أداء المنافس معه')
                            ->visible(fn (Forms\Get $get) => $get('branch_type') === 'competitor')
                            ->required(fn (Forms\Get $get) => $get('branch_type') === 'competitor'),
                    ])->columns(1),

                Forms\Components\Section::make('معلومات الفرع')
                    ->description('المعلومات الأساسية للفرع')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الفرع')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('google_place_id')
                            ->label('معرف Google Place')
                            ->maxLength(255)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url()
                            ->maxLength(255)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('الموقع')
                    ->description('معلومات الموقع الجغرافي')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('city')
                            ->label('المدينة')
                            ->maxLength(100)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('country')
                            ->label('الدولة')
                            ->maxLength(100)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('lat')
                            ->label('خط العرض')
                            ->numeric()
                            ->step(0.00000001)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('lng')
                            ->label('خط الطول')
                            ->numeric()
                            ->step(0.00000001)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('الفرع نشط')
                            ->default(true)
                            ->helperText('الفروع غير النشطة لن تظهر في التقارير'),

                        Forms\Components\Select::make('status')
                            ->label('حالة الأداء')
                            ->options([
                                'excellent' => 'ممتاز',
                                'good' => 'جيد',
                                'average' => 'متوسط',
                                'needs_improvement' => 'يحتاج تحسين',
                            ])
                            ->default('good'),
                    ])->columns(2),

                Forms\Components\Section::make('المستخدمين')
                    ->description('حدد المستخدمين المسؤولين عن هذا الفرع')
                    ->schema([
                        Forms\Components\CheckboxList::make('users')
                            ->label('مدراء الفرع')
                            ->relationship('users', 'name')
                            ->columns(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الفرع')
                    ->description(fn (Branch $record): string => $record->name_ar ?? '')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('المدينة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('country')
                    ->label('الدولة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . ' ★' : '—')
                    ->color(fn ($state) => match(true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'info',
                        $state >= 3.0 => 'warning',
                        $state > 0 => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('total_reviews')
                    ->label('المراجعات')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('performance_score')
                    ->label('الأداء')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn (int $state): string => match(true) {
                        $state >= 85 => 'success',
                        $state >= 70 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'excellent' => 'ممتاز',
                        'good' => 'جيد',
                        'average' => 'متوسط',
                        'needs_improvement' => 'يحتاج تحسين',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'excellent' => 'success',
                        'good' => 'info',
                        'average' => 'warning',
                        'needs_improvement' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sync_status')
                    ->label('حالة المزامنة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'غير محدد')
                    ->color(fn ($state) => $state?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('آخر مزامنة')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('لم تتم المزامنة')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الأداء')
                    ->options([
                        'excellent' => 'ممتاز',
                        'good' => 'جيد',
                        'average' => 'متوسط',
                        'needs_improvement' => 'يحتاج تحسين',
                    ]),
                Tables\Filters\SelectFilter::make('city')
                    ->label('المدينة')
                    ->options(fn () => Branch::distinct()->pluck('city', 'city')->filter()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Tables\Actions\Action::make('syncReviews')
                    ->label('مزامنة المراجعات')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('مزامنة المراجعات')
                    ->modalDescription('سيتم جلب أحدث المراجعات. قد تستغرق العملية بضع دقائق.')
                    ->modalSubmitActionLabel('بدء المزامنة')
                    ->visible(fn (Branch $record): bool => !empty($record->google_place_id))
                    ->action(function (Branch $record) {
                        SyncBranchReviewsJob::dispatch($record)->onQueue('reviews');

                        Notification::make()
                            ->title('تم بدء المزامنة')
                            ->body("جاري مزامنة مراجعات {$record->name}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('analyzeReviews')
                    ->label('تحليل المراجعات')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تحليل المراجعات بالذكاء الاصطناعي')
                    ->modalDescription('سيتم تحليل المراجعات باستخدام الذكاء الاصطناعي لاستخراج رؤى وتوصيات. قد تستغرق العملية عدة دقائق.')
                    ->modalSubmitActionLabel('بدء التحليل')
                    ->visible(fn (Branch $record): bool => $record->reviews()->exists())
                    ->disabled(fn (Branch $record): bool => app(AnalysisPipelineService::class)->hasActiveAnalysis($record))
                    ->action(function (Branch $record) {
                        try {
                            $service = app(AnalysisPipelineService::class);
                            $overview = $service->startAnalysis($record);

                            Notification::make()
                                ->title('تم بدء التحليل')
                                ->body("جاري تحليل مراجعات {$record->name} - سيتم إشعارك عند الانتهاء")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل بدء التحليل')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('viewReport')
                    ->label('عرض التقرير')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('success')
                    ->url(fn (Branch $record): string => \App\Filament\Pages\BranchReportPage::getUrl(['branch' => $record]))
                    ->visible(fn (Branch $record): bool => $record->reviews()->exists()),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا يوجد فروع')
            ->emptyStateDescription('قم بإضافة فرع جديد للبدء')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 0 ? 'success' : 'warning';
    }
}
