<x-filament-panels::page>
    {{-- Google Connection Status Card --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-link class="w-5 h-5" />
                حالة الربط مع Google Business
            </div>
        </x-slot>

        <div class="space-y-4">
            @if($this->isConnected)
                {{-- Connected State --}}
                <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">متصل</h3>
                            <div class="text-sm text-green-600 dark:text-green-400">
                                <p>{{ $this->connectedName ?? 'حساب Google Business' }}</p>
                                <p class="text-xs">{{ $this->connectedEmail }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Google Locations Section --}}
                @if(count($this->googleLocations) > 0)
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            الفروع المتاحة في Google Business ({{ count($this->googleLocations) }})
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($this->googleLocations as $location)
                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900 dark:text-white">
                                                {{ $location['name'] }}
                                            </h5>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $location['address'] }}
                                            </p>
                                        </div>
                                        <x-filament::button
                                            size="sm"
                                            color="primary"
                                            wire:click="importLocation('{{ $location['account_id'] }}', '{{ $location['location_id'] }}', '{{ addslashes($location['name']) }}', '{{ $location['place_id'] }}', '{{ addslashes($location['address']) }}')"
                                        >
                                            إضافة
                                        </x-filament::button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($this->loadingLocations)
                    <div class="flex items-center justify-center py-8">
                        <x-filament::loading-indicator class="w-8 h-8" />
                        <span class="ms-2 text-gray-500">جاري تحميل الفروع...</span>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <p>اضغط على "تحديث الفروع من Google" لتحميل الفروع</p>
                    </div>
                @endif

            @else
                {{-- Disconnected State --}}
                <div class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        ربط حساب Google Business
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-md mb-4">
                        قم بربط حساب Google Business الخاص بك لاستيراد فروعك تلقائياً والرد على المراجعات
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mb-4">
                        اضغط على "ربط حساب Google Business" في الأعلى
                    </p>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Manual Branch Section --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-storefront class="w-5 h-5" />
                الفروع
            </div>
        </x-slot>

        <x-slot name="description">
            قم بإدارة فروعك أو إضافة فروع منافسين للمقارنة. اضغط على "إضافة فرع يدوياً" في الأعلى.
        </x-slot>

        {{-- Info Alert --}}
        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
            <div class="flex gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-amber-700 dark:text-amber-300">
                    <p class="font-medium mb-1">ملاحظة:</p>
                    <ul class="list-disc list-inside space-y-1 text-amber-600 dark:text-amber-400">
                        <li>الفروع المضافة يدوياً لا يمكن الرد على مراجعاتها</li>
                        <li>يمكن ربط فرع كمنافس لمقارنة الأداء</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Branches Table --}}
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
