<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Traits\HasSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;
    use HasSubscription;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'logo',
        'email',
        'phone',
        'subscription_plan',
        'subscription_expires_at',
        'is_active',
        'settings',
        'current_subscription_id',
        'trial_ends_at',
        'billing_email',
        'billing_address',
        'tax_number',
        'country_code',
        'timezone',
        'preferred_currency',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get all users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all branches belonging to this tenant.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get all reviews through branches.
     */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Branch::class);
    }

    /**
     * Get the admin user for this tenant.
     */
    public function getAdminUser(): ?User
    {
        return $this->users()->where('role', 'admin')->first();
    }

    /**
     * Get the current active subscription.
     */
    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'current_subscription_id');
    }

    /**
     * Get all subscriptions for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->active()->latest();
    }

    /**
     * Get all invoices for this tenant.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all payments for this tenant.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get usage records for this tenant.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Get usage summaries for this tenant.
     */
    public function usageSummaries(): HasMany
    {
        return $this->hasMany(UsageSummary::class);
    }

    /**
     * Get tenant overrides.
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(TenantOverride::class);
    }

    /**
     * Get valid (non-expired) overrides.
     */
    public function validOverrides(): HasMany
    {
        return $this->overrides()->valid();
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        // Check new subscription system first
        if ($this->currentSubscription) {
            return $this->currentSubscription->isActive();
        }

        // Fallback to legacy fields
        if ($this->subscription_plan === 'trial') {
            return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    /**
     * Check if tenant is on trial.
     */
    public function isOnTrial(): bool
    {
        if ($this->currentSubscription) {
            return $this->currentSubscription->isOnTrial();
        }

        return $this->subscription_plan === 'trial' && $this->hasActiveSubscription();
    }

    /**
     * Get the current plan.
     */
    public function getCurrentPlan(): ?Plan
    {
        return $this->currentSubscription?->plan;
    }

    /**
     * Get subscription status.
     */
    public function getSubscriptionStatus(): ?SubscriptionStatus
    {
        return $this->currentSubscription?->status;
    }

    /**
     * Check if tenant has a specific feature.
     */
    public function hasFeature(string $featureKey): bool
    {
        // Check overrides first
        $override = $this->validOverrides()
            ->features()
            ->forKey($featureKey)
            ->first();

        if ($override) {
            return $override->getBooleanValue();
        }

        // Check plan features
        $plan = $this->getCurrentPlan();

        return $plan?->hasFeature($featureKey) ?? false;
    }

    /**
     * Get a limit value for this tenant.
     */
    public function getLimit(string $limitKey): int
    {
        // Check overrides first
        $override = $this->validOverrides()
            ->limits()
            ->forKey($limitKey)
            ->first();

        if ($override) {
            return $override->getIntegerValue();
        }

        // Get from plan limits
        $plan = $this->getCurrentPlan();

        return $plan?->getLimit($limitKey) ?? 0;
    }

    /**
     * Check if limit is unlimited.
     */
    public function isUnlimited(string $limitKey): bool
    {
        return $this->getLimit($limitKey) === -1;
    }

    /**
     * Get current usage summary.
     */
    public function getCurrentUsageSummary(): UsageSummary
    {
        return UsageSummary::getCurrentForTenant($this->id);
    }

    /**
     * Check if within usage limit.
     */
    public function isWithinLimit(string $usageField, string $limitKey): bool
    {
        $limit = $this->getLimit($limitKey);

        if ($limit === -1) {
            return true;
        }

        $summary = $this->getCurrentUsageSummary();

        return $summary->isWithinLimit($usageField, $limit);
    }

    /**
     * Get the billing email (fallback to main email).
     */
    public function getBillingEmailAttribute($value): string
    {
        return $value ?? $this->email;
    }

    /**
     * Get the preferred currency (with fallback).
     */
    public function getPreferredCurrencyAttribute($value): string
    {
        return $value ?? config('subscription.default_currency', 'SAR');
    }

    /**
     * Get the display name (Arabic if available, otherwise English).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?? $this->name;
    }
}
