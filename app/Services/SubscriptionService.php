<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionAction;
use App\Enums\SubscriptionStatus;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Create a new subscription for a tenant
     */
    public function create(
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle = BillingCycle::MONTHLY,
        bool $startTrial = true,
        ?string $currency = null
    ): Subscription {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle, $startTrial, $currency) {
            $currency = $currency ?? $tenant->preferred_currency ?? config('subscription.default_currency', 'SAR');
            $trialDays = config('subscription.trial_days', 7);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $startTrial && !$plan->is_free ? SubscriptionStatus::TRIAL : SubscriptionStatus::ACTIVE,
                'billing_cycle' => $billingCycle,
                'currency' => $currency,
                'price_at_renewal' => $plan->getPrice($billingCycle->value, strtolower($currency)),
                'started_at' => now(),
                'trial_ends_at' => $startTrial && !$plan->is_free ? now()->addDays($trialDays) : null,
                'expires_at' => $this->calculateExpiryDate($billingCycle, $startTrial && !$plan->is_free, $plan->is_free),
                'next_billing_date' => $startTrial && !$plan->is_free ? now()->addDays($trialDays) : now()->addMonth(),
            ]);

            // Update tenant's current subscription
            $tenant->update(['current_subscription_id' => $subscription->id]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::CREATED,
                newPlanId: $plan->id,
                newStatus: $subscription->status->value,
                changedByType: 'system',
            );

            Log::info('Subscription created', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Change the subscription plan (upgrade/downgrade).
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
            $oldPlan = $subscription->plan;
            $oldStatus = $subscription->status->value;

            $isUpgrade = $newPlan->price_monthly_sar > ($oldPlan->price_monthly_sar ?? 0);
            $action = $isUpgrade ? SubscriptionAction::UPGRADED : SubscriptionAction::DOWNGRADED;

            if ($immediate) {
                // Calculate proration if upgrading mid-cycle
                $prorationCredit = 0;
                if ($isUpgrade && $subscription->status === SubscriptionStatus::ACTIVE) {
                    $prorationCredit = $this->calculateProrationCredit($subscription);
                }

                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'price_at_renewal' => $newPlan->getPrice(
                        $subscription->billing_cycle->value,
                        strtolower($subscription->currency)
                    ),
                    'proration_credit' => $prorationCredit,
                ]);

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
                    metadata: ['immediate' => true, 'proration_credit' => $prorationCredit]
                );

                // Generate invoice for upgrade
                if ($isUpgrade && !$newPlan->is_free) {
                    $this->generateUpgradeInvoice($subscription, $oldPlan, $newPlan, $prorationCredit);
                }
            } else {
                // Schedule plan change for end of period
                $subscription->update([
                    'scheduled_plan_id' => $newPlan->id,
                    'scheduled_change_date' => $subscription->expires_at,
                ]);

                // Record history
                $subscription->recordHistory(
                    action: SubscriptionAction::PLAN_CHANGE_SCHEDULED,
                    oldPlanId: $oldPlanId,
                    newPlanId: $newPlan->id,
                    oldStatus: $oldStatus,
                    newStatus: $subscription->status->value,
                    changedByType: $changedByType,
                    changedById: $changedById,
                    reason: $reason,
                    metadata: [
                        'immediate' => false,
                        'scheduled_for' => $subscription->expires_at?->toDateTimeString(),
                    ]
                );
            }

            Log::info('Subscription plan changed', [
                'subscription_id' => $subscription->id,
                'old_plan' => $oldPlanId,
                'new_plan' => $newPlan->id,
                'immediate' => $immediate,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Extend the subscription by days.
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
            $oldStatus = $subscription->status->value;

            $newExpiresAt = ($oldExpiresAt ?? now())->addDays($days);

            // If subscription was expired, reactivate it
            $newStatus = $subscription->status;
            if (in_array($subscription->status, [SubscriptionStatus::EXPIRED, SubscriptionStatus::GRACE_PERIOD])) {
                $newStatus = SubscriptionStatus::ACTIVE;
            }

            $updateData = [
                'expires_at' => $newExpiresAt,
                'status' => $newStatus,
            ];

            // Also extend trial if in trial
            if ($subscription->trial_ends_at && $subscription->trial_ends_at > now()) {
                $updateData['trial_ends_at'] = $subscription->trial_ends_at->addDays($days);
            }

            $subscription->update($updateData);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::EXTENDED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $subscription->plan_id,
                oldStatus: $oldStatus,
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

            Log::info('Subscription extended', [
                'subscription_id' => $subscription->id,
                'days' => $days,
                'new_expiry' => $newExpiresAt,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cancel subscription.
     */
    public function cancel(
        Subscription $subscription,
        bool $immediate = false,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $immediate, $reason, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            if ($immediate) {
                $subscription->update([
                    'status' => SubscriptionStatus::CANCELLED,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                    'auto_renew' => false,
                ]);
            } else {
                // Cancel at end of period
                $subscription->update([
                    'cancel_at_period_end' => true,
                    'cancellation_reason' => $reason,
                    'auto_renew' => false,
                ]);
            }

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

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'immediate' => $immediate,
                'reason' => $reason,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Reactivate a cancelled or expired subscription.
     */
    public function reactivate(
        Subscription $subscription,
        ?string $reason = null,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $reason, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'cancelled_at' => null,
                'cancel_at_period_end' => false,
                'cancellation_reason' => null,
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
            );

            Log::info('Subscription reactivated', [
                'subscription_id' => $subscription->id,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Renew subscription.
     */
    public function renew(
        Subscription $subscription,
        ?string $changedByType = null,
        ?int $changedById = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $changedByType, $changedById) {
            $oldStatus = $subscription->status->value;

            $newExpiryDate = $this->calculateExpiryDate(
                $subscription->billing_cycle,
                false,
                $subscription->plan->is_free ?? false
            );

            // Handle scheduled plan change
            $planId = $subscription->plan_id;
            if ($subscription->scheduled_plan_id) {
                $planId = $subscription->scheduled_plan_id;
            }

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'plan_id' => $planId,
                'expires_at' => $newExpiryDate,
                'next_billing_date' => $newExpiryDate,
                'renewed_at' => now(),
                'scheduled_plan_id' => null,
                'scheduled_change_date' => null,
            ]);

            // Record history
            $subscription->recordHistory(
                action: SubscriptionAction::RENEWED,
                oldPlanId: $subscription->plan_id,
                newPlanId: $planId,
                oldStatus: $oldStatus,
                newStatus: SubscriptionStatus::ACTIVE->value,
                changedByType: $changedByType,
                changedById: $changedById,
                metadata: ['new_expiry' => $newExpiryDate->toDateTimeString()]
            );

            // Generate renewal invoice
            if (!$subscription->plan->is_free) {
                $this->generateRenewalInvoice($subscription);
            }

            Log::info('Subscription renewed', [
                'subscription_id' => $subscription->id,
                'new_expiry' => $newExpiryDate,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Handle expired subscriptions.
     */
    public function handleExpired(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $gracePeriodDays = config('subscription.grace_period_days', 3);
            $oldStatus = $subscription->status->value;

            // Check if still in grace period
            if ($subscription->expires_at && $subscription->expires_at->addDays($gracePeriodDays) > now()) {
                $subscription->update([
                    'status' => SubscriptionStatus::GRACE_PERIOD,
                    'grace_ends_at' => $subscription->expires_at->addDays($gracePeriodDays),
                ]);

                $subscription->recordHistory(
                    action: SubscriptionAction::GRACE_PERIOD_STARTED,
                    oldPlanId: $subscription->plan_id,
                    oldStatus: $oldStatus,
                    newStatus: SubscriptionStatus::GRACE_PERIOD->value,
                    changedByType: 'system',
                    metadata: ['grace_until' => $subscription->expires_at->addDays($gracePeriodDays)->toDateTimeString()]
                );
            } else {
                // Grace period ended, expire the subscription
                $subscription->update(['status' => SubscriptionStatus::EXPIRED]);

                $subscription->recordHistory(
                    action: SubscriptionAction::EXPIRED,
                    oldPlanId: $subscription->plan_id,
                    oldStatus: $oldStatus,
                    newStatus: SubscriptionStatus::EXPIRED->value,
                    changedByType: 'system',
                    reason: 'Subscription expired after grace period',
                );
            }

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

            $newExpiryDate = $this->calculateExpiryDate($subscription->billing_cycle, false, false);

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'trial_ends_at' => now(),
                'expires_at' => $newExpiryDate,
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

            // Generate first invoice
            if (!$subscription->plan->is_free) {
                $this->generateRenewalInvoice($subscription);
            }

            Log::info('Trial converted to paid', [
                'subscription_id' => $subscription->id,
            ]);

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

    /**
     * Calculate proration credit.
     */
    protected function calculateProrationCredit(Subscription $subscription): float
    {
        if (!$subscription->expires_at || !$subscription->price_at_renewal) {
            return 0;
        }

        $totalDays = $subscription->billing_cycle === BillingCycle::YEARLY ? 365 : 30;
        $remainingDays = now()->diffInDays($subscription->expires_at, false);

        if ($remainingDays <= 0) {
            return 0;
        }

        $dailyRate = $subscription->price_at_renewal / $totalDays;
        return round($dailyRate * $remainingDays, 2);
    }

    /**
     * Calculate expiry date based on billing cycle.
     */
    protected function calculateExpiryDate(BillingCycle $cycle, bool $isTrial, bool $isFree): Carbon
    {
        if ($isFree) {
            return now()->addYears(10); // Essentially no expiry
        }

        if ($isTrial) {
            return now()->addDays(config('subscription.trial_days', 7));
        }

        return match ($cycle) {
            BillingCycle::MONTHLY => now()->addMonth(),
            BillingCycle::YEARLY => now()->addYear(),
            BillingCycle::LIFETIME => now()->addYears(100),
        };
    }

    /**
     * Generate upgrade invoice.
     */
    protected function generateUpgradeInvoice(
        Subscription $subscription,
        Plan $oldPlan,
        Plan $newPlan,
        float $prorationCredit
    ): Invoice {
        $amount = $newPlan->getPrice(
            $subscription->billing_cycle->value,
            strtolower($subscription->currency)
        );

        $finalAmount = max(0, $amount - $prorationCredit);
        $tax = $this->calculateTax($finalAmount);

        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'currency' => $subscription->currency,
            'subtotal' => $amount,
            'discount_amount' => $prorationCredit,
            'tax_amount' => $tax,
            'total_amount' => $finalAmount + $tax,
            'status' => InvoiceStatus::PENDING,
            'billing_cycle' => $subscription->billing_cycle,
            'due_date' => now()->addDays(7),
            'period_start' => now(),
            'period_end' => $subscription->expires_at,
            'items' => [
                [
                    'description' => "Upgrade to {$newPlan->name}",
                    'description_ar' => "ترقية إلى {$newPlan->name_ar}",
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total' => $amount,
                ],
                [
                    'description' => "Proration credit from {$oldPlan->name}",
                    'description_ar' => "خصم من الباقة السابقة {$oldPlan->name_ar}",
                    'quantity' => 1,
                    'unit_price' => -$prorationCredit,
                    'total' => -$prorationCredit,
                ],
            ],
            'notes' => "Plan upgrade from {$oldPlan->name} to {$newPlan->name}",
        ]);
    }

    /**
     * Generate renewal invoice.
     */
    protected function generateRenewalInvoice(Subscription $subscription): Invoice
    {
        $amount = $subscription->price_at_renewal ?? $subscription->plan->getPrice(
            $subscription->billing_cycle->value,
            strtolower($subscription->currency)
        );
        $tax = $this->calculateTax($amount);

        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'currency' => $subscription->currency,
            'subtotal' => $amount,
            'discount_amount' => 0,
            'tax_amount' => $tax,
            'total_amount' => $amount + $tax,
            'status' => InvoiceStatus::PENDING,
            'billing_cycle' => $subscription->billing_cycle,
            'due_date' => now()->addDays(7),
            'period_start' => now(),
            'period_end' => $subscription->expires_at,
            'items' => [
                [
                    'description' => "{$subscription->plan->name} - {$subscription->billing_cycle->labelEn()}",
                    'description_ar' => "{$subscription->plan->name_ar} - {$subscription->billing_cycle->label()}",
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total' => $amount,
                ],
            ],
        ]);
    }

    /**
     * Calculate tax.
     */
    protected function calculateTax(float $amount): float
    {
        $taxRate = config('subscription.invoice.tax_rate', 15);
        return round($amount * ($taxRate / 100), 2);
    }
}
