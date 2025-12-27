<?php

namespace App\Filament\Admin\Pages;

use App\Models\Feature;
use App\Models\Plan;
use Filament\Pages\Page;

class Subscription extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'الاشتراك';

    protected static ?string $title = 'إدارة الاشتراك';

    protected static ?string $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.admin.pages.subscription';

    public function getTenant()
    {
        return auth()->user()->tenant;
    }

    public function getCurrentSubscription()
    {
        return $this->getTenant()->currentSubscription;
    }

    public function getCurrentPlan()
    {
        return $this->getCurrentSubscription()?->plan;
    }

    public function getAvailablePlans()
    {
        return Plan::active()
            ->where('is_custom', false)
            ->orderBy('sort_order')
            ->get();
    }

    public function getUsageSummary()
    {
        return $this->getTenant()->getUsageSummary();
    }

    public function getAvailableFeatures()
    {
        return $this->getTenant()->getAvailableFeatures();
    }

    public function getFeatureByKey(string $key): ?Feature
    {
        return Feature::where('key', $key)->first();
    }
}
