<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\FeatureCategory;
use App\Filament\SuperAdmin\Resources\FeatureResource\Pages;
use App\Models\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'الميزات';
    protected static ?string $modelLabel = 'ميزة';
    protected static ?string $pluralModelLabel = 'الميزات';
    protected static ?string $navigationGroup = 'الاشتراكات';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الميزة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('المفتاح (Key)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->placeholder('monthly_rankings')
                                    ->helperText('يستخدم في الكود للتحقق من الصلاحيات')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                        $set('key', Str::snake($state))
                                    ),

                                Forms\Components\Select::make('category')
                                    ->label('التصنيف')
                                    ->options(collect(FeatureCategory::cases())->mapWithKeys(fn ($cat) => [
                                        $cat->value => $cat->label()
                                    ]))
                                    ->required()
                                    ->native(false)
                                    ->default('reviews'),

                                Forms\Components\TextInput::make('name')
                                    ->label('الاسم (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Monthly Rankings'),

                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم (عربي)')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('المنافسة الشهرية'),

                                Forms\Components\Textarea::make('description')
                                    ->label('الوصف (English)')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Compare your branches with competitors monthly'),

                                Forms\Components\Textarea::make('description_ar')
                                    ->label('الوصف (عربي)')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('قارن فروعك بالمنافسين شهرياً'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشطة')
                                    ->default(true)
                                    ->helperText('الميزات غير النشطة لن تظهر في الباقات'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('الترتيب')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('الأرقام الأصغر تظهر أولاً'),
                            ]),
                    ]),

                Forms\Components\Section::make('الباقات المرتبطة')
                    ->schema([
                        Forms\Components\Placeholder::make('plans_info')
                            ->content(function (?Feature $record) {
                                if (!$record) {
                                    return 'احفظ الميزة أولاً لربطها بالباقات';
                                }

                                $plans = $record->plans()->wherePivot('is_enabled', true)->get();

                                if ($plans->isEmpty()) {
                                    return 'هذه الميزة غير مرتبطة بأي باقة';
                                }

                                return view('filament.super-admin.components.feature-plans', [
                                    'plans' => $plans
                                ]);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($operation) => $operation === 'create'),
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

                Tables\Columns\TextColumn::make('key')
                    ->label('المفتاح')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الميزة')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name),

                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match($state) {
                        FeatureCategory::REVIEWS => 'primary',
                        FeatureCategory::ANALYTICS => 'info',
                        FeatureCategory::AI => 'warning',
                        FeatureCategory::EXPORT => 'success',
                        FeatureCategory::INTEGRATION => 'gray',
                        FeatureCategory::SUPPORT => 'danger',
                    })
                    ->icon(fn ($state) => $state->icon()),

                Tables\Columns\TextColumn::make('plans_count')
                    ->label('الباقات')
                    ->state(fn ($record) => $record->planFeatures()->where('is_enabled', true)->count())
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options(collect(FeatureCategory::cases())->mapWithKeys(fn ($cat) => [
                        $cat->value => $cat->label()
                    ])),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشطة')
                    ->falseLabel('غير نشطة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, Tables\Actions\DeleteAction $action) {
                        if ($record->planFeatures()->exists()) {
                            $action->cancel();
                            $action->failureNotificationTitle('لا يمكن حذف الميزة - مرتبطة بباقات');
                        }
                    }),
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

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label('التصنيف')
                    ->getTitleFromRecordUsing(fn ($record) => $record->category->label()),
            ])
            ->defaultGroup('category')
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
