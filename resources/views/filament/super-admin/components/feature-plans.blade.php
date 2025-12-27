<div class="space-y-2">
    @foreach($plans as $plan)
        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800">
            <span class="w-3 h-3 rounded-full" style="background-color: {{ match($plan->color) {
                'primary' => '#6366f1',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'info' => '#0ea5e9',
                default => '#6b7280',
            } }}"></span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $plan->name_ar }}</span>
            @if($plan->pivot->limit_value)
                <span class="text-xs text-gray-500">(حد: {{ $plan->pivot->limit_value }})</span>
            @endif
        </div>
    @endforeach
</div>
