<?php

namespace App\Models;

use App\Enums\SubscriptionAction;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    public $timestamps = false;

    protected $table = 'subscription_history';

    protected $fillable = [
        'subscription_id',
        'action',
        'old_plan_id',
        'new_plan_id',
        'old_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'action' => SubscriptionAction::class,
        'old_status' => SubscriptionStatus::class,
        'new_status' => SubscriptionStatus::class,
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'old_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'new_plan_id');
    }

    /**
     * Get the entity that made the change.
     */
    public function getChangedByAttribute(): ?Model
    {
        if (! $this->changed_by_type || ! $this->changed_by_id) {
            return null;
        }

        return match ($this->changed_by_type) {
            'super_admin' => SuperAdmin::find($this->changed_by_id),
            'tenant' => User::find($this->changed_by_id),
            default => null,
        };
    }

    /**
     * Get the name of who made the change.
     */
    public function getChangedByNameAttribute(): string
    {
        if (! $this->changed_by_type) {
            return 'النظام';
        }

        $entity = $this->changed_by;

        return $entity?->name ?? match ($this->changed_by_type) {
            'super_admin' => 'مشرف النظام',
            'tenant' => 'صاحب الحساب',
            'system' => 'النظام',
            default => 'غير معروف',
        };
    }

    /**
     * Get icon for the action.
     */
    public function getIconAttribute(): string
    {
        return $this->action->icon();
    }

    /**
     * Get color for the action.
     */
    public function getColorAttribute(): string
    {
        return $this->action->color();
    }
}
