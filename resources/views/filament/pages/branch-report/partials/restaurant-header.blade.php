@php
    $restaurantInfo = $this->getRestaurantInfo();
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
    {{-- Hero Section with Gradient --}}
    <div class="relative h-32 sm:h-48 bg-gradient-to-br from-blue-500 to-indigo-600">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute bottom-4 right-4 left-4 sm:bottom-6 sm:right-6 sm:left-6">
            <div class="flex items-end gap-4 sm:gap-6">
                {{-- Restaurant Image --}}
                <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-xl overflow-hidden border-4 border-white shadow-lg bg-white flex-shrink-0">
                    @if($restaurantInfo['photoUrl'])
                        <img src="{{ $restaurantInfo['photoUrl'] }}" alt="{{ $restaurantInfo['name'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-blue-50">
                            <x-heroicon-o-building-storefront class="w-8 h-8 sm:w-12 sm:h-12 text-blue-600" />
                        </div>
                    @endif
                </div>

                {{-- Restaurant Info --}}
                <div class="flex-1 text-white min-w-0">
                    <h1 class="text-xl sm:text-3xl font-bold mb-1 sm:mb-2 truncate">
                        {{ $restaurantInfo['name'] }}
                    </h1>
                    <div class="flex items-center text-white/90">
                        <x-heroicon-o-map-pin class="w-4 h-4 sm:w-5 sm:h-5 ml-2 flex-shrink-0" />
                        <span class="text-sm sm:text-lg truncate">{{ $restaurantInfo['location'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Date Bar --}}
    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
        <div class="flex flex-row items-center justify-between text-sm">
            <div class="text-gray-600 dark:text-gray-400">
                <span class="font-medium">تاريخ التقرير: {{ now()->locale('ar')->isoFormat('D MMMM YYYY') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700 text-xs sm:text-sm px-3 py-1 rounded-full">
                    تقرير آخر 3 شهور
                </span>
                <x-filament::button wire:click="startNewAnalysis" icon="heroicon-o-arrow-path" size="sm" color="gray">
                    تحديث
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
