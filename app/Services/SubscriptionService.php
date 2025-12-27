<?php

namespace App\Services;

use App\Enums\SubscriptionAction;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Change the subscription plan.
     */
    public function changePlan(
        Subscription $subscription,
        Plan $newPlan,
        bool $immediate = true,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $newPlan, $immediate, $reason, $changedByType, $changedById) {
            $oldPlanId = $subscription->plan_id;
            $oldStatus = $subscription->status->value;

            $isUpgrade = $newPlan->price_monthly_sar > ($subscription->plan->price_monthly_sar ?? 0);
            $action = $isUpgrade ? SubscriptionAction::UPGRADED : SubscriptionAction::DOWNGRADED;

            if ($immediate) {
                $subscription->update([
                    'plan_id' => $newPlan->id,
                ]);
            } else {
                // Schedule plan change for end of period
                $subscription->update([
                    'scheduled_plan_id' => $newPlan->id,
                ]);
            }

            // Record history
            $subscription->recordHistory(
                action: $action,
                oldPlanId: $oldPlanId,
                newPlanId: $newPlan->id,
                oldStatus: $oldStatus,
                newStatus: $subscription->status->value,
                changedByType: $changedByType,
                changedById: $changedById,
                reason: $reason,
                metadata: ['immediate' => $immediate]
            );

            return $subscription->fresh();
        });
    }

    /**
     * Extend the subscription expiry.
     */
    public function extend(
        Subscription $subscription,
        int $days,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $days, $reason, $changedByType, $changedById) {
            $oldExpiresAt = $subscription->expires_at;

            $newExpiresAt = $subscription->expires_at
                ? $subscription->expires_at->addDays($days)
                : now()->addDays($days);

            // If subscription was expired, reactivate it
            $newStatus = $subscription->status;
            if ($subscription->status === SubscriptionStatus::EXPIRED) {
                $newStatus = SubscriptionStatus::ACTIVE;
            }

            $subscription->update([
                'expires_at' => $newExpiresAt,
                'status' => $newStatus,
            ]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::RENEWED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $subscription->status->value,
                newStatus: $newStatus->value,
                changedByType: $changedByType,
                changedById: $changedById,
                reason: $reason,
                metadata: [
                    'days_added' => $days,
                    'old_expires_at' => $oldExpiresAt?->toISOString(),
                    'new_expires_at' => $newExpiresAt->toISOString(),
                ]
            );

            return $subscription->fresh();
        });
    }

    /**
     * Cancel subscription.
     */
    public function cancel(
        Subscription $subscription,
        ?string $reason = null,
        bool $immediate = false,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $reason, $immediate, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $updateData = [
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'auto_renew' => false,
            ];

            if ($immediate) {
                $updateData['status'] = SubscriptionStatus::CANCELLED;
                $updateData['expires_at'] = now();
            }

            $subscription->update($updateData);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::CANCELLED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $oldStatus,
                newStatus: $subscription->status->value,
                changedByType: $changedByType,
                changedById: $changedById,
                reason: $reason,
                metadata: ['immediate' => $immediate]
            );

            return $subscription->fresh();
        });
    }

    /**
     * Reactivate a cancelled or expired subscription.
     */
    public function reactivate(
        Subscription $subscription,
        int $daysToExtend = 30,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $daysToExtend, $reason, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'expires_at' => now()->addDays($daysToExtend),
            ]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::REACTIVATED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $oldStatus,
                newStatus: SubscriptionStatus::ACTIVE->value,
                changedByType: $changedByType,
                changedById: $changedById,
                reason: $reason,
                metadata: ['days_extended' => $daysToExtend]
            );

            return $subscription->fresh();
        });
    }

    /**
     * Convert trial to paid subscription.
     */
    public function convertTrial(
        Subscription $subscription,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'trial_ends_at' => now(),
                'expires_at' => now()->addMonth(),
            ]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::TRIAL_CONVERTED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $oldStatus,
                newStatus: SubscriptionStatus::ACTIVE->value,
                changedByType: $changedByType,
                changedById: $changedById,
            );

            return $subscription->fresh();
        });
    }

    /**
     * Suspend subscription.
     */
    public function suspend(
        Subscription $subscription,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $reason, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $subscription->update([
                'status' => SubscriptionStatus::SUSPENDED,
            ]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::SUSPENDED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $oldStatus,
                newStatus: SubscriptionStatus::SUSPENDED->value,
                changedByType: $changedByType,
                changedById: $changedById,
                reason: $reason,
            );

            return $subscription->fresh();
        });
    }
}
