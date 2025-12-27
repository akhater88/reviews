<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionAction;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_cycle',
        'currency',
        'amount_paid',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'grace_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'gateway_subscription_id',
        'gateway_customer_id',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'billing_cycle' => BillingCycle::class,
        'amount_paid' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class)->orderByDesc('created_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Status Checks
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canAccessFeatures(): bool
    {
        return $this->status->canAccessFeatures();
    }

    public function isOnTrial(): bool
    {
        return $this->status === SubscriptionStatus::TRIAL;
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === SubscriptionStatus::GRACE_PERIOD;
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELLED;
    }

    public function isPastDue(): bool
    {
        return $this->status === SubscriptionStatus::PAST_DUE;
    }

    public function isSuspended(): bool
    {
        return $this->status === SubscriptionStatus::SUSPENDED;
    }

    // Date Checks
    public function daysUntilExpiry(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function daysUntilTrialEnds(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->daysUntilExpiry() <= $days && $this->daysUntilExpiry() > 0;
    }

    public function hasTrialEnded(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    // Formatted Helpers
    public function getFormattedAmount(): string
    {
        $symbol = $this->currency === 'SAR' ? 'ر.س' : '$';

        return $symbol.' '.number_format($this->amount_paid, 2);
    }

    // History
    public function recordHistory(
        SubscriptionAction $action,
        ?int $oldPlanId = null,
        ?int $newPlanId = null,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $changedByType = null,
        ?int $changedById = null,
        ?string $reason = null,
        ?array $metadata = null
    ): SubscriptionHistory {
        return $this->history()->create([
            'action' => $action->value,
            'old_plan_id' => $oldPlanId,
            'new_plan_id' => $newPlanId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SubscriptionStatus::TRIAL,
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::GRACE_PERIOD,
        ]);
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    public function scopeOnTrial($query)
    {
        return $query->where('status', SubscriptionStatus::TRIAL);
    }

    public function scopeTrialEndingSoon($query, int $days = 3)
    {
        return $query->onTrial()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now()->addDays($days))
            ->where('trial_ends_at', '>', now());
    }

    public function scopeNeedsRenewal($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('auto_renew', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(1));
    }
}
