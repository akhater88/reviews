<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /**
     * Only admins can access the users list.
     */
    public static function canAccess(array $parameters = []): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && $user->isAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مستخدم جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserResource\Widgets\UsersStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->icon('heroicon-o-users'),
            'admins' => Tab::make('المدراء الرئيسيين')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin')),
            'managers' => Tab::make('مدراء الفروع')
                ->icon('heroicon-o-building-storefront')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'manager')),
        ];
    }
}
