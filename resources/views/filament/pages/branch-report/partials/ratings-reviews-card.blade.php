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

        {{-- Average Rating Card (Top Left) - Light Green Background --}}
        <div class="rounded-2xl p-6 relative" style="background: linear-gradient(135deg, rgb(236 253 245), rgb(220 252 231));">
            {{-- Badge Top Left --}}
            <span class="absolute top-4 right-4 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(0,0,0,0.08); color: rgb(55 65 81);">
                {{ $ratingLabel }}
            </span>

            {{-- Icon Top Right --}}
            <div class="absolute top-4 left-4 w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(59 130 246);">
                <x-heroicon-s-star class="h-6 w-6 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-14 pb-4">
                <div class="text-5xl font-bold mb-3" style="color: rgb(17 24 39);">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}/5
                    @else
                        --
                    @endif
                </div>
                <div class="text-base font-medium mb-1" style="color: rgb(55 65 81);">
                    متوسط التقييم الكلّي (كل الفترة)
                </div>
                @if($averageRating > 0)
                    <div class="flex items-center justify-center gap-0.5 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="h-5 w-5 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-300' }}" />
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        {{-- Total Reviews Card (Top Right) - White Background --}}
        <div class="rounded-2xl p-6 relative bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            {{-- Icon Top Right --}}
            <div class="absolute top-4 left-4 w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(139 92 246);">
                <x-heroicon-o-chat-bubble-left-right class="h-6 w-6 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-14 pb-4">
                <div class="text-5xl font-bold mb-3" style="color: rgb(17 24 39);">
                    {{ $formattedTotalReviews }}
                </div>
                <div class="text-base font-medium mb-1" style="color: rgb(55 65 81);">
                    إجمالي المراجعات
                </div>
                <div class="text-sm" style="color: rgb(107 114 128);">
                    من Google Places
                </div>
            </div>
        </div>

        {{-- AI Rating Card (Bottom Left) - Light Mint Background --}}
        <div class="rounded-2xl p-6 relative" style="background: linear-gradient(135deg, rgb(236 253 245), rgb(209 250 229));">
            {{-- Badge Top Left --}}
            <span class="absolute top-4 right-4 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(0,0,0,0.08); color: rgb(22 101 52);">
                آخر 3 شهور
            </span>

            {{-- Icon Top Right --}}
            <div class="absolute top-4 left-4 w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(16 185 129);">
                <x-heroicon-s-star class="h-6 w-6 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-14 pb-4">
                <div class="text-5xl font-bold mb-3" style="color: rgb(17 24 39);">
                    @if($aiRating > 0)
                        {{ number_format($aiRating, 1) }}/5
                    @else
                        {{ number_format($averageRating, 1) }}/5
                    @endif
                </div>
                <div class="text-base font-medium mb-1" style="color: rgb(55 65 81);">
                    متوسط التحليل الذكي
                </div>
                <div class="text-sm" style="color: rgb(107 114 128);">
                    من الذكاء الاصطناعي
                </div>
            </div>
        </div>

        {{-- 3-Month Reviews Card (Bottom Right) - White/Light Purple Background --}}
        <div class="rounded-2xl p-6 relative bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            {{-- Badge Top Left --}}
            <span class="absolute top-4 right-4 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(139, 92, 246, 0.1); color: rgb(109 40 217);">
                آخر 3 شهور
            </span>

            {{-- Icon Top Right --}}
            <div class="absolute top-4 left-4 w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(139 92 246);">
                <x-heroicon-o-check-circle class="h-6 w-6 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-14 pb-4">
                <div class="text-5xl font-bold mb-3" style="color: rgb(17 24 39);">
                    {{ number_format($analysisReviewCount) }}
                </div>
                <div class="text-base font-medium mb-1" style="color: rgb(55 65 81);">
                    عدد المراجعات
                </div>
                <div class="text-sm" style="color: rgb(107 114 128);">
                    من التحليل الذكي
                </div>
            </div>
        </div>

    </div>
</div>
