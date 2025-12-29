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

        {{-- Average Rating Card (Top Right in RTL) - Light Green Background --}}
        <div class="rounded-2xl p-6 relative" style="background: linear-gradient(135deg, rgb(236 253 245), rgb(220 252 231));">
            {{-- Badge Top Left --}}
            <span class="absolute top-4 left-4 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgba(0,0,0,0.08); color: rgb(55 65 81);">
                {{ $ratingLabel }}
            </span>

            {{-- Icon Top Right --}}
            <div class="absolute top-4 right-4 w-11 h-11 rounded-xl flex items-center justify-center" style="background: rgb(59 130 246);">
                <x-heroicon-s-star class="h-5 w-5 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-12 pb-2">
                <div class="text-4xl font-bold mb-2" style="color: rgb(17 24 39);">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}/5
                    @else
                        --
                    @endif
                </div>
                <div class="text-sm font-medium mb-1" style="color: rgb(55 65 81);">
                    متوسط التقييم الكلّي (كل الفترة)
                </div>
                @if($averageRating > 0)
                    <div class="flex items-center justify-center gap-0.5 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="h-5 w-5 {{ $i <= round($averageRating) ? 'text-gray-800' : 'text-gray-400' }}" />
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        {{-- Total Reviews Card (Top Left in RTL) - White Background --}}
        <div class="rounded-2xl p-6 relative bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            {{-- Icon Top Right --}}
            <div class="absolute top-4 right-4 w-11 h-11 rounded-xl flex items-center justify-center" style="background: rgb(139 92 246);">
                <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-12 pb-2">
                <div class="text-4xl font-bold mb-2" style="color: rgb(17 24 39);">
                    {{ $formattedTotalReviews }}
                </div>
                <div class="text-sm font-medium mb-1" style="color: rgb(55 65 81);">
                    إجمالي المراجعات
                </div>
                <div class="text-xs" style="color: rgb(107 114 128);">
                    من Google Places
                </div>
            </div>
        </div>

        {{-- AI Rating Card (Bottom Right in RTL) - Light Mint Background --}}
        <div class="rounded-2xl p-6 relative" style="background: linear-gradient(135deg, rgb(236 253 245), rgb(209 250 229));">
            {{-- Badge with Icon Top Left --}}
            <div class="absolute top-4 left-4 flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgb(16 185 129); color: white;">
                <x-heroicon-s-star class="h-4 w-4" />
                <span>3 شهور</span>
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-12 pb-2">
                <div class="text-4xl font-bold mb-2" style="color: rgb(17 24 39);">
                    @if($aiRating > 0)
                        {{ number_format($aiRating, 1) }}/5
                    @else
                        {{ number_format($averageRating, 1) }}/5
                    @endif
                </div>
                <div class="text-sm font-medium mb-1" style="color: rgb(55 65 81);">
                    متوسط التحليل الذكي
                </div>
                <div class="text-xs" style="color: rgb(107 114 128);">
                    من الذكاء الاصطناعي
                </div>
            </div>
        </div>

        {{-- 3-Month Reviews Card (Bottom Left in RTL) - White Background --}}
        <div class="rounded-2xl p-6 relative bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            {{-- Badge with Icon Top Left --}}
            <div class="absolute top-4 left-4 flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-medium" style="background: rgb(16 185 129); color: white;">
                <x-heroicon-o-check-circle class="h-4 w-4" />
                <span>3 شهور</span>
            </div>

            {{-- Content - Centered --}}
            <div class="text-center pt-12 pb-2">
                <div class="text-4xl font-bold mb-2" style="color: rgb(17 24 39);">
                    {{ number_format($analysisReviewCount) }}
                </div>
                <div class="text-sm font-medium mb-1" style="color: rgb(55 65 81);">
                    عدد المراجعات
                </div>
                <div class="text-xs" style="color: rgb(107 114 128);">
                    من التحليل الذكي
                </div>
            </div>
        </div>

    </div>
</div>
