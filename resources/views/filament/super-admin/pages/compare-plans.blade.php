<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Currency Toggle --}}
        <div class="flex justify-end">
            <button
                wire:click="toggleCurrency"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
            >
                <span class="flex items-center gap-2">
                    <x-heroicon-o-currency-dollar class="w-5 h-5" />
                    {{ $currency === 'SAR' ? 'عرض بالدولار' : 'عرض بالريال' }}
                </span>
            </button>
        </div>

        {{-- Plans Comparison Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white w-64">
                            الميزة
                        </th>
                        @foreach($this->getPlans() as $plan)
                            <th class="px-6 py-4 text-center min-w-40">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full"
                                          style="background-color: {{ match($plan->color) {
                                              'primary' => '#6366f1',
                                              'success' => '#10b981',
                                              'warning' => '#f59e0b',
                                              'danger' => '#ef4444',
                                              'info' => '#0ea5e9',
                                              default => '#6b7280',
                                          } }}20; color: {{ match($plan->color) {
                                              'primary' => '#6366f1',
                                              'success' => '#10b981',
                                              'warning' => '#f59e0b',
                                              'danger' => '#ef4444',
                                              'info' => '#0ea5e9',
                                              default => '#6b7280',
                                          } }}">
                                        {{ $plan->name_ar }}
                                        @if($plan->is_popular)
                                            <x-heroicon-s-star class="w-4 h-4 mr-1 text-warning-500" />
                                        @endif
                                    </span>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        @if($plan->is_free)
                                            مجاني
                                        @else
                                            {{ $currency === 'SAR' ? 'ر.س' : '$' }}
                                            {{ number_format($plan->getPrice('monthly', strtolower($currency)), 0) }}
                                            <span class="text-xs font-normal text-gray-500">/شهر</span>
                                        @endif
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    {{-- Limits Section --}}
                    <tr class="bg-primary-50 dark:bg-primary-900/20">
                        <td colspan="{{ count($this->getPlans()) + 1 }}" class="px-6 py-3 font-bold text-primary-700 dark:text-primary-300">
                            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 inline ml-2" />
                            الحدود
                        </td>
                    </tr>

                    @php
                        $limitRows = [
                            ['key' => 'max_branches', 'label' => 'الفروع الخاصة', 'icon' => 'heroicon-o-building-office'],
                            ['key' => 'max_competitors', 'label' => 'فروع المنافسين', 'icon' => 'heroicon-o-flag'],
                            ['key' => 'max_users', 'label' => 'المستخدمين', 'icon' => 'heroicon-o-users'],
                            ['key' => 'max_ai_replies', 'label' => 'ردود AI / شهر', 'icon' => 'heroicon-o-sparkles'],
                            ['key' => 'max_reviews_sync', 'label' => 'مزامنة المراجعات / شهر', 'icon' => 'heroicon-o-arrow-path'],
                            ['key' => 'analysis_retention_days', 'label' => 'حفظ البيانات', 'icon' => 'heroicon-o-clock', 'suffix' => 'يوم'],
                        ];
                    @endphp

                    @foreach($limitRows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-2">
                                    <x-dynamic-component :component="$row['icon']" class="w-4 h-4 text-gray-400" />
                                    {{ $row['label'] }}
                                </span>
                            </td>
                            @foreach($this->getPlans() as $plan)
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $value = $plan->limits?->{$row['key']} ?? 0;
                                    @endphp
                                    @if($value == -1)
                                        <span class="text-success-600 dark:text-success-400 font-bold">∞</span>
                                    @else
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ number_format($value) }}
                                            @if(isset($row['suffix']))
                                                <span class="text-xs text-gray-500">{{ $row['suffix'] }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- Features by Category --}}
                    @foreach($this->getCategories() as $categoryKey => $categoryInfo)
                        @if($this->getFeaturesByCategory()->has($categoryKey))
                            <tr class="bg-gray-50 dark:bg-gray-800/50">
                                <td colspan="{{ count($this->getPlans()) + 1 }}" class="px-6 py-3 font-bold text-gray-700 dark:text-gray-300">
                                    <x-dynamic-component :component="$categoryInfo['icon']" class="w-5 h-5 inline ml-2" />
                                    {{ $categoryInfo['label'] }}
                                </td>
                            </tr>

                            @foreach($this->getFeaturesByCategory()[$categoryKey] as $feature)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $feature->name_ar }}
                                    </td>
                                    @foreach($this->getPlans() as $plan)
                                        <td class="px-6 py-3 text-center">
                                            @if($this->planHasFeature($plan, $feature))
                                                @php $limit = $this->getFeatureLimit($plan, $feature); @endphp
                                                @if($limit)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-success-100 dark:bg-success-900/50 text-success-700 dark:text-success-400">
                                                        {{ $limit }}
                                                    </span>
                                                @else
                                                    <x-heroicon-s-check-circle class="w-6 h-6 mx-auto text-success-500" />
                                                @endif
                                            @else
                                                <x-heroicon-o-minus class="w-6 h-6 mx-auto text-gray-300 dark:text-gray-600" />
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Yearly Pricing Note --}}
        <div class="p-4 rounded-lg bg-info-50 dark:bg-info-900/20 text-info-700 dark:text-info-300">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-6 h-6 flex-shrink-0" />
                <div>
                    <p class="font-medium">توفير مع الاشتراك السنوي</p>
                    <p class="text-sm opacity-80">احصل على شهرين مجاناً عند الاشتراك السنوي في معظم الباقات</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
