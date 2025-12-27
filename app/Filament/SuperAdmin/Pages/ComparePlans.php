<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Enums\FeatureCategory;
use App\Models\Feature;
use App\Models\Plan;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ComparePlans extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'مقارنة الباقات';
    protected static ?string $title = 'مقارنة الباقات';
    protected static ?string $navigationGroup = 'الاشتراكات';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = true;

    protected static string $view = 'filament.super-admin.pages.compare-plans';

    #[Url]
    public string $currency = 'SAR';

    public function mount(): void
    {
        $this->currency = 'SAR';
    }

    public function getPlans()
    {
        return Plan::with(['limits', 'planFeatures.feature'])
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    public function getFeaturesByCategory()
    {
        return Feature::active()
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($feature) => $feature->category->value);
    }

    public function getCategories(): array
    {
        return collect(FeatureCategory::cases())->mapWithKeys(fn ($cat) => [
            $cat->value => [
                'label' => $cat->label(),
                'icon' => $cat->icon(),
            ]
        ])->toArray();
    }

    public function toggleCurrency(): void
    {
        $this->currency = $this->currency === 'SAR' ? 'USD' : 'SAR';
    }

    public function planHasFeature(Plan $plan, Feature $feature): bool
    {
        return $plan->planFeatures
            ->where('feature_id', $feature->id)
            ->where('is_enabled', true)
            ->isNotEmpty();
    }

    public function getFeatureLimit(Plan $plan, Feature $feature): ?int
    {
        $planFeature = $plan->planFeatures
            ->where('feature_id', $feature->id)
            ->first();

        return $planFeature?->limit_value;
    }
}
