<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('مزامنة المراجعات')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('notify', [
                        'type' => 'info',
                        'message' => 'جاري مزامنة المراجعات...',
                    ]);
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->icon('heroicon-o-chat-bubble-left-right'),

            'positive' => Tab::make('إيجابي')
                ->icon('heroicon-o-face-smile')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sentiment', 'positive'))
                ->badgeColor('success'),

            'neutral' => Tab::make('محايد')
                ->icon('heroicon-o-minus-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sentiment', 'neutral'))
                ->badgeColor('gray'),

            'negative' => Tab::make('سلبي')
                ->icon('heroicon-o-face-frown')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sentiment', 'negative'))
                ->badgeColor('danger'),

            'unreplied' => Tab::make('بدون رد')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                    $q->whereNull('owner_reply')->orWhere('owner_reply', '');
                }))
                ->badgeColor('warning'),
        ];
    }

    #[On('refreshReviews')]
    public function refreshList(): void
    {
        $this->resetTable();
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.resources.reviews.footer');
    }
}
