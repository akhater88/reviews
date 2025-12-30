<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\RelationManagers;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\CompetitionStatus;
use App\Enums\InternalCompetition\PrizeStatus;
use App\Services\InternalCompetition\WinnerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WinnersRelationManager extends RelationManager
{
    protected static string $relationship = 'winners';

    protected static ?string $title = 'الفائزين';

    protected static ?string $modelLabel = 'فائز';

    protected static ?string $pluralModelLabel = 'الفائزين';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return in_array($ownerRecord->status, [
            CompetitionStatus::ENDED,
            CompetitionStatus::PUBLISHED,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('metric_type')
                    ->label('المعيار')
                    ->badge(),

                Tables\Columns\TextColumn::make('final_rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => '🥇 الأول',
                        2 => '🥈 الثاني',
                        3 => '🥉 الثالث',
                        default => "#{$state}",
                    }),

                Tables\Columns\TextColumn::make('winner_display_name')
                    ->label('الفائز')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع'),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('final_score')
                    ->label('النقاط')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('prize.display_name')
                    ->label('الجائزة')
                    ->placeholder('لا توجد جائزة'),

                Tables\Columns\TextColumn::make('prize_status')
                    ->label('حالة الجائزة')
                    ->badge(),

                Tables\Columns\TextColumn::make('announced_at')
                    ->label('تاريخ الإعلان')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('metric_type')
            ->groups([
                Tables\Grouping\Group::make('metric_type')
                    ->label('المعيار')
                    ->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metric_type')
                    ->label('المعيار')
                    ->options(CompetitionMetric::class),

                Tables\Filters\SelectFilter::make('prize_status')
                    ->label('حالة الجائزة')
                    ->options(PrizeStatus::class),
            ])
            ->actions([
                Tables\Actions\Action::make('update_prize_status')
                    ->label('تحديث حالة الجائزة')
                    ->icon('heroicon-o-truck')
                    ->form([
                        Forms\Components\Select::make('prize_status')
                            ->label('الحالة الجديدة')
                            ->options(PrizeStatus::class)
                            ->required(),

                        Forms\Components\TextInput::make('recipient_name')
                            ->label('اسم المستلم')
                            ->visible(fn (Forms\Get $get) => in_array($get('prize_status'), [
                                PrizeStatus::CLAIMED->value,
                                PrizeStatus::PROCESSING->value,
                                PrizeStatus::DELIVERED->value,
                            ])),

                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('رقم الهاتف')
                            ->tel(),

                        Forms\Components\Textarea::make('recipient_address')
                            ->label('العنوان')
                            ->rows(2),

                        Forms\Components\Textarea::make('delivery_notes')
                            ->label('ملاحظات التسليم')
                            ->rows(2)
                            ->visible(fn (Forms\Get $get) => $get('prize_status') === PrizeStatus::DELIVERED->value),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $service = app(WinnerService::class);
                            $status = PrizeStatus::from($data['prize_status']);

                            // Update recipient info
                            $service->updateRecipientInfo($record, [
                                'name' => $data['recipient_name'] ?? null,
                                'phone' => $data['recipient_phone'] ?? null,
                                'address' => $data['recipient_address'] ?? null,
                            ]);

                            // Update status based on target
                            match ($status) {
                                PrizeStatus::CLAIMED => $service->claimPrize($record, $data),
                                PrizeStatus::PROCESSING => $service->startProcessing($record),
                                PrizeStatus::DELIVERED => $service->markAsDelivered(
                                    $record,
                                    null,
                                    $data['delivery_notes'] ?? null
                                ),
                                default => $record->update(['prize_status' => $status]),
                            };

                            Notification::make()
                                ->title('تم تحديث حالة الجائزة')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('خطأ')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->prize_id && $record->prize_status !== PrizeStatus::DELIVERED),
            ])
            ->bulkActions([]);
    }
}
