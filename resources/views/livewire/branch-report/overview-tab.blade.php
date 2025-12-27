<div class="space-y-6" dir="rtl">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Reviews --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">إجمالي المراجعات</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ number_format($data[0]['data']['totalReviews'] ?? $branch->reviews()->count()) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-500 dark:text-gray-400">
                    @php
                        $reviewsWithText = $data[0]['data']['reviewsWithText'] ?? $branch->reviews()->whereNotNull('text')->where('text', '!=', '')->count();
                        $starOnlyReviews = $data[0]['data']['starOnlyReviews'] ?? $branch->reviews()->where(function($q) { $q->whereNull('text')->orWhere('text', ''); })->count();
                    @endphp
                    {{ $reviewsWithText }} مع نص،
                    {{ $starOnlyReviews }} نجوم فقط
                </span>
            </div>
        </div>

        {{-- Average Rating --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">متوسط التقييم</p>
                    @php
                        $avgRating = $branch->reviews()->avg('rating') ?? 0;
                    @endphp
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($avgRating, 1) }}
                        </p>
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-star class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
            </div>
        </div>

        {{-- Sentiment Score --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">المشاعر العامة</p>
                    @php
                        $overallSentiment = $sentiment['overallSentiment'] ?? 'neutral';
                        $sentimentLabel = match($overallSentiment) {
                            'positive' => 'إيجابي',
                            'negative' => 'سلبي',
                            default => 'محايد',
                        };
                        $sentimentColor = match($overallSentiment) {
                            'positive' => 'text-green-600 bg-green-100 dark:bg-green-900/30',
                            'negative' => 'text-red-600 bg-red-100 dark:bg-red-900/30',
                            default => 'text-gray-600 bg-gray-100 dark:bg-gray-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2 {{ $sentimentColor }}">
                        {{ $sentimentLabel }}
                    </span>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-face-smile class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        {{-- Response Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">معدل الاستجابة</p>
                    @php
                        $totalReviews = $branch->reviews()->count();
                        $repliedReviews = $branch->reviews()->whereNotNull('owner_reply')->count();
                        $responseRate = $totalReviews > 0 ? round(($repliedReviews / $totalReviews) * 100) : 0;
                    @endphp
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ $responseRate }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $responseRate }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sentiment Distribution --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Sentiment Pie --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">توزيع المشاعر</h3>

            @php
                $distribution = $sentiment['sentimentDistribution'] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0];
            @endphp

            <div class="space-y-4">
                {{-- Positive --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">إيجابي</span>
                        <span class="font-medium text-green-600">{{ number_format($distribution['positive'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full transition-all" style="width: {{ $distribution['positive'] ?? 0 }}%"></div>
                    </div>
                </div>

                {{-- Neutral --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">محايد</span>
                        <span class="font-medium text-gray-600">{{ number_format($distribution['neutral'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-gray-500 h-3 rounded-full transition-all" style="width: {{ $distribution['neutral'] ?? 0 }}%"></div>
                    </div>
                </div>

                {{-- Negative --}}
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">سلبي</span>
                        <span class="font-medium text-red-600">{{ number_format($distribution['negative'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full transition-all" style="width: {{ $distribution['negative'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Insights --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">الرؤى الرئيسية</h3>

            @if(!empty($sentiment['keyInsights']))
                <ul class="space-y-3">
                    @foreach($sentiment['keyInsights'] as $insight)
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <x-heroicon-o-light-bulb class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                            </div>
                            <span class="text-gray-700 dark:text-gray-300">{{ $insight }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 dark:text-gray-400">لا توجد رؤى متاحة</p>
            @endif
        </div>
    </div>

    {{-- Customer Quotes --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">اقتباسات العملاء</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Positive Quotes --}}
            <div>
                <h4 class="text-sm font-medium text-green-600 dark:text-green-400 mb-3 flex items-center gap-2">
                    <x-heroicon-o-face-smile class="w-5 h-5" />
                    تعليقات إيجابية
                </h4>
                <div class="space-y-3">
                    @forelse(($sentiment['customerQuotes']['positive'] ?? []) as $quote)
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border-r-4 border-green-500">
                            <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد تعليقات</p>
                    @endforelse
                </div>
            </div>

            {{-- Neutral Quotes --}}
            <div>
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3 flex items-center gap-2">
                    <x-heroicon-o-minus-circle class="w-5 h-5" />
                    تعليقات محايدة
                </h4>
                <div class="space-y-3">
                    @forelse(($sentiment['customerQuotes']['neutral'] ?? []) as $quote)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border-r-4 border-gray-400">
                            <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد تعليقات</p>
                    @endforelse
                </div>
            </div>

            {{-- Negative Quotes --}}
            <div>
                <h4 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3 flex items-center gap-2">
                    <x-heroicon-o-face-frown class="w-5 h-5" />
                    تعليقات سلبية
                </h4>
                <div class="space-y-3">
                    @forelse(($sentiment['customerQuotes']['negative'] ?? []) as $quote)
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border-r-4 border-red-500">
                            <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $quote }}"</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد تعليقات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
