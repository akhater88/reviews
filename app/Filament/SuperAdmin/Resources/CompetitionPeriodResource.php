<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\CompetitionPeriodStatus;
use App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\Pages;
use App\Filament\SuperAdmin\Resources\CompetitionPeriodResource\RelationManagers;
use App\Jobs\Competition\SelectWinnersJob;
use App\Jobs\Competition\UpdateRankingsJob;
use App\Models\Competition\CompetitionPeriod;
use App\Services\Competition\WinnerSelectionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetitionPeriodResource extends Resource
{
    protected static ?string $model = CompetitionPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'المسابقة';

    protected static ?string $navigationLabel = 'فترات المسابقة';

    protected static ?string $modelLabel = 'فترة المسابقة';

    protected static ?string $pluralModelLabel = 'فترات المسابقة';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الفترة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم (إنجليزي)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_ar')
                            ->label('الاسم (عربي)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ البداية')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('تاريخ النهاية')
                            ->required()
                            ->native(false)
                            ->after('starts_at'),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(CompetitionPeriodStatus::options())
                            ->required()
                            ->default(CompetitionPeriodStatus::DRAFT->value),
                    ])->columns(2),

                Forms\Components\Section::make('إعدادات الجوائز')
                    ->schema([
                        Forms\Components\TextInput::make('first_prize')
                            ->label('جائزة المركز الأول (ر.س)')
                            ->numeric()
                            ->default(2000),

                        Forms\Components\TextInput::make('second_prize')
                            ->label('جائزة المركز الثاني (ر.س)')
                            ->numeric()
                            ->default(1500),

                        Forms\Components\TextInput::make('third_prize')
                            ->label('جائزة المركز الثالث (ر.س)')
                            ->numeric()
                            ->default(1000),

                        Forms\Components\TextInput::make('nominator_winners_count')
                            ->label('عدد الفائزين بالسحب')
                            ->numeric()
                            ->default(5)
                            ->helperText('عدد المرشحين الذين سيفوزون بالسحب العشوائي'),

                        Forms\Components\TextInput::make('nominator_prize')
                            ->label('جائزة كل فائز بالسحب (ر.س)')
                            ->numeric()
                            ->default(500),

                        Forms\Components\KeyValue::make('prizes')
                            ->label('جوائز إضافية (اختياري)')
                            ->keyLabel('المركز')
                            ->valueLabel('المبلغ (ر.س)')
                            ->addActionLabel('إضافة جائزة')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('أوزان النقاط')
                    ->schema([
                        Forms\Components\KeyValue::make('score_weights')
                            ->label('أوزان النقاط (%)')
                            ->keyLabel('المكون')
                            ->valueLabel('الوزن')
                            ->addActionLabel('إضافة وزن')
                            ->default([
                                'rating' => '25',
                                'sentiment' => '30',
                                'response_rate' => '15',
                                'volume' => '10',
                                'trend' => '10',
                                'keywords' => '10',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الفترة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('البداية')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('النهاية')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (CompetitionPeriodStatus $state): string => $state->label())
                    ->color(fn (CompetitionPeriodStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('total_participants')
                    ->label('المشاركون')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_branches')
                    ->label('المطاعم')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_nominations')
                    ->label('الترشيحات')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('winners_selected')
                    ->label('الفائزين')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('winners_announced')
                    ->label('معلن')
                    ->boolean()
                    ->trueIcon('heroicon-o-megaphone')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CompetitionPeriodStatus::options()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (CompetitionPeriod $record) => $record->status === CompetitionPeriodStatus::DRAFT)
                        ->action(fn (CompetitionPeriod $record) => $record->update(['status' => CompetitionPeriodStatus::ACTIVE])),

                    Tables\Actions\Action::make('complete')
                        ->label('إنهاء')
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (CompetitionPeriod $record) => $record->status === CompetitionPeriodStatus::ACTIVE)
                        ->action(fn (CompetitionPeriod $record) => $record->update(['status' => CompetitionPeriodStatus::COMPLETED])),

                    Tables\Actions\Action::make('recalculate')
                        ->label('إعادة حساب النقاط')
                        ->icon('heroicon-o-calculator')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (CompetitionPeriod $record) {
                            dispatch(new UpdateRankingsJob($record));
                            Notification::make()
                                ->success()
                                ->title('تم جدولة إعادة الحساب')
                                ->send();
                        }),

                    Tables\Actions\Action::make('selectWinners')
                        ->label('اختيار الفائزين')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('اختيار الفائزين')
                        ->modalDescription('سيتم اختيار أفضل 3 مطاعم + سحب عشوائي للمرشحين. سيتم إشعار الفائزين تلقائياً. هل أنت متأكد؟')
                        ->visible(fn (CompetitionPeriod $record) => $record->status === CompetitionPeriodStatus::COMPLETED && !$record->winners_selected)
                        ->action(function (CompetitionPeriod $record) {
                            dispatch(new SelectWinnersJob($record));
                            Notification::make()
                                ->success()
                                ->title('تم جدولة اختيار الفائزين')
                                ->body('سيتم إشعار الفائزين تلقائياً')
                                ->send();
                        }),

                    Tables\Actions\Action::make('announceWinners')
                        ->label('إعلان الفائزين')
                        ->icon('heroicon-o-megaphone')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('إعلان الفائزين')
                        ->modalDescription('سيتم نشر قائمة الفائزين للعموم. هل أنت متأكد؟')
                        ->visible(fn (CompetitionPeriod $record) => $record->winners_selected && !$record->winners_announced)
                        ->action(function (CompetitionPeriod $record) {
                            app(WinnerSelectionService::class)->announceWinners($record);
                            Notification::make()
                                ->success()
                                ->title('تم إعلان الفائزين')
                                ->send();
                        }),

                    Tables\Actions\Action::make('viewWinners')
                        ->label('عرض الفائزين')
                        ->icon('heroicon-o-eye')
                        ->url(fn (CompetitionPeriod $record) => route('competition.winners.period', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (CompetitionPeriod $record) => $record->winners_announced),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NominationsRelationManager::class,
            RelationManagers\ScoresRelationManager::class,
            RelationManagers\WinnersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompetitionPeriods::route('/'),
            'create' => Pages\CreateCompetitionPeriod::route('/create'),
            'view' => Pages\ViewCompetitionPeriod::route('/{record}'),
            'edit' => Pages\EditCompetitionPeriod::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', CompetitionPeriodStatus::ACTIVE)->count() ?: null;
    }
}
