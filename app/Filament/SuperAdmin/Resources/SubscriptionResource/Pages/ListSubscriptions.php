<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;

use App\Enums\SubscriptionStatus;
use App\Filament\SuperAdmin\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء اشتراك')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn () => Subscription::count()),

            'active' => Tab::make('نشط')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::ACTIVE))
                ->badge(fn () => Subscription::where('status', SubscriptionStatus::ACTIVE)->count())
                ->badgeColor('success'),

            'trial' => Tab::make('تجريبي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::TRIAL))
                ->badge(fn () => Subscription::where('status', SubscriptionStatus::TRIAL)->count())
                ->badgeColor('info'),

            'expiring' => Tab::make('ينتهي قريباً')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringSoon())
                ->badge(fn () => Subscription::expiringSoon()->count())
                ->badgeColor('warning'),

            'expired' => Tab::make('منتهي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::EXPIRED))
                ->badge(fn () => Subscription::where('status', SubscriptionStatus::EXPIRED)->count())
                ->badgeColor('danger'),

            'cancelled' => Tab::make('ملغي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::CANCELLED))
                ->badge(fn () => Subscription::where('status', SubscriptionStatus::CANCELLED)->count())
                ->badgeColor('gray'),
        ];
    }
}
