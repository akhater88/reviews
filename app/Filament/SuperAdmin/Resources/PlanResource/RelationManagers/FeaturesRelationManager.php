<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\RelationManagers;

use App\Enums\FeatureCategory;
use App\Models\Feature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'planFeatures';
    protected static ?string $title = 'الميزات';
    protected static ?string $modelLabel = 'ميزة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('feature_id')
                    ->label('الميزة')
                    ->options(Feature::active()->pluck('name_ar', 'id'))
                    ->required()
                    ->searchable()
                    ->native(false),

                Forms\Components\Toggle::make('is_enabled')
                    ->label('مفعّلة')
                    ->default(true),

                Forms\Components\TextInput::make('limit_value')
                    ->label('حد مخصص')
                    ->numeric()
                    ->placeholder('اختياري'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('feature.name_ar')
                    ->label('الميزة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('feature.category')
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
                    }),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('مفعّلة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('limit_value')
                    ->label('الحد')
                    ->default('-')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options(collect(FeatureCategory::cases())->mapWithKeys(fn ($cat) => [
                        $cat->value => $cat->label()
                    ]))
                    ->query(fn ($query, $data) => $query->when(
                        $data['value'],
                        fn ($query) => $query->whereHas('feature', fn ($q) => $q->where('category', $data['value']))
                    )),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة ميزة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('enable')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn ($records) => $records->each->update(['is_enabled' => true])),

                Tables\Actions\BulkAction::make('disable')
                    ->label('تعطيل')
                    ->icon('heroicon-o-x-circle')
                    ->action(fn ($records) => $records->each->update(['is_enabled' => false])),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
