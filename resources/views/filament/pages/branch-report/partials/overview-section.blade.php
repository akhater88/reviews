@php
    $overviewCards = $this->getOverviewCards();
    $ratingsCard = collect($overviewCards)->firstWhere('type', 'ratings_reviews');
    $sentimentCard = collect($overviewCards)->firstWhere('type', 'general_sentiment');
    $categoryCard = collect($overviewCards)->firstWhere('type', 'category_analysis');
@endphp

{{-- Section Header --}}
<div class="rounded-xl shadow-sm border border-blue-100 dark:border-blue-800" style="background: linear-gradient(to right, rgb(239 246 255), rgb(238 242 255));">
    <div class="px-5 py-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">نظرة عامة</h2>
    </div>
</div>

{{-- Content Card --}}
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl">
    <div class="p-5 sm:p-6 space-y-8">

        {{-- 1. Ratings & Reviews Cards --}}
        @if($ratingsCard)
            @include('filament.pages.branch-report.partials.ratings-reviews-card', ['card' => $ratingsCard])
        @else
            {{-- Fallback: Show branch basic stats if no card data --}}
            <div class="space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">التقييمات والمراجعات</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Total Reviews --}}
                    <div class="rounded-2xl p-5 sm:p-6 border border-indigo-100 dark:border-indigo-800" style="background: linear-gradient(to bottom right, rgb(238 242 255), rgba(199 210 254, 0.5));">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(99 102 241);">
                                <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-3xl sm:text-4xl font-bold text-indigo-900 dark:text-indigo-100">
                                {{ number_format($this->branch->total_reviews ?? 0) }}
                            </div>
                            <div class="text-sm font-medium text-indigo-700 dark:text-indigo-300">إجمالي المراجعات</div>
                        </div>
                    </div>
                    {{-- Average Rating --}}
                    <div class="rounded-2xl p-5 sm:p-6 border border-blue-100 dark:border-blue-800" style="background: linear-gradient(to bottom right, rgb(239 246 255), rgba(191 219 254, 0.5));">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(59 130 246);">
                                <x-heroicon-s-star class="h-5 w-5 text-white" />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-3xl sm:text-4xl font-bold text-blue-900 dark:text-blue-100">
                                {{ number_format($this->branch->current_rating ?? 0, 1) }}/5
                            </div>
                            <div class="text-sm font-medium text-blue-700 dark:text-blue-300">متوسط التقييم</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. Sentiment Distribution --}}
        @if($sentimentCard)
            @include('filament.pages.branch-report.partials.sentiment-card', ['card' => $sentimentCard])
        @endif

        {{-- 3. Category Analysis --}}
        @if($categoryCard)
            @include('filament.pages.branch-report.partials.category-analysis-card', ['card' => $categoryCard])
        @endif

    </div>
</div>
