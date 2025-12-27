<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
    <div class="flex items-center gap-4 mb-4">
        <div class="flex-1">
            <span class="px-3 py-1 text-sm font-medium rounded-full" style="background-color: {{ match($subscription->plan?->color) {
                'primary' => '#6366f1',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'info' => '#0ea5e9',
                default => '#6b7280',
            } }}20; color: {{ match($subscription->plan?->color) {
                'primary' => '#6366f1',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'info' => '#0ea5e9',
                default => '#6b7280',
            } }}">
                {{ $subscription->plan?->name_ar ?? 'بدون باقة' }}
            </span>
        </div>
        <span class="px-2 py-1 text-xs font-medium rounded-full" style="background-color: {{ match($subscription->status->value) {
            'active' => '#10b981',
            'trial' => '#0ea5e9',
            'expired' => '#ef4444',
            default => '#6b7280',
        } }}20; color: {{ match($subscription->status->value) {
            'active' => '#10b981',
            'trial' => '#0ea5e9',
            'expired' => '#ef4444',
            default => '#6b7280',
        } }}">
            {{ $subscription->status->label() }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-gray-500">دورة الفوترة:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->billing_cycle->label() }}</span>
        </div>
        <div>
            <span class="text-gray-500">العملة:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->currency }}</span>
        </div>
        <div>
            <span class="text-gray-500">تاريخ البدء:</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->started_at?->format('Y-m-d') ?? '-' }}</span>
        </div>
        <div>
            <span class="text-gray-500">تاريخ الانتهاء:</span>
            <span class="font-medium {{ $subscription->isExpiringSoon() ? 'text-warning-600' : 'text-gray-900 dark:text-white' }}">
                {{ $subscription->expires_at?->format('Y-m-d') ?? '-' }}
                @if($subscription->daysUntilExpiry() > 0)
                    ({{ $subscription->daysUntilExpiry() }} يوم)
                @endif
            </span>
        </div>
    </div>
</div>
