<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Filament\SuperAdmin\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $tenant = Tenant::find($data['tenant_id']);
        $plan = Plan::find($data['plan_id']);
        $billingCycle = BillingCycle::from($data['billing_cycle']);
        $status = SubscriptionStatus::from($data['status']);

        // Determine if this is a trial based on selected status
        $startTrial = $status === SubscriptionStatus::TRIAL;

        $subscription = app(SubscriptionService::class)->create(
            $tenant,
            $plan,
            $billingCycle,
            startTrial: $startTrial,
            currency: $data['currency']
        );

        // If admin selected a different status, update it
        if ($subscription->status !== $status) {
            $subscription->update(['status' => $status]);
        }

        Notification::make()
            ->title('تم إنشاء الاشتراك')
            ->success()
            ->send();

        return $subscription;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
