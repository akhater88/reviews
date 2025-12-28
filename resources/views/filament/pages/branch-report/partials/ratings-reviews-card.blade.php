@php
    $data = $card['data'] ?? [];

    // Get total reviews from card data or branch
    $totalReviews = $data['totalReviews'] ?? $this->branch->total_reviews ?? 0;
    $reviewsWithText = $data['reviewsWithText'] ?? 0;

    // Get rating from branch model (card data doesn't have it)
    $averageRating = $this->branch->current_rating ?? 0;

    // For AI analysis period, use the analysis data
    $analysisReviewCount = $totalReviews;

    // Dynamic rating colors with inline styles
    $ratingStyles = match(true) {
        $averageRating >= 4.5 => [
            'bg' => 'background: linear-gradient(to bottom right, rgb(240 253 244), rgba(187 247 208, 0.5));',
            'border' => 'border-color: rgb(220 252 231);',
            'text' => 'color: rgb(20 83 45);',
            'icon' => 'background: rgb(34 197 94);',
        ],
        $averageRating >= 4.0 => [
            'bg' => 'background: linear-gradient(to bottom right, rgb(239 246 255), rgba(191 219 254, 0.5));',
            'border' => 'border-color: rgb(219 234 254);',
            'text' => 'color: rgb(30 64 175);',
            'icon' => 'background: rgb(59 130 246);',
        ],
        $averageRating >= 3.5 => [
            'bg' => 'background: linear-gradient(to bottom right, rgb(255 251 235), rgba(253 230 138, 0.5));',
            'border' => 'border-color: rgb(254 243 199);',
            'text' => 'color: rgb(146 64 14);',
            'icon' => 'background: rgb(245 158 11);',
        ],
        default => [
            'bg' => 'background: linear-gradient(to bottom right, rgb(254 242 242), rgba(254 202 202, 0.5));',
            'border' => 'border-color: rgb(254 226 226);',
            'text' => 'color: rgb(127 29 29);',
            'icon' => 'background: rgb(239 68 68);',
        ],
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
        <div class="rounded-2xl p-5 sm:p-6 border border-indigo-100 dark:border-indigo-800" style="background: linear-gradient(to bottom right, rgb(238 242 255), rgba(199 210 254, 0.5));">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(99 102 241);">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 text-white" />
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold text-indigo-900 dark:text-indigo-100">
                    {{ number_format($totalReviews) }}
                </div>
                <div class="text-sm font-medium text-indigo-700 dark:text-indigo-300">إجمالي المراجعات</div>
                @if($reviewsWithText > 0)
                    <div class="text-xs text-indigo-600 dark:text-indigo-400">{{ number_format($reviewsWithText) }} مراجعة نصية</div>
                @else
                    <div class="text-xs text-indigo-600 dark:text-indigo-400">من Google Places</div>
                @endif
            </div>
        </div>

        {{-- Average Rating Card --}}
        <div class="rounded-2xl p-5 sm:p-6 border" style="{{ $ratingStyles['bg'] }} {{ $ratingStyles['border'] }}">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="{{ $ratingStyles['icon'] }}">
                    <x-heroicon-s-star class="h-5 w-5 text-white" />
                </div>
                @if($averageRating > 0)
                    <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: rgba(255,255,255,0.7); {{ $ratingStyles['text'] }}">
                        {{ $ratingLabel }}
                    </span>
                @endif
            </div>
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-bold" style="{{ $ratingStyles['text'] }}">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}/5
                    @else
                        --
                    @endif
                </div>
                <div class="text-sm font-medium" style="{{ $ratingStyles['text'] }} opacity: 0.8;">متوسط التقييم الكلّي</div>
                @if($averageRating > 0)
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="h-4 w-4 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" />
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Analysis Period Info --}}
    @if(!empty($data['periodStart']) && !empty($data['periodEnd']))
        <div class="rounded-2xl p-5 sm:p-6 border border-purple-100 dark:border-purple-800" style="background: linear-gradient(to bottom right, rgb(250 245 255), rgba(233 213 255, 0.5));">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(168 85 247);">
                    <x-heroicon-o-calendar-days class="h-5 w-5 text-white" />
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: rgba(255,255,255,0.7); color: rgb(126 34 206);">
                    فترة التحليل
                </span>
            </div>
            <div class="space-y-1">
                <div class="text-lg font-bold text-purple-900 dark:text-purple-100">
                    {{ $data['periodStart'] }} - {{ $data['periodEnd'] }}
                </div>
                <div class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ number_format($analysisReviewCount) }} مراجعة تم تحليلها</div>
                <div class="text-xs text-purple-600 dark:text-purple-400">بواسطة الذكاء الاصطناعي</div>
            </div>
        </div>
    @endif
</div>
