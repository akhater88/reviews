<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Section::make('معلومات الفرع')
                    ->description('أدخل المعلومات الأساسية للفرع')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الفرع (إنجليزي)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم الفرع (عربي)')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('google_place_id')
                            ->label('معرف Google Place')
                            ->helperText('سيتم ملؤه تلقائياً عند الربط مع Google')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('الموقع')
                    ->description('معلومات الموقع الجغرافي')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('city')
                            ->label('المدينة')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('country')
                            ->label('الدولة')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('latitude')
                            ->label('خط العرض')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\TextInput::make('longitude')
                            ->label('خط الطول')
                            ->numeric()
                            ->step(0.00000001),
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

                Tables\Columns\TextColumn::make('last_sync_at')
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
                Tables\Actions\Action::make('view_report')
                    ->label('عرض التقرير')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn (Branch $record): string => route('filament.admin.pages.branch-report', ['branch' => $record->id])),
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
