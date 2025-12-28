<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CompetitionBranchResource\Pages;
use App\Jobs\Competition\AnalyzeBranchReviewsJob;
use App\Jobs\Competition\SyncBranchReviewsJob;
use App\Models\Competition\CompetitionBranch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetitionBranchResource extends Resource
{
    protected static ?string $model = CompetitionBranch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'المسابقة';

    protected static ?string $navigationLabel = 'المطاعم المشاركة';

    protected static ?string $modelLabel = 'مطعم';

    protected static ?string $pluralModelLabel = 'المطاعم المشاركة';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المطعم')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_ar')
                            ->label('الاسم (عربي)')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('google_place_id')
                            ->label('Google Place ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('city')
                            ->label('المدينة')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('بيانات Google')
                    ->schema([
                        Forms\Components\TextInput::make('google_rating')
                            ->label('التقييم')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(5),

                        Forms\Components\TextInput::make('google_reviews_count')
                            ->label('عدد المراجعات')
                            ->numeric(),

                        Forms\Components\TextInput::make('latitude')
                            ->label('خط العرض')
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('خط الطول')
                            ->numeric(),
                    ])->columns(4),

                Forms\Components\Section::make('الحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),

                        Forms\Components\Toggle::make('is_eligible')
                            ->label('مؤهل للمسابقة')
                            ->default(true),

                        Forms\Components\Select::make('sync_status')
                            ->label('حالة المزامنة')
                            ->options([
                                'pending' => 'في الانتظار',
                                'syncing' => 'جاري المزامنة',
                                'synced' => 'مكتمل',
                                'failed' => 'فشل',
                            ])
                            ->default('pending'),

                        Forms\Components\Textarea::make('sync_error')
                            ->label('خطأ المزامنة')
                            ->rows(2)
                            ->visible(fn ($get) => $get('sync_status') === 'failed'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->getStateUsing(fn ($record) => $record->photos[0] ?? null),

                Tables\Columns\TextColumn::make('name')
                    ->label('المطعم')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->city),

                Tables\Columns\TextColumn::make('google_rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => $state ? "⭐ {$state}" : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('google_reviews_count')
                    ->label('المراجعات')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_nominations')
                    ->label('الترشيحات')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->nominations()->count()),

                Tables\Columns\TextColumn::make('sync_status')
                    ->label('المزامنة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'syncing' => 'info',
                        'synced' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_eligible')
                    ->label('مؤهل')
                    ->boolean(),

                Tables\Columns\TextColumn::make('reviews_last_synced_at')
                    ->label('آخر مزامنة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->label('المدينة')
                    ->options(fn () => CompetitionBranch::distinct()->pluck('city', 'city')->filter()),

                Tables\Filters\SelectFilter::make('sync_status')
                    ->label('حالة المزامنة')
                    ->options([
                        'pending' => 'في الانتظار',
                        'syncing' => 'جاري المزامنة',
                        'synced' => 'مكتمل',
                        'failed' => 'فشل',
                    ]),

                Tables\Filters\TernaryFilter::make('is_eligible')
                    ->label('مؤهل'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('syncReviews')
                        ->label('مزامنة المراجعات')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (CompetitionBranch $record) {
                            dispatch(new SyncBranchReviewsJob($record));
                            Notification::make()
                                ->success()
                                ->title('تم جدولة المزامنة')
                                ->send();
                        }),

                    Tables\Actions\Action::make('analyzeReviews')
                        ->label('تحليل المراجعات')
                        ->icon('heroicon-o-cpu-chip')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (CompetitionBranch $record) {
                            dispatch(new AnalyzeBranchReviewsJob($record));
                            Notification::make()
                                ->success()
                                ->title('تم جدولة التحليل')
                                ->send();
                        }),

                    Tables\Actions\Action::make('viewOnGoogle')
                        ->label('فتح في Google')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (CompetitionBranch $record) => $record->google_maps_url)
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('syncAll')
                        ->label('مزامنة المحدد')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $delay = 0;
                            foreach ($records as $record) {
                                dispatch(new SyncBranchReviewsJob($record))
                                    ->delay(now()->addSeconds($delay));
                                $delay += 10;
                            }
                            Notification::make()
                                ->success()
                                ->title('تم جدولة المزامنة')
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('markEligible')
                        ->label('تأهيل')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_eligible' => true])),

                    Tables\Actions\BulkAction::make('markIneligible')
                        ->label('إلغاء التأهيل')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_eligible' => false])),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المطعم')
                    ->schema([
                        Infolists\Components\ImageEntry::make('photo_url')
                            ->label('')
                            ->size(120)
                            ->getStateUsing(fn ($record) => $record->photos[0] ?? null),

                        Infolists\Components\TextEntry::make('name')
                            ->label('الاسم'),

                        Infolists\Components\TextEntry::make('address')
                            ->label('العنوان'),

                        Infolists\Components\TextEntry::make('city')
                            ->label('المدينة'),

                        Infolists\Components\TextEntry::make('google_rating')
                            ->label('التقييم')
                            ->formatStateUsing(fn ($state) => "⭐ {$state}/5"),

                        Infolists\Components\TextEntry::make('google_reviews_count')
                            ->label('المراجعات'),
                    ])->columns(3),

                Infolists\Components\Section::make('إحصائيات المسابقة')
                    ->schema([
                        Infolists\Components\TextEntry::make('nominations_count')
                            ->label('إجمالي الترشيحات')
                            ->getStateUsing(fn ($record) => $record->nominations()->count()),

                        Infolists\Components\TextEntry::make('first_nominated_at')
                            ->label('أول ترشيح')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('reviews_last_synced_at')
                            ->label('آخر مزامنة')
                            ->dateTime('d/m/Y H:i'),
                    ])->columns(3),
            ]);
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
            'index' => Pages\ListCompetitionBranches::route('/'),
            'create' => Pages\CreateCompetitionBranch::route('/create'),
            'view' => Pages\ViewCompetitionBranch::route('/{record}'),
            'edit' => Pages\EditCompetitionBranch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
