@if($hasAccess())
    {{ $slot }}
@else
    @if($getFallbackContent())
        {{ $getFallbackContent() }}
    @else
        <div class="p-4 rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800">
            <div class="flex items-center gap-3">
                <x-heroicon-o-lock-closed class="w-6 h-6 text-warning-500" />
                <div>
                    <p class="font-medium text-warning-700 dark:text-warning-300">
                        ميزة مقفلة
                    </p>
                    <p class="text-sm text-warning-600 dark:text-warning-400">
                        هذه الميزة غير متاحة في باقتك الحالية.
                        <a href="{{ route('filament.admin.pages.subscription') }}" class="underline">
                            ترقية الآن
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endif
@endif
