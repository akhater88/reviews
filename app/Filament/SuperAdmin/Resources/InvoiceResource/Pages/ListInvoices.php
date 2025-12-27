<?php

namespace App\Filament\SuperAdmin\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\SuperAdmin\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل'),

            'pending' => Tab::make('معلقة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::PENDING))
                ->badge(fn () => Invoice::where('status', InvoiceStatus::PENDING)->count())
                ->badgeColor('warning'),

            'paid' => Tab::make('مدفوعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvoiceStatus::PAID))
                ->badge(fn () => Invoice::where('status', InvoiceStatus::PAID)->count())
                ->badgeColor('success'),

            'overdue' => Tab::make('متأخرة')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', InvoiceStatus::PENDING)
                    ->where('due_date', '<', now())
                )
                ->badge(fn () => Invoice::where('status', InvoiceStatus::PENDING)->where('due_date', '<', now())->count())
                ->badgeColor('danger'),
        ];
    }
}
