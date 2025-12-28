@php
    $overviewCards = $this->getOverviewCards();
    $ratingsCard = collect($overviewCards)->firstWhere('type', 'ratings_reviews');
    $sentimentCard = collect($overviewCards)->firstWhere('type', 'general_sentiment');
    $categoryCard = collect($overviewCards)->firstWhere('type', 'category_analysis');
@endphp

{{-- Section Header --}}
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800 rounded-xl shadow-sm">
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
