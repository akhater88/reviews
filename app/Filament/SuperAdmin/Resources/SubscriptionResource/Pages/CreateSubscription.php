<?php

namespace App\Filament\SuperAdmin\Resources\SubscriptionResource\Pages;

use App\Enums\BillingCycle;
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

        $subscription = app(SubscriptionService::class)->create(
            $tenant,
            $plan,
            $billingCycle,
            startTrial: false,
            currency: $data['currency']
        );

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
