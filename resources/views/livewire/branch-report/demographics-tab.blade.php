<div class="space-y-6" dir="rtl">
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm border border-pink-100 dark:border-pink-800" style="background: linear-gradient(to right, rgb(253 242 248), rgb(252 231 243));">
        <div class="px-5 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">البيانات الديموغرافية</h2>
            @if(!empty($data['summary']['totalAnalyzed']))
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-600">
                    {{ number_format($data['summary']['totalAnalyzed']) }} مراجعة إجمالية
                </span>
            @endif
        </div>
    </div>

    {{-- Gender Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(($data['categories'] ?? []) as $category)
            @php
                $genderName = $category['category'] ?? '';
                $percentage = $category['percentage'] ?? 0;
                $totalReviews = $category['totalReviews'] ?? 0;
                $avgRating = $category['averageRating'] ?? 0;
                $positiveCount = $category['positiveCount'] ?? 0;
                $negativeCount = $category['negativeCount'] ?? 0;
                $totalAnalyzed = $data['summary']['totalAnalyzed'] ?? 300;

                // Colors based on gender
                $colors = match($genderName) {
                    'ذكور' => [
                        'border' => 'border-blue-200 dark:border-blue-700',
                        'percentBg' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                        'progressBg' => 'bg-blue-500',
                        'headerBg' => 'bg-blue-50 dark:bg-blue-900/20',
                    ],
                    'إناث' => [
                        'border' => 'border-pink-200 dark:border-pink-700',
                        'percentBg' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/50 dark:text-pink-300',
                        'progressBg' => 'bg-pink-500',
                        'headerBg' => 'bg-pink-50 dark:bg-pink-900/20',
                    ],
                    default => [
                        'border' => 'border-gray-200 dark:border-gray-700',
                        'percentBg' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        'progressBg' => 'bg-gray-400',
                        'headerBg' => 'bg-gray-50 dark:bg-gray-700/50',
                    ],
                };

                // Progress bar width (percentage of total)
                $progressWidth = min(100, max(0, $percentage));
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $colors['border'] }} overflow-hidden shadow-sm {{ $expandedCategory === $genderName ? 'ring-2 ring-blue-500 dark:ring-blue-400' : '' }}">
                {{-- Card Header --}}
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $genderName }}</h3>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ $colors['percentBg'] }}">
                            {{ number_format($percentage, 2) }}%
                        </span>
                    </div>

                    {{-- Main Stats --}}
                    <div class="text-center mb-4">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($totalReviews) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">من أصل {{ number_format($totalAnalyzed) }} مراجعة</div>
                    </div>

                    {{-- Rating --}}
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($avgRating, 1) }}</span>
                        <x-heroicon-s-star class="w-5 h-5 text-yellow-400" />
                    </div>

                    {{-- Progress Bar --}}
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                        <div class="h-full {{ $colors['progressBg'] }} rounded-full" style="width: {{ $progressWidth }}%"></div>
                    </div>

                    {{-- Expand Button --}}
                    <button
                        wire:click="toggleCategory('{{ $genderName }}')"
                        class="w-full text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        {{ $expandedCategory === $genderName ? 'إخفاء التفاصيل' : 'اضغط لعرض التفاصيل' }}
                    </button>
                </div>

                {{-- Expanded Details --}}
                @if($expandedCategory === $genderName)
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-5">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">تفاصيل فئة: {{ $genderName }}</h4>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                            {{-- Positive Highlights --}}
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-2 mb-3">
                                    <x-heroicon-o-hand-thumb-up class="w-5 h-5 text-green-500" />
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">أبرز الإيجابيات</span>
                                </div>
                                @if(!empty($category['topPositives']))
                                    <div class="space-y-2">
                                        @foreach(array_slice($category['topPositives'], 0, 3) as $quote)
                                            <div class="flex items-start gap-2">
                                                <span class="w-2 h-2 mt-2 bg-green-500 rounded-full flex-shrink-0"></span>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">لا توجد تعليقات إيجابية</p>
                                @endif
                            </div>

                            {{-- Negative Highlights --}}
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-2 mb-3">
                                    <x-heroicon-o-hand-thumb-down class="w-5 h-5 text-red-500" />
                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400">أبرز السلبيات</span>
                                </div>
                                @if(!empty($category['topNegatives']))
                                    <div class="space-y-2">
                                        @foreach(array_slice($category['topNegatives'], 0, 3) as $quote)
                                            <div class="flex items-start gap-2">
                                                <span class="w-2 h-2 mt-2 bg-red-500 rounded-full flex-shrink-0"></span>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">لا توجد تعليقات سلبية</p>
                                @endif
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div class="grid grid-cols-3 gap-3 bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                            <div class="text-center">
                                <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($positiveCount) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">إيجابيات</div>
                            </div>
                            <div class="text-center border-x border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($avgRating, 1) }}</span>
                                    <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">التقييم</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($percentage, 2) }}%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">النسبة</div>
                            </div>
                        </div>
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
