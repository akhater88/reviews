<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\CompetitionScoreResource\Pages;
use App\Jobs\Competition\CalculateBranchScoreJob;
use App\Models\Competition\CompetitionPeriod;
use App\Models\Competition\CompetitionScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetitionScoreResource extends Resource
{
    protected static ?string $model = CompetitionScore::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'المسابقة';

    protected static ?string $navigationLabel = 'النقاط والترتيب';

    protected static ?string $modelLabel = 'نقاط';

    protected static ?string $pluralModelLabel = 'النقاط والترتيب';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات أساسية')
                    ->schema([
                        Forms\Components\Select::make('competition_period_id')
                            ->label('الفترة')
                            ->relationship('period', 'name_ar')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('competition_branch_id')
                            ->label('المطعم')
                            ->relationship('competitionBranch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('النقاط')
                    ->schema([
                        Forms\Components\TextInput::make('rating_score')
                            ->label('التقييم (25)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(25),

                        Forms\Components\TextInput::make('sentiment_score')
                            ->label('المشاعر (30)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(30),

                        Forms\Components\TextInput::make('response_rate')
                            ->label('معدل الرد (15)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(15),

                        Forms\Components\TextInput::make('review_volume_score')
                            ->label('الحجم (10)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('trend_score')
                            ->label('الاتجاه (10)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('keyword_score')
                            ->label('الكلمات (10)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('competition_score')
                            ->label('المجموع (100)')
                            ->numeric()
                            ->step(0.01)
                            ->maxValue(100)
                            ->disabled(),
                    ])->columns(4),

                Forms\Components\Section::make('الترتيب')
                    ->schema([
                        Forms\Components\TextInput::make('rank_position')
                            ->label('الترتيب')
                            ->numeric(),

                        Forms\Components\TextInput::make('nomination_count')
                            ->label('عدد المرشحين')
                            ->numeric(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rank_position')
                    ->label('#')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state === 1) {
                            return '🥇';
                        }
                        if ($state === 2) {
                            return '🥈';
                        }
                        if ($state === 3) {
                            return '🥉';
                        }

                        return $state;
                    }),

                Tables\Columns\ImageColumn::make('competitionBranch.photo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->getStateUsing(fn ($record) => $record->competitionBranch?->photos[0] ?? null),

                Tables\Columns\TextColumn::make('competitionBranch.name')
                    ->label('المطعم')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->competitionBranch?->city),

                Tables\Columns\TextColumn::make('period.name_ar')
                    ->label('الفترة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('competition_score')
                    ->label('النقاط')
                    ->numeric(2)
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('rating_score')
                    ->label('التقييم')
                    ->numeric(1)
                    ->sortable(),

                Tables\Columns\TextColumn::make('sentiment_score')
                    ->label('المشاعر')
                    ->numeric(1)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomination_count')
                    ->label('المرشحين')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_analyzed_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->defaultSort('rank_position', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('competition_period_id')
                    ->label('الفترة')
                    ->relationship('period', 'name_ar')
                    ->default(fn () => CompetitionPeriod::current()?->id),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('recalculate')
                        ->label('إعادة الحساب')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (CompetitionScore $record) {
                            dispatch(new CalculateBranchScoreJob(
                                $record->competitionBranch,
                                $record->period
                            ));
                            Notification::make()
                                ->success()
                                ->title('تم جدولة إعادة الحساب')
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('recalculateAll')
                        ->label('إعادة حساب المحدد')
                        ->icon('heroicon-o-calculator')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $delay = 0;
                            foreach ($records as $record) {
                                dispatch(new CalculateBranchScoreJob(
                                    $record->competitionBranch,
                                    $record->period
                                ))->delay(now()->addSeconds($delay));
                                $delay += 5;
                            }
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('المطعم')
                    ->schema([
                        Infolists\Components\ImageEntry::make('competitionBranch.photo_url')
                            ->label('')
                            ->size(80)
                            ->getStateUsing(fn ($record) => $record->competitionBranch?->photos[0] ?? null),

                        Infolists\Components\TextEntry::make('competitionBranch.name')
                            ->label('الاسم'),

                        Infolists\Components\TextEntry::make('competitionBranch.city')
                            ->label('المدينة'),

                        Infolists\Components\TextEntry::make('period.name_ar')
                            ->label('الفترة'),
                    ])->columns(4),

                Infolists\Components\Section::make('تفاصيل النقاط')
                    ->schema([
                        Infolists\Components\TextEntry::make('rating_score')
                            ->label('التقييم (25)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/25'),

                        Infolists\Components\TextEntry::make('sentiment_score')
                            ->label('المشاعر (30)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/30'),

                        Infolists\Components\TextEntry::make('response_rate')
                            ->label('معدل الرد (15)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/15'),

                        Infolists\Components\TextEntry::make('review_volume_score')
                            ->label('الحجم (10)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/10'),

                        Infolists\Components\TextEntry::make('trend_score')
                            ->label('الاتجاه (10)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/10'),

                        Infolists\Components\TextEntry::make('keyword_score')
                            ->label('الكلمات (10)')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/10'),
                    ])->columns(3),

                Infolists\Components\Section::make('المجموع')
                    ->schema([
                        Infolists\Components\TextEntry::make('competition_score')
                            ->label('النقاط الإجمالية')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2) . '/100')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('rank_position')
                            ->label('الترتيب')
                            ->formatStateUsing(fn ($state) => "#{$state}")
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                    ])->columns(2),
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
            'index' => Pages\ListCompetitionScores::route('/'),
            'view' => Pages\ViewCompetitionScore::route('/{record}'),
            'edit' => Pages\EditCompetitionScore::route('/{record}/edit'),
        ];
    }
}
