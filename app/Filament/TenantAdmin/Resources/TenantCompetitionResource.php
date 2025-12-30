<?php

namespace App\Filament\TenantAdmin\Resources;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Filament\TenantAdmin\Resources\TenantCompetitionResource\Pages;
use App\Models\InternalCompetition\InternalCompetition;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantCompetitionResource extends Resource
{
    protected static ?string $model = InternalCompetition::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'المسابقات';

    protected static ?string $modelLabel = 'مسابقة';

    protected static ?string $pluralModelLabel = 'المسابقات';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $tenantId = filament()->getTenant()?->id;
        if (!$tenantId) {
            return null;
        }

        return static::getModel()::active()
            ->forTenant($tenantId)
            ->count() ?: null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('المسابقة')
                    ->searchable(['name', 'name_ar'])
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('البدء')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('الانتهاء')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('المتبقي')
                    ->suffix(' يوم')
                    ->color(fn ($state) => $state <= 3 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('التقدم')
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('my_branches_count')
                    ->label('فروعي المشاركة')
                    ->state(function (InternalCompetition $record) {
                        $tenantId = filament()->getTenant()?->id;
                        return $record->activeBranches()
                            ->where('tenant_id', $tenantId)
                            ->count();
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CompetitionStatus::class),

                Tables\Filters\Filter::make('active')
                    ->label('النشطة فقط')
                    ->query(fn (Builder $query) => $query->active())
                    ->toggle()
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->emptyStateHeading('لا توجد مسابقات')
            ->emptyStateDescription('لم يتم تسجيلك في أي مسابقات بعد')
            ->emptyStateIcon('heroicon-o-trophy');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المسابقة')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('display_name')
                                    ->label('اسم المسابقة'),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge(),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->date(),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->date(),

                                Infolists\Components\TextEntry::make('remaining_days')
                                    ->label('الأيام المتبقية')
                                    ->suffix(' يوم'),
                            ]),
                    ]),

                Infolists\Components\Section::make('المعايير')
                    ->schema([
                        Infolists\Components\TextEntry::make('enabled_metrics')
                            ->label('معايير التقييم')
                            ->state(function (InternalCompetition $record) {
                                return collect($record->enabled_metrics)
                                    ->map(fn ($metric) => $metric->getLabel())
                                    ->join('، ');
                            }),
                    ]),

                Infolists\Components\Section::make('الجوائز')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('prizes')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('rank_label')
                                    ->label('المركز'),
                                Infolists\Components\TextEntry::make('metric_type')
                                    ->label('المعيار')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('display_name')
                                    ->label('الجائزة'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenantCompetitions::route('/'),
            'view' => Pages\ViewTenantCompetition::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenantId = filament()->getTenant()?->id;

        return parent::getEloquentQuery()
            ->forTenant($tenantId)
            ->whereIn('status', [
                CompetitionStatus::ACTIVE,
                CompetitionStatus::ENDED,
                CompetitionStatus::PUBLISHED,
            ]);
    }
}
