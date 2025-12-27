<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Mail\TenantWelcomeMail;
use App\Mail\TenantCredentialsMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    /**
     * Send welcome email with credentials to new tenant admin
     */
    public function sendWelcomeEmail(User $admin, string $password, Tenant $tenant): void
    {
        try {
            Mail::to($admin->email)->send(new TenantWelcomeMail(
                admin: $admin,
                password: $password,
                tenant: $tenant,
                loginUrl: config('app.url') . '/admin/login'
            ));

            Log::info('Welcome email sent', [
                'tenant_id' => $tenant->id,
                'admin_email' => $admin->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send credentials email (for password reset by admin)
     */
    public function sendCredentialsEmail(User $user, ?string $password, Tenant $tenant): void
    {
        try {
            Mail::to($user->email)->send(new TenantCredentialsMail(
                user: $user,
                password: $password,
                tenant: $tenant,
                loginUrl: config('app.url') . '/admin/login',
                isReset: $password !== null
            ));

            Log::info('Credentials email sent', [
                'tenant_id' => $tenant->id,
                'user_email' => $user->email,
                'is_reset' => $password !== null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send credentials email', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create tenant with admin and subscription
     */
    public function provision(array $tenantData, array $adminData, int $planId, bool $startTrial = true): Tenant
    {
        return DB::transaction(function () use ($tenantData, $adminData, $planId, $startTrial) {
            // Create tenant
            $tenant = Tenant::create($tenantData);

            // Create admin
            $password = Str::random(12);
            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'phone' => $adminData['phone'] ?? null,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Create subscription
            $plan = Plan::find($planId);
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $startTrial
                    ? SubscriptionStatus::TRIAL
                    : SubscriptionStatus::ACTIVE,
                'billing_cycle' => BillingCycle::MONTHLY,
                'currency' => $tenant->preferred_currency ?? 'SAR',
                'started_at' => now(),
                'trial_ends_at' => $startTrial ? now()->addDays(config('subscription.trial_days', 7)) : null,
                'expires_at' => $startTrial
                    ? now()->addDays(config('subscription.trial_days', 7))
                    : now()->addMonth(),
            ]);

            $tenant->update(['current_subscription_id' => $subscription->id]);

            // Send email
            $this->sendWelcomeEmail($admin, $password, $tenant);

            return $tenant;
        });
    }
}
