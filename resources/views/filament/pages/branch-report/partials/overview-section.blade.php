@php
    $overviewCards = $this->getOverviewCards();
    $ratingsCard = collect($overviewCards)->firstWhere('type', 'ratings_reviews');
    $sentimentCard = collect($overviewCards)->firstWhere('type', 'general_sentiment');
@endphp

{{-- Content Card --}}
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl">
    <div class="p-6">

        {{-- 1. Ratings & Reviews Cards --}}
        @if($ratingsCard)
            <div style="margin-bottom: 2.5rem;">
                @include('filament.pages.branch-report.partials.ratings-reviews-card', ['card' => $ratingsCard])
            </div>

            {{-- 2. Timeline Trend Chart --}}
            <div style="margin-bottom: 2.5rem;">
                @include('filament.pages.branch-report.partials.timeline-trend-chart', ['card' => $ratingsCard])
            </div>
        @else
            {{-- Fallback: Show branch basic stats if no card data --}}
            <div class="space-y-6" style="margin-bottom: 2.5rem;">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">التقييمات والمراجعات</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Total Reviews --}}
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
                                {{ number_format($this->branch->total_reviews ?? 0) }}
                            </div>
                        </div>
                    </div>
                    {{-- Average Rating --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgb(59 130 246);">
                                    <x-heroicon-s-star class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">متوسط التقييم</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">كل الفترة</div>
                                </div>
                            </div>
                            <div class="text-4xl font-bold" style="color: rgb(59 130 246);">
                                {{ number_format($this->branch->current_rating ?? 0, 1) }}/5
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Sentiment Distribution --}}
        @if($sentimentCard)
            <div>
                @include('filament.pages.branch-report.partials.sentiment-card', ['card' => $sentimentCard])
            </div>
        @endif

    </div>
</div>
