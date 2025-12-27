<?php

namespace App\Filament\SuperAdmin\Resources\FeatureResource\Pages;

use App\Enums\FeatureCategory;
use App\Filament\SuperAdmin\Resources\FeatureResource;
use App\Models\Feature;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة ميزة')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('الكل')
                ->badge(fn () => Feature::count()),
        ];

        foreach (FeatureCategory::cases() as $category) {
            $tabs[$category->value] = Tab::make($category->label())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $category))
                ->badge(fn () => Feature::where('category', $category)->count())
                ->icon($category->icon());
        }

        return $tabs;
    }
}
