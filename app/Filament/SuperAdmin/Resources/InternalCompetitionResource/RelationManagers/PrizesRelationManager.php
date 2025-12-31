<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\RelationManagers;

use App\Enums\InternalCompetition\CompetitionMetric;
use App\Enums\InternalCompetition\PrizeType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PrizesRelationManager extends RelationManager
{
    protected static string $relationship = 'prizes';

    protected static ?string $title = 'الجوائز';

    protected static ?string $modelLabel = 'جائزة';

    protected static ?string $pluralModelLabel = 'الجوائز';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('metric_type')
                            ->label('المعيار')
                            ->options(function () {
                                $enabledMetrics = $this->ownerRecord->enabled_metrics;
                                return collect($enabledMetrics)
                                    ->mapWithKeys(fn ($metric) => [$metric->value => $metric->getLabel()])
                                    ->toArray();
                            })
                            ->required(),

                        Forms\Components\Select::make('rank')
                            ->label('المركز')
                            ->options([
                                1 => '🥇 المركز الأول',
                                2 => '🥈 المركز الثاني',
                                3 => '🥉 المركز الثالث',
                            ])
                            ->required(),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الجائزة (بالإنجليزية)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('iPhone 15 Pro'),

                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم الجائزة (بالعربية)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('آيفون 15 برو'),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->label('وصف الجائزة')
                    ->rows(2)
                    ->maxLength(500),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('prize_type')
                            ->label('نوع الجائزة')
                            ->options(PrizeType::class)
                            ->required()
                            ->default(PrizeType::PHYSICAL),

                        Forms\Components\TextInput::make('estimated_value')
                            ->label('القيمة التقديرية')
                            ->numeric()
                            ->suffix('ر.س')
                            ->placeholder('5000'),
                    ]),

                Forms\Components\FileUpload::make('image_path')
                    ->label('صورة الجائزة')
                    ->image()
                    ->directory('competition-prizes')
                    ->maxSize(2048)
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('400'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=🎁&background=f3f4f6'),

                Tables\Columns\TextColumn::make('metric_type')
                    ->label('المعيار')
                    ->badge(),

                Tables\Columns\TextColumn::make('rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => '🥇 الأول',
                        2 => '🥈 الثاني',
                        3 => '🥉 الثالث',
                        default => "#{$state}",
                    }),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('الجائزة')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('prize_type')
                    ->label('النوع')
                    ->badge(),

                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('القيمة')
                    ->money('SAR')
                    ->placeholder('-'),
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => $this->ownerRecord->status->canEdit()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->ownerRecord->status->canEdit()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $this->ownerRecord->status->canEdit()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $this->ownerRecord->status->canEdit()),
                ]),
            ]);
    }
}
