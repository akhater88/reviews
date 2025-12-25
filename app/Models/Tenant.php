<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
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
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === 'trial') {
            return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    /**
     * Get the display name (Arabic if available, otherwise English).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?? $this->name;
    }
}
