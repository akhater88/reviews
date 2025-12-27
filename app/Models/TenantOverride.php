<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOverride extends Model
{
    protected $fillable = [
        'tenant_id',
        'override_type',
        'key',
        'value',
        'expires_at',
        'granted_by',
        'reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'granted_by');
    }

    /**
     * Check if override is still valid.
     */
    public function isValid(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Check if this is a feature override.
     */
    public function isFeatureOverride(): bool
    {
        return $this->override_type === 'feature';
    }

    /**
     * Check if this is a limit override.
     */
    public function isLimitOverride(): bool
    {
        return $this->override_type === 'limit';
    }

    /**
     * Get the value as boolean (for features).
     */
    public function getBooleanValue(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get the value as integer (for limits).
     */
    public function getIntegerValue(): int
    {
        return (int) $this->value;
    }

    /**
     * Get display name for the override type.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->override_type) {
            'feature' => 'ميزة',
            'limit' => 'حد',
            default => $this->override_type,
        };
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeFeatures($query)
    {
        return $query->where('override_type', 'feature');
    }

    public function scopeLimits($query)
    {
        return $query->where('override_type', 'limit');
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
