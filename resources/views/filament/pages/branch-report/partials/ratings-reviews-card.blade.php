@php
    $data = $card['data'] ?? [];
    $totalReviews = $data['totalReviews'] ?? 0;
    $averageRating = $data['averageRating'] ?? 0;
    $threeMonthCount = $data['threeMonthReviewCount'] ?? 0;
    $threeMonthRating = $data['threeMonthAverageRating'] ?? $data['threeMonthRating'] ?? 0;

    // Dynamic rating color
    $ratingColors = match(true) {
        $averageRating >= 4.5 => ['bg' => 'from-green-50 to-green-100/50 dark:from-green-900/20 dark:to-green-800/20', 'border' => 'border-green-100 dark:border-green-800', 'text' => 'text-green-900 dark:text-green-100', 'icon' => 'bg-green-500'],
        $averageRating >= 4.0 => ['bg' => 'from-blue-50 to-blue-100/50 dark:from-blue-900/20 dark:to-blue-800/20', 'border' => 'border-blue-100 dark:border-blue-800', 'text' => 'text-blue-900 dark:text-blue-100', 'icon' => 'bg-blue-500'],
        $averageRating >= 3.5 => ['bg' => 'from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/20', 'border' => 'border-amber-100 dark:border-amber-800', 'text' => 'text-amber-900 dark:text-amber-100', 'icon' => 'bg-amber-500'],
        default => ['bg' => 'from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/20', 'border' => 'border-red-100 dark:border-red-800', 'text' => 'text-red-900 dark:text-red-100', 'icon' => 'bg-red-500'],
    };

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

    {{-- Main Metrics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        {{-- Total Reviews Card --}}
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-2xl p-5 sm:p-6 border border-indigo-100 dark:border-indigo-800">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-indigo-900 dark:text-indigo-100">
                    {{ number_format($totalReviews) }}
                </div>
                <div class="text-sm font-medium text-indigo-700 dark:text-indigo-300">إجمالي المراجعات</div>
                <div class="text-xs text-indigo-600 dark:text-indigo-400">من Google Places</div>
            </div>
        </div>

        {{-- Average Rating Card --}}
        <div class="bg-gradient-to-br {{ $ratingColors['bg'] }} rounded-2xl p-5 sm:p-6 border {{ $ratingColors['border'] }}">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 {{ $ratingColors['icon'] }} rounded-xl flex items-center justify-center">
                    <x-heroicon-s-star class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-white/70 dark:bg-gray-800/70 {{ $ratingColors['text'] }} font-medium">
                    {{ $ratingLabel }}
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold {{ $ratingColors['text'] }}">
                    {{ number_format($averageRating, 1) }}/5
                </div>
                <div class="text-sm font-medium {{ str_replace('900', '700', str_replace('100', '300', $ratingColors['text'])) }}">متوسط التقييم الكلّي</div>
                <div class="flex items-center gap-1 mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        <x-heroicon-s-star class="h-4 w-4 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" />
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- AI Analysis Metrics (Last 3 Months) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        {{-- 3-Month Review Count --}}
        <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl p-5 sm:p-6 border border-purple-100 dark:border-purple-800">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-white/70 dark:bg-gray-800/70 text-purple-600 dark:text-purple-300 font-medium">
                    آخر 3 شهور
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-purple-900 dark:text-purple-100">
                    {{ number_format($threeMonthCount) }}
                </div>
                <div class="text-sm font-medium text-purple-700 dark:text-purple-300">عدد المراجعات</div>
                <div class="text-xs text-purple-600 dark:text-purple-400">من التحليل الذكي</div>
            </div>
        </div>

        {{-- 3-Month Average Rating --}}
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-2xl p-5 sm:p-6 border border-emerald-100 dark:border-emerald-800">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <x-heroicon-s-star class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-white/70 dark:bg-gray-800/70 text-emerald-600 dark:text-emerald-300 font-medium">
                    آخر 3 شهور
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-emerald-900 dark:text-emerald-100">
                    {{ number_format($threeMonthRating, 1) }}/5
                </div>
                <div class="text-sm font-medium text-emerald-700 dark:text-emerald-300">متوسط التحليل الذكي</div>
                <div class="text-xs text-emerald-600 dark:text-emerald-400">من الذكاء الاصطناعي</div>
            </div>
        </div>
    </div>
</div>
