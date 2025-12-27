<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\Pages;

use App\Filament\SuperAdmin\Resources\TenantResource;
use App\Filament\SuperAdmin\Resources\TenantResource\Widgets\TenantActivityWidget;
use App\Filament\SuperAdmin\Resources\TenantResource\Widgets\TenantUsageWidget;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),

            Actions\Action::make('change_plan')
                ->label('تغيير الباقة')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('plan_id')
                        ->label('الباقة الجديدة')
                        ->options(Plan::active()->pluck('name_ar', 'id'))
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('action')
                        ->label('نوع التغيير')
                        ->options([
                            'immediate' => 'فوري',
                            'end_of_period' => 'نهاية الفترة الحالية',
                        ])
                        ->default('immediate')
                        ->native(false),

                    Forms\Components\Textarea::make('reason')
                        ->label('سبب التغيير')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $tenant = $this->record;
                    $newPlan = Plan::find($data['plan_id']);

                    app(SubscriptionService::class)
                        ->changePlan(
                            $tenant->currentSubscription,
                            $newPlan,
                            $data['action'] === 'immediate',
                            $data['reason'] ?? null,
                            'super_admin',
                            auth()->guard('super_admin')->id()
                        );

                    Notification::make()
                        ->title('تم تغيير الباقة')
                        ->body("تم تغيير الباقة إلى {$newPlan->name_ar}")
                        ->success()
                        ->send();

                    $this->refreshFormData(['currentSubscription']);
                })
                ->visible(fn () => $this->record->currentSubscription),

            Actions\Action::make('extend_subscription')
                ->label('تمديد الاشتراك')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('period')
                        ->label('مدة التمديد')
                        ->options([
                            '7' => 'أسبوع',
                            '30' => 'شهر',
                            '90' => '3 أشهر',
                            '180' => '6 أشهر',
                            '365' => 'سنة',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Textarea::make('reason')
                        ->label('سبب التمديد')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $subscription = $this->record->currentSubscription;

                    if (!$subscription) {
                        Notification::make()
                            ->title('لا يوجد اشتراك')
                            ->danger()
                            ->send();
                        return;
                    }

                    app(SubscriptionService::class)
                        ->extend(
                            $subscription,
                            (int) $data['period'],
                            $data['reason'] ?? null,
                            'super_admin',
                            auth()->guard('super_admin')->id()
                        );

                    Notification::make()
                        ->title('تم تمديد الاشتراك')
                        ->success()
                        ->send();

                    $this->refreshFormData(['currentSubscription']);
                }),

            Actions\Action::make('impersonate')
                ->label('الدخول كعميل')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    $admin = $this->record->users()->where('role', 'admin')->first();

                    if (!$admin) {
                        Notification::make()
                            ->title('لا يوجد مسؤول')
                            ->danger()
                            ->send();
                        return;
                    }

                    session(['impersonating_from' => auth()->guard('super_admin')->id()]);
                    auth()->guard('web')->login($admin);

                    return redirect('/admin');
                })
                ->visible(fn () => $this->record->is_active),

            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TenantUsageWidget::class,
            TenantActivityWidget::class,
        ];
    }
}
