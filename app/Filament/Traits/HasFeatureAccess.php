<?php

namespace App\Filament\Traits;

use App\Services\FeatureGateService;
use Filament\Notifications\Notification;

trait HasFeatureAccess
{
    /**
     * Get the current tenant
     */
    protected function getTenant()
    {
        return auth()->user()?->tenant;
    }

    /**
     * Check if current tenant has feature access
     */
    protected function hasFeature(string $featureKey): bool
    {
        $tenant = $this->getTenant();

        if (! $tenant) {
            return false;
        }

        return app(FeatureGateService::class)->canAccess($tenant, $featureKey);
    }

    /**
     * Check if current tenant has any of the features
     */
    protected function hasAnyFeature(array $featureKeys): bool
    {
        $tenant = $this->getTenant();

        if (! $tenant) {
            return false;
        }

        return app(FeatureGateService::class)->canAccessAny($tenant, $featureKeys);
    }

    /**
     * Check if current tenant has all of the features
     */
    protected function hasAllFeatures(array $featureKeys): bool
    {
        $tenant = $this->getTenant();

        if (! $tenant) {
            return false;
        }

        return app(FeatureGateService::class)->canAccessAll($tenant, $featureKeys);
    }

    /**
     * Get limit value
     */
    protected function getLimit(string $limitKey): int
    {
        $tenant = $this->getTenant();

        if (! $tenant) {
            return 0;
        }

        return app(FeatureGateService::class)->getLimit($tenant, $limitKey);
    }

    /**
     * Check if within limit
     */
    protected function isWithinLimit(string $limitKey, int $currentUsage): bool
    {
        $limit = $this->getLimit($limitKey);

        if ($limit === -1) {
            return true;
        }

        return $currentUsage < $limit;
    }

    /**
     * Show upgrade notification
     */
    protected function showUpgradeNotification(?string $feature = null): void
    {
        $message = $feature
            ? "ميزة {$feature} غير متاحة في باقتك الحالية"
            : 'هذه الميزة غير متاحة في باقتك الحالية';

        Notification::make()
            ->title('الترقية مطلوبة')
            ->body($message)
            ->warning()
            ->actions([
                \Filament\Notifications\Actions\Action::make('upgrade')
                    ->label('ترقية الباقة')
                    ->url(route('filament.admin.pages.subscription')),
            ])
            ->persistent()
            ->send();
    }

    /**
     * Show limit exceeded notification
     */
    protected function showLimitExceededNotification(string $limitLabel): void
    {
        Notification::make()
            ->title('تم الوصول للحد الأقصى')
            ->body("لقد وصلت للحد الأقصى من {$limitLabel} لهذا الشهر")
            ->danger()
            ->actions([
                \Filament\Notifications\Actions\Action::make('upgrade')
                    ->label('ترقية الباقة')
                    ->url(route('filament.admin.pages.subscription')),
            ])
            ->persistent()
            ->send();
    }

    /**
     * Ensure feature access or show notification
     */
    protected function ensureFeatureAccess(string $featureKey): bool
    {
        if (! $this->hasFeature($featureKey)) {
            $this->showUpgradeNotification($featureKey);

            return false;
        }

        return true;
    }
}
