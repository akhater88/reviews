<?php

namespace App\Models;

use App\Enums\FeatureCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    protected $fillable = [
        'key',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'category' => FeatureCategory::class,
        'is_active' => 'boolean',
    ];

    // Relationships
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot('is_enabled', 'limit_value')
            ->withTimestamps();
    }

    // Helpers
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

    public function scopeByCategory($query, FeatureCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
