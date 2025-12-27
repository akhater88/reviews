<?php

namespace App\Filament\Components;

use App\Services\FeatureGateService;
use Closure;
use Filament\Forms\Components\Component;

class FeatureGate extends Component
{
    protected string $view = 'filament.components.feature-gate';

    protected string $featureKey;

    protected ?Closure $fallbackContent = null;

    public static function make(string $featureKey): static
    {
        $static = app(static::class);
        $static->featureKey = $featureKey;

        return $static;
    }

    public function fallback(Closure $callback): static
    {
        $this->fallbackContent = $callback;

        return $this;
    }

    public function hasAccess(): bool
    {
        $tenant = auth()->user()?->tenant;

        if (! $tenant) {
            return false;
        }

        return app(FeatureGateService::class)->canAccess($tenant, $this->featureKey);
    }

    public function getFallbackContent(): mixed
    {
        if ($this->fallbackContent) {
            return ($this->fallbackContent)();
        }

        return null;
    }

    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }
}
