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

    // Dynamic colors based on rating
    $ratingColors = match(true) {
        $averageRating >= 4.5 => ['bg' => 'from-green-50 to-green-100/50', 'border' => 'border-green-100', 'text' => 'text-green-900', 'textLight' => 'text-green-700', 'textLighter' => 'text-green-600', 'icon' => 'bg-green-500'],
        $averageRating >= 4.0 => ['bg' => 'from-blue-50 to-blue-100/50', 'border' => 'border-blue-100', 'text' => 'text-blue-900', 'textLight' => 'text-blue-700', 'textLighter' => 'text-blue-600', 'icon' => 'bg-blue-500'],
        $averageRating >= 3.5 => ['bg' => 'from-amber-50 to-amber-100/50', 'border' => 'border-amber-100', 'text' => 'text-amber-900', 'textLight' => 'text-amber-700', 'textLighter' => 'text-amber-600', 'icon' => 'bg-amber-500'],
        $averageRating >= 3.0 => ['bg' => 'from-yellow-50 to-yellow-100/50', 'border' => 'border-yellow-100', 'text' => 'text-yellow-900', 'textLight' => 'text-yellow-700', 'textLighter' => 'text-yellow-600', 'icon' => 'bg-yellow-500'],
        default => ['bg' => 'from-red-50 to-red-100/50', 'border' => 'border-red-100', 'text' => 'text-red-900', 'textLight' => 'text-red-700', 'textLighter' => 'text-red-600', 'icon' => 'bg-red-500'],
    };
@endphp

<div class="space-y-6">
    {{-- Sub-section Header --}}
    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">التقييمات والمراجعات</h3>
    </div>

    {{-- Main Metrics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Total Reviews Card - Indigo --}}
        <div class="rounded-2xl p-6 border" style="background: linear-gradient(to bottom right, rgb(238 242 255), rgb(224 231 255 / 0.5)); border-color: rgb(224 231 255);">
            {{-- Header Row --}}
            <div class="flex items-center justify-between mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgb(99 102 241);">
                    <x-heroicon-o-chat-bubble-left-right class="h-4 w-4 text-white" />
                </div>
            </div>
            {{-- Content --}}
            <div class="space-y-1">
                <div class="text-3xl font-bold" style="color: rgb(30 27 75);">
                    {{ $formattedTotalReviews }}
                </div>
                <div class="text-sm font-medium" style="color: rgb(67 56 202);">إجمالي المراجعات</div>
                <div class="text-xs" style="color: rgb(79 70 229);">من Google Places</div>
            </div>
        </div>

        {{-- Average Rating Card - Dynamic Color --}}
        <div class="bg-gradient-to-br {{ $ratingColors['bg'] }} rounded-2xl p-6 border {{ $ratingColors['border'] }}">
            {{-- Header Row --}}
            <div class="flex items-center justify-between mb-4">
                <div class="w-7 h-7 {{ $ratingColors['icon'] }} rounded-lg flex items-center justify-center">
                    <x-heroicon-s-star class="h-4 w-4 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $ratingColors['text'] }}" style="background: rgba(255,255,255,0.7);">
                    {{ $ratingLabel }}
                </span>
            </div>
            {{-- Content --}}
            <div class="space-y-1">
                <div class="text-3xl font-bold {{ $ratingColors['text'] }}">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}/5
                    @else
                        --
                    @endif
                </div>
                <div class="text-sm font-medium {{ $ratingColors['textLight'] }}">متوسط التقييم الكلّي (كل الفترة)</div>
                @if($averageRating > 0)
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="h-3 w-3 {{ $i <= floor($averageRating) ? 'text-amber-400' : 'text-gray-300' }}" />
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- AI Analysis Metrics Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- 3-Month Review Count - Purple --}}
        <div class="rounded-2xl p-6 border" style="background: linear-gradient(to bottom right, rgb(250 245 255), rgb(243 232 255 / 0.5)); border-color: rgb(243 232 255);">
            {{-- Header Row --}}
            <div class="flex items-center justify-between mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgb(168 85 247);">
                    <x-heroicon-o-chat-bubble-left-right class="h-4 w-4 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: rgba(255,255,255,0.7); color: rgb(147 51 234);">
                    آخر 3 شهور
                </span>
            </div>
            {{-- Content --}}
            <div class="space-y-1">
                <div class="text-3xl font-bold" style="color: rgb(59 7 100);">
                    {{ number_format($analysisReviewCount) }}
                </div>
                <div class="text-sm font-medium" style="color: rgb(126 34 206);">عدد المراجعات</div>
                <div class="text-xs" style="color: rgb(147 51 234);">من التحليل الذكي</div>
            </div>
        </div>

        {{-- 3-Month AI Rating - Emerald --}}
        <div class="rounded-2xl p-6 border" style="background: linear-gradient(to bottom right, rgb(236 253 245), rgb(209 250 229 / 0.5)); border-color: rgb(209 250 229);">
            {{-- Header Row --}}
            <div class="flex items-center justify-between mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgb(16 185 129);">
                    <x-heroicon-s-star class="h-4 w-4 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: rgba(255,255,255,0.7); color: rgb(5 150 105);">
                    آخر 3 شهور
                </span>
            </div>
            {{-- Content --}}
            <div class="space-y-1">
                <div class="text-3xl font-bold" style="color: rgb(6 78 59);">
                    @if($aiRating > 0)
                        {{ number_format($aiRating, 1) }}/5
                    @else
                        {{ number_format($averageRating, 1) }}/5
                    @endif
                </div>
                <div class="text-sm font-medium" style="color: rgb(4 120 87);">متوسط التحليل الذكي</div>
                <div class="text-xs" style="color: rgb(5 150 105);">من الذكاء الاصطناعي</div>
            </div>
        </div>
    </div>
</div>
