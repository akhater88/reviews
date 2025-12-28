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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Total Reviews Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgb(99 102 241);">
                        <x-heroicon-o-chat-bubble-left-right class="h-6 w-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المراجعات</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">من Google Places</div>
                    </div>
                </div>
                <div class="text-4xl font-bold" style="color: rgb(99 102 241);">
                    {{ $formattedTotalReviews }}
                </div>
            </div>
        </div>

        {{-- Average Rating Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgb(59 130 246);">
                        <x-heroicon-s-star class="h-6 w-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">متوسط التقييم الكلّي</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">كل الفترة</div>
                    </div>
                    @if($averageRating > 0)
                        <span class="text-xs px-3 py-1.5 rounded-full font-medium" style="background-color: rgb(239 246 255); color: rgb(59 130 246);">
                            {{ $ratingLabel }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-4xl font-bold" style="color: rgb(59 130 246);">
                        @if($averageRating > 0)
                            {{ number_format($averageRating, 1) }}/5
                        @else
                            --
                        @endif
                    </div>
                    @if($averageRating > 0)
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="h-5 w-5 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" />
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3-Month Reviews Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgb(34 197 94);">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">عدد المراجعات</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">من التحليل الذكي</div>
                    </div>
                    <span class="text-xs px-3 py-1.5 rounded-full font-medium" style="background-color: rgb(240 253 244); color: rgb(34 197 94);">
                        آخر 3 شهور
                    </span>
                </div>
                <div class="text-4xl font-bold" style="color: rgb(34 197 94);">
                    {{ number_format($analysisReviewCount) }}
                </div>
            </div>
        </div>

        {{-- 3-Month AI Rating Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgb(34 197 94);">
                        <x-heroicon-s-star class="h-6 w-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">متوسط التحليل الذكي</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">من الذكاء الاصطناعي</div>
                    </div>
                    <span class="text-xs px-3 py-1.5 rounded-full font-medium" style="background-color: rgb(240 253 244); color: rgb(34 197 94);">
                        آخر 3 شهور
                    </span>
                </div>
                <div class="text-4xl font-bold" style="color: rgb(34 197 94);">
                    @if($aiRating > 0)
                        {{ number_format($aiRating, 1) }}/5
                    @else
                        {{ number_format($averageRating, 1) }}/5
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
