<?php

namespace App\Filament\TenantAdmin\Widgets\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetitionWinner;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyWinnersWidget extends BaseWidget
{
    protected static ?string $heading = 'جوائزي 🏆';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $tenantId = auth()->user()?->tenant_id;

        return $table
            ->query(InternalCompetitionWinner::query()
                ->where('tenant_id', $tenantId)
                ->with(['competition', 'branch', 'prize'])
                ->orderByDesc('announced_at'))
            ->columns([
                Tables\Columns\TextColumn::make('competition.name_ar')
                    ->label('المسابقة')
                    ->limit(20),
                Tables\Columns\TextColumn::make('final_rank')
                    ->label('المركز')
                    ->formatStateUsing(fn ($state) => match ($state) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => "#{$state}" }),
                Tables\Columns\TextColumn::make('prize.name_ar')
                    ->label('الجائزة')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('prize_status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->emptyStateHeading('لم تفز بعد')
            ->emptyStateDescription('استمر في تحسين أدائك!')
            ->paginated(false);
    }
}
