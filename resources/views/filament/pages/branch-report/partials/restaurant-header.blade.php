@php
    $restaurantInfo = $this->getRestaurantInfo();
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
    {{-- Hero Section with Gradient --}}
    <div class="p-6 sm:p-8 rounded-t-2xl" style="background: linear-gradient(to bottom right, rgb(59 130 246), rgb(79 70 229));">
        <div class="flex items-center" style="gap: 1.5rem;">
            {{-- Restaurant Image --}}
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-4 border-white shadow-xl bg-white flex-shrink-0">
                @if($restaurantInfo['photoUrl'])
                    <img src="{{ $restaurantInfo['photoUrl'] }}" alt="{{ $restaurantInfo['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center" style="background: rgb(239 246 255);">
                        <x-heroicon-o-building-storefront class="w-10 h-10 sm:w-12 sm:h-12 text-blue-600" />
                    </div>
                @endif
            </div>

            {{-- Restaurant Info --}}
            <div class="flex-1 text-white min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold mb-2 truncate">
                    {{ $restaurantInfo['name'] }}
                </h1>
                <div class="flex items-center gap-2" style="color: rgba(255,255,255,0.95);">
                    <x-heroicon-o-map-pin class="w-5 h-5 sm:w-6 sm:h-6 flex-shrink-0" />
                    <span class="text-base sm:text-lg truncate">{{ $restaurantInfo['location'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Date Bar --}}
    <div class="px-6 py-4 sm:px-8 sm:py-5 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
        <div class="flex flex-row items-center justify-between text-sm">
            <div class="text-gray-600 dark:text-gray-400">
                <span class="font-medium text-base">تاريخ التقرير: {{ now()->locale('ar')->isoFormat('D MMMM YYYY') }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm px-4 py-1.5 rounded-full font-medium" style="background: rgb(239 246 255); color: rgb(29 78 216); border: 1px solid rgb(191 219 254);">
                    تقرير آخر 3 شهور
                </span>
                <x-filament::button wire:click="startNewAnalysis" icon="heroicon-o-arrow-path" size="sm" color="gray">
                    تحديث
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
