@php
    $data = $card['data'] ?? [];
    $categories = $data['categories'] ?? $data['dynamicCategories'] ?? $data['organicCategories'] ?? [];
    $categoriesArray = is_array($categories) ? array_values(array_filter($categories, fn($c) => isset($c['name']))) : [];

    // Use provided best/worst or calculate from array
    $bestCategory = $data['bestCategory'] ?? (!empty($categoriesArray) ? collect($categoriesArray)->sortByDesc('rating')->first() : null);
    $worstCategory = $data['worstCategory'] ?? (!empty($categoriesArray) ? collect($categoriesArray)->sortBy('rating')->first() : null);
@endphp

@if(!empty($categoriesArray))
<div class="space-y-6">
    {{-- Sub-section Header --}}
    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">تحليل الفئات</h3>
    </div>

    {{-- Categories List --}}
    @foreach($categoriesArray as $index => $category)
        @php
            $categoryName = $category['name'] ?? 'فئة غير محددة';
            $rating = $category['rating'] ?? 0;
            $mentions = $category['totalMentions'] ?? $category['mentions'] ?? 0;
            $positiveCount = $category['positiveCount'] ?? 0;
            $negativeCount = $category['negativeCount'] ?? 0;
            $positiveQuotes = $category['positiveExamples'] ?? $category['topPhrasesPositive'] ?? [];
            $negativeQuotes = $category['negativeExamples'] ?? $category['topPhrasesNegative'] ?? [];

            $starColor = match(true) {
                $rating >= 4.0 => 'text-green-500',
                $rating >= 3.0 => 'text-yellow-500',
                default => 'text-red-500',
            };
        @endphp

        <div class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-5">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="text-right">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $categoryName }}</h4>
                </div>
                <div class="flex items-center gap-3">
                    @if($mentions > 0)
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $mentions }} إشارة</div>
                    @endif
                    @if($rating > 0)
                        <div class="text-xl font-bold {{ $starColor }}">
                            {{ number_format($rating, 1) }} <span class="text-amber-400">★</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Feedback Count Split --}}
            @if($positiveCount > 0 || $negativeCount > 0)
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $positiveCount }}</div>
                        <div class="text-sm text-green-700 dark:text-green-300">تعليقات إيجابية</div>
                    </div>
                    <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $negativeCount }}</div>
                        <div class="text-sm text-red-700 dark:text-red-300">تعليقات سلبية</div>
                    </div>
                </div>
            @endif

            {{-- Quote Examples --}}
            <div class="space-y-3">
                @if(!empty($positiveQuotes) && count($positiveQuotes) > 0)
                    <div class="bg-green-50 dark:bg-green-900/20 border-r-4 border-green-400 dark:border-green-600 p-3 rounded">
                        <div class="text-xs text-green-600 dark:text-green-400 font-medium mb-1">مثال إيجابي:</div>
                        <p class="text-sm text-green-800 dark:text-green-200">"{{ is_array($positiveQuotes) ? $positiveQuotes[0] : $positiveQuotes }}"</p>
                    </div>
                @endif

                @if(!empty($negativeQuotes) && count($negativeQuotes) > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border-r-4 border-red-400 dark:border-red-600 p-3 rounded">
                        <div class="text-xs text-red-600 dark:text-red-400 font-medium mb-1">مثال سلبي:</div>
                        <p class="text-sm text-red-800 dark:text-red-200">"{{ is_array($negativeQuotes) ? $negativeQuotes[0] : $negativeQuotes }}"</p>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    {{-- Best & Worst Summary --}}
    @if($bestCategory && $worstCategory)
        @php
            $bestName = $bestCategory['name'] ?? '';
            $worstName = $worstCategory['name'] ?? '';
        @endphp
        @if($bestName && $worstName && $bestName !== $worstName)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Best Category --}}
                <div class="bg-gradient-to-br from-green-50 to-green-100/50 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-s-trophy class="w-5 h-5 text-green-600 dark:text-green-400" />
                        <span class="text-sm font-medium text-green-700 dark:text-green-300">الفئة الأفضل أداءً</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $bestName }}</div>
                    @if(!empty($bestCategory['rating']))
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($bestCategory['rating'], 1) }} <span class="text-amber-400">★</span></div>
                    @endif
                    @if(!empty($bestCategory['reason']))
                        <p class="text-sm text-green-700 dark:text-green-300 mt-2">{{ $bestCategory['reason'] }}</p>
                    @endif
                </div>

                {{-- Worst Category --}}
                <div class="bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-4 border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400" />
                        <span class="text-sm font-medium text-red-700 dark:text-red-300">الفئة التي تحتاج تحسين</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $worstName }}</div>
                    @if(!empty($worstCategory['rating']))
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($worstCategory['rating'], 1) }} <span class="text-amber-400">★</span></div>
                    @endif
                    @if(!empty($worstCategory['reason']))
                        <p class="text-sm text-red-700 dark:text-red-300 mt-2">{{ $worstCategory['reason'] }}</p>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endif
