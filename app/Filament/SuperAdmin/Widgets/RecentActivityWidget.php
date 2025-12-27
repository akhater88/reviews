<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Payment;
use App\Models\SubscriptionHistory;
use App\Models\Tenant;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.super-admin.widgets.recent-activity';
    protected static ?string $heading = 'النشاط الأخير';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 3;
    protected static ?string $maxHeight = '400px';

    public function getActivities(): Collection
    {
        $activities = collect();

        // Recent tenant registrations
        $recentTenants = Tenant::latest()
            ->take(5)
            ->get()
            ->map(fn ($tenant) => [
                'type' => 'tenant_created',
                'icon' => 'heroicon-o-user-plus',
                'color' => 'success',
                'title' => 'عميل جديد',
                'description' => $tenant->name,
                'timestamp' => $tenant->created_at,
            ]);

        // Recent subscription changes
        $recentSubscriptions = SubscriptionHistory::with(['subscription.tenant', 'newPlan'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($history) => [
                'type' => 'subscription_' . $history->action->value,
                'icon' => $this->getActionIcon($history->action->value),
                'color' => $history->action->color(),
                'title' => $history->action->label(),
                'description' => ($history->subscription?->tenant?->name ?? 'Unknown') .
                    ($history->newPlan ? ' - ' . $history->newPlan->name_ar : ''),
                'timestamp' => $history->created_at,
            ]);

        // Recent payments
        $recentPayments = Payment::with(['tenant'])
            ->where('status', 'completed')
            ->latest('paid_at')
            ->take(5)
            ->get()
            ->map(fn ($payment) => [
                'type' => 'payment_completed',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'success',
                'title' => 'دفعة جديدة',
                'description' => ($payment->tenant?->name ?? 'Unknown') . ' - ' .
                    ($payment->currency === 'SAR' ? 'ر.س' : '$') . ' ' . number_format($payment->amount, 2),
                'timestamp' => $payment->paid_at,
            ]);

        return $activities
            ->merge($recentTenants)
            ->merge($recentSubscriptions)
            ->merge($recentPayments)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();
    }

    private function getActionIcon(string $action): string
    {
        return match ($action) {
            'created' => 'heroicon-o-plus-circle',
            'upgraded' => 'heroicon-o-arrow-trending-up',
            'downgraded' => 'heroicon-o-arrow-trending-down',
            'renewed' => 'heroicon-o-arrow-path',
            'cancelled' => 'heroicon-o-x-circle',
            'expired' => 'heroicon-o-clock',
            'reactivated' => 'heroicon-o-check-circle',
            'trial_started' => 'heroicon-o-play',
            'trial_ended' => 'heroicon-o-stop',
            default => 'heroicon-o-information-circle',
        };
    }
}
