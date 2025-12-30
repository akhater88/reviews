<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\PrizeStatus;
use App\Filament\SuperAdmin\Resources\InternalCompetitionWinnerResource\Pages;
use App\Models\InternalCompetition\InternalCompetitionWinner;
use App\Services\InternalCompetition\WinnerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternalCompetitionWinnerResource extends Resource
{
    protected static ?string $model = InternalCompetitionWinner::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'المسابقات';

    protected static ?string $navigationLabel = 'الفائزين والجوائز';

    protected static ?string $modelLabel = 'فائز';

    protected static ?string $pluralModelLabel = 'الفائزين';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('prize_status', [
            PrizeStatus::CLAIMED,
            PrizeStatus::PROCESSING,
        ])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المستلم')
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('اسم المستلم')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('رقم الهاتف')
                            ->tel(),

                        Forms\Components\Textarea::make('recipient_address')
                            ->label('العنوان')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('معلومات التسليم')
                    ->schema([
                        Forms\Components\Select::make('prize_status')
                            ->label('حالة الجائزة')
                            ->options(PrizeStatus::class)
                            ->required(),

                        Forms\Components\Textarea::make('delivery_notes')
                            ->label('ملاحظات التسليم')
                            ->rows(3),

                        Forms\Components\FileUpload::make('delivery_proof_path')
                            ->label('إثبات التسليم')
                            ->image()
                            ->directory('delivery-proofs'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('competition.display_name')
                    ->label('المسابقة')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('metric_type')
                    ->label('المعيار')
                    ->badge(),

                Tables\Columns\TextColumn::make('final_rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => "#{$state}",
                    }),

                Tables\Columns\TextColumn::make('winner_display_name')
                    ->label('الفائز')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('prize.display_name')
                    ->label('الجائزة')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('prize_status')
                    ->label('حالة الجائزة')
                    ->badge(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('المستلم')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('announced_at')
                    ->label('تاريخ الإعلان')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('announced_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('prize_status')
                    ->label('حالة الجائزة')
                    ->options(PrizeStatus::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('metric_type')
                    ->label('المعيار')
                    ->options(CompetitionMetric::class),

                Tables\Filters\SelectFilter::make('competition_id')
                    ->label('المسابقة')
                    ->relationship('competition', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('pending_delivery')
                    ->label('بانتظار التسليم')
                    ->query(fn ($query) => $query->whereIn('prize_status', [
                        PrizeStatus::CLAIMED,
                        PrizeStatus::PROCESSING,
                    ]))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('claim')
                    ->label('تأكيد الاستلام')
                    ->icon('heroicon-o-hand-raised')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('اسم المستلم')
                            ->required(),
                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('رقم الهاتف')
                            ->tel(),
                        Forms\Components\Textarea::make('recipient_address')
                            ->label('العنوان'),
                    ])
                    ->action(function ($record, array $data) {
                        app(WinnerService::class)->claimPrize($record, $data);
                        Notification::make()->title('تم تأكيد الاستلام')->success()->send();
                    })
                    ->visible(fn ($record) => $record->prize_status === PrizeStatus::ANNOUNCED),

                Tables\Actions\Action::make('process')
                    ->label('بدء التجهيز')
                    ->icon('heroicon-o-cog')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(WinnerService::class)->startProcessing($record);
                        Notification::make()->title('تم بدء التجهيز')->success()->send();
                    })
                    ->visible(fn ($record) => $record->prize_status === PrizeStatus::CLAIMED),

                Tables\Actions\Action::make('deliver')
                    ->label('تأكيد التسليم')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('proof')
                            ->label('صورة إثبات التسليم')
                            ->image()
                            ->directory('delivery-proofs'),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات'),
                    ])
                    ->action(function ($record, array $data) {
                        app(WinnerService::class)->markAsDelivered(
                            $record,
                            $data['proof'] ?? null,
                            $data['notes'] ?? null
                        );
                        Notification::make()->title('تم تأكيد التسليم')->success()->send();
                    })
                    ->visible(fn ($record) => in_array($record->prize_status, [
                        PrizeStatus::CLAIMED,
                        PrizeStatus::PROCESSING,
                    ])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternalCompetitionWinners::route('/'),
            'edit' => Pages\EditInternalCompetitionWinner::route('/{record}/edit'),
        ];
    }
}
