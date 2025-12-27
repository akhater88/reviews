<div class="space-y-6" dir="rtl">
    {{-- Summary Cards --}}
    @if(!empty($data['summary']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي المحللين</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $data['summary']['totalAnalyzed'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">الفئة الأكثر</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $data['summary']['dominantGender'] ?? '-' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">الأعلى تقييماً</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $data['summary']['highestRatedGender'] ?? '-' }}</p>
            </div>
        </div>
    @endif

    {{-- Gender Categories --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(($data['categories'] ?? []) as $category)
            @php
                $icon = match($category['category'] ?? '') {
                    'ذكور' => 'heroicon-o-user',
                    'إناث' => 'heroicon-o-user',
                    default => 'heroicon-o-question-mark-circle',
                };
                $bgColor = match($category['category'] ?? '') {
                    'ذكور' => 'bg-blue-100 dark:bg-blue-900/30 border-blue-200 dark:border-blue-700',
                    'إناث' => 'bg-pink-100 dark:bg-pink-900/30 border-pink-200 dark:border-pink-700',
                    default => 'bg-gray-100 dark:bg-gray-900/30 border-gray-200 dark:border-gray-700',
                };
                $iconColor = match($category['category'] ?? '') {
                    'ذكور' => 'text-blue-600 dark:text-blue-400',
                    'إناث' => 'text-pink-600 dark:text-pink-400',
                    default => 'text-gray-600 dark:text-gray-400',
                };
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Header --}}
                <div class="p-6 {{ $bgColor }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center">
                                <x-dynamic-component :component="$icon" class="w-6 h-6 {{ $iconColor }}" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $category['category'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $category['totalReviews'] ?? 0 }} مراجعة</p>
                            </div>
                        </div>
                        <div class="text-left">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($category['percentage'] ?? 0, 1) }}%</p>
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">متوسط التقييم</span>
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-heroicon-s-star class="w-4 h-4 {{ $i <= round($category['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                            </div>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($category['averageRating'] ?? 0, 1) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">إيجابي / سلبي</span>
                        <span class="text-sm font-medium">
                            <span class="text-green-600">{{ $category['positiveCount'] ?? 0 }}</span>
                            /
                            <span class="text-red-600">{{ $category['negativeCount'] ?? 0 }}</span>
                        </span>
                    </div>

                    {{-- Toggle Details --}}
                    <button
                        wire:click="toggleCategory('{{ $category['category'] }}')"
                        class="w-full flex items-center justify-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        <span>{{ $expandedCategory === $category['category'] ? 'إخفاء التفاصيل' : 'عرض التفاصيل' }}</span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform {{ $expandedCategory === $category['category'] ? 'rotate-180' : '' }}" />
                    </button>
                </div>

                {{-- Expanded Details --}}
                @if($expandedCategory === $category['category'])
                    <div class="border-t border-gray-200 dark:border-gray-700 p-6 space-y-4">
                        {{-- Positive Quotes --}}
                        @if(!empty($category['topPositives']))
                            <div>
                                <h4 class="text-sm font-medium text-green-600 dark:text-green-400 mb-2">أبرز التعليقات الإيجابية</h4>
                                <div class="space-y-2">
                                    @foreach($category['topPositives'] as $quote)
                                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Negative Quotes --}}
                        @if(!empty($category['topNegatives']))
                            <div>
                                <h4 class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">أبرز التعليقات السلبية</h4>
                                <div class="space-y-2">
                                    @foreach($category['topNegatives'] as $quote)
                                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- No Data State --}}
    @if(empty($data['categories']))
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
            <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات ديموغرافية</h3>
            <p class="text-gray-500 dark:text-gray-400">سيتم تحليل البيانات الديموغرافية بعد تحليل المراجعات</p>
        </div>
    @endif
</div>
