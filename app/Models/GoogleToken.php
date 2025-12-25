<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class GoogleToken extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'google_email',
        'google_account_id',
        'google_account_name',
        'scopes',
        'status',
        'connected_at',
        'last_used_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_used_at' => 'datetime',
        'scopes' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Encrypt access token before saving
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt access token when retrieving
     */
    public function getAccessTokenAttribute($value): ?string
    {
        if (!$value) return null;
        
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt refresh token before saving
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt refresh token when retrieving
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        if (!$value) return null;
        
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * Check if token is active and valid
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Check if token needs refresh (expires within 5 minutes)
     */
    public function needsRefresh(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->subMinutes(5)->isPast();
    }

    /**
     * Get the tenant that owns the token
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Update last used timestamp
     */
    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Mark token as revoked
     */
    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    /**
     * Mark token as expired
     */
    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
