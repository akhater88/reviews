@php
    $data = $card['data'] ?? [];

    // Get total reviews from card data or branch
    $totalReviews = $data['totalReviews'] ?? $this->branch->total_reviews ?? 0;
    $reviewsWithText = $data['reviewsWithText'] ?? 0;

    // Get rating from branch model (card data doesn't have it)
    $averageRating = $this->branch->current_rating ?? 0;

    // For AI analysis period, use the analysis data
    $analysisReviewCount = $reviewsWithText ?: $totalReviews;
    $aiRating = $data['aiAverageRating'] ?? $averageRating;

    // Format large numbers
    $formattedTotalReviews = $totalReviews >= 1000
        ? number_format($totalReviews / 1000, 1) . 'ألف'
        : number_format($totalReviews);

    $ratingLabel = match(true) {
        $averageRating >= 4.5 => 'ممتاز',
        $averageRating >= 4.0 => 'جيد جداً',
        $averageRating >= 3.5 => 'جيد',
        $averageRating >= 3.0 => 'متوسط',
        default => 'ضعيف',
    };
@endphp

<div class="space-y-6">
    {{-- Sub-section Header --}}
    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">التقييمات والمراجعات</h3>
    </div>

    {{-- Main Metrics Grid - 4 Cards in 2x2 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        {{-- Total Reviews Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(99 102 241);">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $formattedTotalReviews }}
                </div>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">إجمالي المراجعات</div>
                <div class="text-xs text-gray-500 dark:text-gray-500">من Google Places</div>
            </div>
        </div>

        {{-- Average Rating Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(59 130 246);">
                    <x-heroicon-s-star class="h-5 w-5 text-white" />
                </div>
                @if($averageRating > 0)
                    <span class="text-xs px-3 py-1 rounded-full font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        {{ $ratingLabel }}
                    </span>
                @endif
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-blue-600 dark:text-blue-400">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}/5
                    @else
                        --
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">متوسط التقييم الكلّي (كل الفترة)</div>
                @if($averageRating > 0)
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="h-4 w-4 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" />
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        {{-- 3-Month Reviews Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                    آخر 3 شهور
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($analysisReviewCount) }}
                </div>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">عدد المراجعات</div>
                <div class="text-xs text-gray-500 dark:text-gray-500">من التحليل الذكي</div>
            </div>
        </div>

        {{-- 3-Month AI Rating Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                    <x-heroicon-s-star class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-3 py-1 rounded-full font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                    آخر 3 شهور
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-green-600 dark:text-green-400">
                    @if($aiRating > 0)
                        {{ number_format($aiRating, 1) }}/5
                    @else
                        {{ number_format($averageRating, 1) }}/5
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">متوسط التحليل الذكي</div>
                <div class="text-xs text-gray-500 dark:text-gray-500">من الذكاء الاصطناعي</div>
            </div>
        </div>
    </div>
</div>
