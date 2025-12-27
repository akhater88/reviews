<div class="space-y-6" dir="rtl">
    {{-- Keywords Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الكلمات المفتاحية</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">العبارات الأكثر تكراراً في المراجعات</p>
        </div>

        <div class="p-6">
            @if(!empty($data['keywordGroups']))
                <div class="flex flex-wrap gap-3">
                    @foreach($data['keywordGroups'] as $keyword)
                        @php
                            $bgColor = match($keyword['sentiment'] ?? 'neutral') {
                                'positive' => 'bg-green-100 dark:bg-green-900/30 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200',
                                'negative' => 'bg-red-100 dark:bg-red-900/30 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200',
                                default => 'bg-gray-100 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200',
                            };
                        @endphp
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $bgColor }}">
                            <span class="font-medium">{{ $keyword['mainKeyword'] }}</span>
                            <span class="text-xs opacity-75">{{ $keyword['frequency'] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">لا توجد كلمات مفتاحية</p>
            @endif
        </div>
    </div>

    {{-- Food Items Grid --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">العناصر الغذائية</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">الأطباق والمشروبات المذكورة في المراجعات</p>
        </div>

        <div class="p-6">
            @if(!empty($data['foodItems']))
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($data['foodItems'] as $item)
                        @php
                            $sentimentColor = match($item['sentiment'] ?? 'neutral') {
                                'positive' => 'border-green-500',
                                'negative' => 'border-red-500',
                                'mixed' => 'border-yellow-500',
                                default => 'border-gray-300 dark:border-gray-600',
                            };
                            $sentimentBg = match($item['sentiment'] ?? 'neutral') {
                                'positive' => 'bg-green-50 dark:bg-green-900/20',
                                'negative' => 'bg-red-50 dark:bg-red-900/20',
                                'mixed' => 'bg-yellow-50 dark:bg-yellow-900/20',
                                default => 'bg-gray-50 dark:bg-gray-700/50',
                            };
                        @endphp
                        <div class="rounded-xl border-2 {{ $sentimentColor }} {{ $sentimentBg }} p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $item['name'] }}</h4>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ $item['mentions'] ?? 0 }} ذكر</span>
                                <div class="flex items-center gap-1">
                                    <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($item['averageRating'] ?? 0, 1) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">لا توجد عناصر غذائية مذكورة</p>
            @endif
        </div>
    </div>

    {{-- Aspects Analysis --}}
    @if(!empty($data['aspects']))
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تحليل الجوانب</h3>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($data['aspects'] as $aspect)
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $aspect['name'] }}</h4>
                            <span class="text-sm text-gray-500">ثقة: {{ number_format(($aspect['confidence'] ?? 0) * 100) }}%</span>
                        </div>

                        <div class="flex items-center gap-4 mb-4">
                            @php
                                $total = ($aspect['positiveCount'] ?? 0) + ($aspect['negativeCount'] ?? 0) + ($aspect['neutralCount'] ?? 0);
                                $positiveWidth = $total > 0 ? (($aspect['positiveCount'] ?? 0) / $total) * 100 : 0;
                                $negativeWidth = $total > 0 ? (($aspect['negativeCount'] ?? 0) / $total) * 100 : 0;
                            @endphp
                            <div class="flex-1">
                                <div class="flex h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                                    <div class="bg-green-500" style="width: {{ $positiveWidth }}%"></div>
                                    <div class="bg-red-500" style="width: {{ $negativeWidth }}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-green-600">{{ $aspect['positiveCount'] ?? 0 }} إيجابي</span>
                                <span class="text-red-600">{{ $aspect['negativeCount'] ?? 0 }} سلبي</span>
                            </div>
                        </div>

                        @if(!empty($aspect['topPhrasesPositive']) || !empty($aspect['topPhrasesNegative']))
                            <div class="grid grid-cols-2 gap-4">
                                @if(!empty($aspect['topPhrasesPositive']))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_slice($aspect['topPhrasesPositive'], 0, 3) as $phrase)
                                            <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded">{{ $phrase }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($aspect['topPhrasesNegative']))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_slice($aspect['topPhrasesNegative'], 0, 3) as $phrase)
                                            <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-1 rounded">{{ $phrase }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
