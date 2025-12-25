<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'google_account_id',
        'google_email',
        'google_location_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'replies_this_month',
        'last_synced_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the branch this connection belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * Check if connection is active.
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected' && !$this->isTokenExpired();
    }

    /**
     * Get status display in Arabic.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'connected' => 'متصل',
            'expired' => 'انتهت الصلاحية',
            'disconnected' => 'غير متصل',
            default => $this->status,
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'connected' => 'success',
            'expired' => 'warning',
            'disconnected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Mark connection as expired.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark connection as disconnected.
     */
    public function disconnect(): void
    {
        $this->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ]);
    }

    /**
     * Increment monthly reply counter.
     */
    public function incrementReplyCount(): void
    {
        $this->increment('replies_this_month');
    }

    /**
     * Reset monthly reply counter (call on month start).
     */
    public function resetMonthlyCount(): void
    {
        $this->update(['replies_this_month' => 0]);
    }
}
