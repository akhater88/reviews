<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReviewsWidget extends BaseWidget
{
    protected static ?string $heading = 'أحدث المراجعات';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Review::query()
                    ->with('branch')
                    ->latest('review_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label('المراجع')
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->color(fn (int $state): string => match(true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('text')
                    ->label('المراجعة')
                    ->limit(50)
                    ->tooltip(fn (Review $record): string => $record->text ?? ''),

                Tables\Columns\TextColumn::make('sentiment')
                    ->label('المشاعر')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'positive' => 'إيجابي',
                        'neutral' => 'محايد',
                        'negative' => 'سلبي',
                        default => 'غير محدد',
                    })
                    ->color(fn (?string $state): string => match($state) {
                        'positive' => 'success',
                        'neutral' => 'warning',
                        'negative' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('is_replied')
                    ->label('تم الرد')
                    ->boolean(),

                Tables\Columns\TextColumn::make('review_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            // Removed actions for now - will add when ReviewResource is created
            ->emptyStateHeading('لا توجد مراجعات')
            ->emptyStateDescription('ستظهر المراجعات هنا بعد المزامنة مع Google')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
