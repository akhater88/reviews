<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            النشاط الأخير
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getActivities() as $activity)
                <div class="flex items-start gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div @class([
                        'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                        'bg-success-100 dark:bg-success-900/50' => $activity['color'] === 'success',
                        'bg-warning-100 dark:bg-warning-900/50' => $activity['color'] === 'warning',
                        'bg-danger-100 dark:bg-danger-900/50' => $activity['color'] === 'danger',
                        'bg-info-100 dark:bg-info-900/50' => $activity['color'] === 'info',
                        'bg-gray-100 dark:bg-gray-700' => !in_array($activity['color'], ['success', 'warning', 'danger', 'info']),
                    ])>
                        <x-dynamic-component
                            :component="$activity['icon']"
                            @class([
                                'w-5 h-5',
                                'text-success-600 dark:text-success-400' => $activity['color'] === 'success',
                                'text-warning-600 dark:text-warning-400' => $activity['color'] === 'warning',
                                'text-danger-600 dark:text-danger-400' => $activity['color'] === 'danger',
                                'text-info-600 dark:text-info-400' => $activity['color'] === 'info',
                                'text-gray-600 dark:text-gray-400' => !in_array($activity['color'], ['success', 'warning', 'danger', 'info']),
                            ])
                        />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $activity['title'] }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ $activity['description'] }}
                        </p>
                    </div>

                    <div class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500">
                        {{ $activity['timestamp']->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-400" />
                    <p class="mt-2 text-gray-500 dark:text-gray-400">لا يوجد نشاط حتى الآن</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
