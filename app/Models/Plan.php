<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'price_monthly_sar',
        'price_yearly_sar',
        'price_monthly_usd',
        'price_yearly_usd',
        'is_active',
        'is_popular',
        'is_free',
        'is_custom',
        'sort_order',
        'color',
        'icon',
    ];

    protected $casts = [
        'price_monthly_sar' => 'decimal:2',
        'price_yearly_sar' => 'decimal:2',
        'price_monthly_usd' => 'decimal:2',
        'price_yearly_usd' => 'decimal:2',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_free' => 'boolean',
        'is_custom' => 'boolean',
    ];

    // Relationships
    public function limits(): HasOne
    {
        return $this->hasOne(PlanLimit::class);
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('is_enabled', 'limit_value')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Helpers
    public function getPrice(string $cycle, string $currency = 'SAR'): float
    {
        $column = 'price_'.strtolower($cycle).'_'.strtolower($currency);

        return (float) ($this->{$column} ?? 0);
    }

    public function getPriceFormatted(string $cycle, string $currency = 'SAR'): string
    {
        $price = $this->getPrice($cycle, $currency);
        $symbol = $currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($price, 2);
    }

    public function hasFeature(string $featureKey): bool
    {
        return $this->planFeatures()
            ->whereHas('feature', fn ($q) => $q->where('key', $featureKey))
            ->where('is_enabled', true)
            ->exists();
    }

    public function getLimit(string $limitKey): int
    {
        return $this->limits?->{$limitKey} ?? 0;
    }

    public function isUnlimited(string $limitKey): bool
    {
        return $this->getLimit($limitKey) === -1;
    }

    public function getDisplayName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $locale === 'ar' ? ($this->name_ar ?: $this->name) : $this->name;
    }

    public function getDisplayDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $locale === 'ar' ? ($this->description_ar ?: $this->description) : $this->description;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->active()->where('is_custom', false)->orderBy('sort_order');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }
}
