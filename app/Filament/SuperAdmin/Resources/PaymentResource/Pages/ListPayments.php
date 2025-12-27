<?php

namespace App\Filament\SuperAdmin\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\SuperAdmin\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),
            'pending_manual' => Tab::make('بانتظار التأكيد')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('payment_gateway', 'manual')
                    ->where('status', PaymentStatus::PENDING))
                ->badge(fn () => Payment::where('payment_gateway', 'manual')
                    ->where('status', PaymentStatus::PENDING)->count())
                ->badgeColor('warning'),
            'completed' => Tab::make('مكتملة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::COMPLETED))
                ->badgeColor('success'),
            'failed' => Tab::make('فاشلة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::FAILED))
                ->badgeColor('danger'),
        ];
    }
}
