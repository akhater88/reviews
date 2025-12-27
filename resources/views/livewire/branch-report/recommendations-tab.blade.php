<div class="space-y-6" dir="rtl">
    {{-- Immediate Actions --}}
    @if(!empty($data['immediateActions']))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-bolt class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">إجراءات فورية</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">إجراءات عاجلة يجب تنفيذها</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                @foreach($data['immediateActions'] as $action)
                    @php
                        $priorityColor = match($action['priority'] ?? 'medium') {
                            'high' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            'low' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                        };
                        $priorityLabel = match($action['priority'] ?? 'medium') {
                            'high' => 'عاجل',
                            'low' => 'منخفض',
                            default => 'متوسط',
                        };
                    @endphp
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $action['title'] }}</h4>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColor }}">{{ $priorityLabel }}</span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $action['description'] }}</p>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-clock class="w-4 h-4" />
                                <span>{{ $action['timeframe'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-arrow-trending-up class="w-4 h-4" />
                                <span>{{ $action['expectedImpact'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Strategic Initiatives --}}
    @if(!empty($data['strategicInitiatives']))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-rocket-launch class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">مبادرات استراتيجية</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">خطط طويلة المدى للتحسين</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                @foreach($data['strategicInitiatives'] as $initiative)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $initiative['title'] }}</h4>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $initiative['description'] }}</p>

                        @if(!empty($initiative['steps']))
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الخطوات:</h5>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    @foreach($initiative['steps'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $initiative['timeframe'] ?? '-' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Operational Improvements --}}
    @if(!empty($data['operationalImprovements']))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تحسينات تشغيلية</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">تحسينات يومية للعمليات</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Service --}}
                    @if(!empty($data['operationalImprovements']['service']))
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <x-heroicon-o-hand-raised class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                <h4 class="font-semibold text-gray-900 dark:text-white">الخدمة</h4>
                            </div>
                            <ul class="space-y-2">
                                @foreach($data['operationalImprovements']['service'] as $item)
                                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Food --}}
                    @if(!empty($data['operationalImprovements']['food']))
                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <x-heroicon-o-cake class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                <h4 class="font-semibold text-gray-900 dark:text-white">الطعام</h4>
                            </div>
                            <ul class="space-y-2">
                                @foreach($data['operationalImprovements']['food'] as $item)
                                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" />
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Environment --}}
                    @if(!empty($data['operationalImprovements']['environment']))
                        <div class="bg-teal-50 dark:bg-teal-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <x-heroicon-o-building-storefront class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                <h4 class="font-semibold text-gray-900 dark:text-white">البيئة</h4>
                            </div>
                            <ul class="space-y-2">
                                @foreach($data['operationalImprovements']['environment'] as $item)
                                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-teal-500 mt-0.5 flex-shrink-0" />
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- No Data State --}}
    @if(empty($data['immediateActions']) && empty($data['strategicInitiatives']) && empty($data['operationalImprovements']))
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
            <x-heroicon-o-light-bulb class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد توصيات</h3>
            <p class="text-gray-500 dark:text-gray-400">سيتم إنشاء التوصيات بعد تحليل المراجعات</p>
        </div>
    @endif
</div>
