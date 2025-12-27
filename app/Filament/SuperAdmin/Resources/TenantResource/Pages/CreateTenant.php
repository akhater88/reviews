<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Pages;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Filament\SuperAdmin\Resources\TenantResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove admin fields - we'll handle them separately
        unset($data['admin_name'], $data['admin_email'], $data['admin_phone'],
              $data['send_credentials'], $data['plan_id'], $data['start_trial']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $formData = $this->form->getState();
        $tenant = $this->record;

        DB::transaction(function () use ($formData, $tenant) {
            // Generate password
            $password = Str::random(12);

            // Create admin user
            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $formData['admin_name'],
                'email' => $formData['admin_email'],
                'phone' => $formData['admin_phone'] ?? null,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Create subscription
            $plan = Plan::find($formData['plan_id']);
            $startTrial = $formData['start_trial'] ?? true;

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $startTrial ? SubscriptionStatus::TRIAL : SubscriptionStatus::ACTIVE,
                'billing_cycle' => BillingCycle::MONTHLY,
                'currency' => $tenant->preferred_currency ?? 'SAR',
                'started_at' => now(),
                'trial_ends_at' => $startTrial ? now()->addDays(config('subscription.trial_days', 7)) : null,
                'expires_at' => $startTrial
                    ? now()->addDays(config('subscription.trial_days', 7))
                    : now()->addMonth(),
            ]);

            // Update tenant with subscription
            $tenant->update(['current_subscription_id' => $subscription->id]);

            // Send credentials email
            if ($formData['send_credentials'] ?? true) {
                app(TenantProvisioningService::class)
                    ->sendWelcomeEmail($admin, $password, $tenant);
            }

            // Store password temporarily for display
            session(['new_tenant_password' => $password]);
        });

        // Show success notification with password
        $password = session('new_tenant_password');
        session()->forget('new_tenant_password');

        Notification::make()
            ->title('تم إنشاء العميل بنجاح')
            ->body("كلمة المرور: {$password}")
            ->success()
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('copy')
                    ->label('نسخ كلمة المرور')
                    ->button()
                    ->action(fn () => null)
                    ->extraAttributes([
                        'x-on:click' => "navigator.clipboard.writeText('{$password}')",
                    ]),
            ])
            ->send();
    }
}
