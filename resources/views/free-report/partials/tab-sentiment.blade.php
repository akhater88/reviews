<div class="space-y-6">
    @php
        $sentimentBreakdown = $result->sentiment_breakdown ?? ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        $total = array_sum($sentimentBreakdown);
        $positivePercent = $total > 0 ? ($sentimentBreakdown['positive'] / $total) * 100 : 0;
        $negativePercent = $total > 0 ? ($sentimentBreakdown['negative'] / $total) * 100 : 0;
        $neutralPercent = $total > 0 ? ($sentimentBreakdown['neutral'] / $total) * 100 : 0;
    @endphp

    <!-- Sentiment Distribution -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
            <div class="text-4xl font-bold text-green-600 mb-2">{{ $sentimentBreakdown['positive'] ?? 0 }}</div>
            <div class="text-green-700 font-medium">تقييمات إيجابية</div>
            <div class="text-green-600 text-sm mt-1">{{ number_format($positivePercent, 1) }}%</div>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <div class="text-4xl font-bold text-red-600 mb-2">{{ $sentimentBreakdown['negative'] ?? 0 }}</div>
            <div class="text-red-700 font-medium">تقييمات سلبية</div>
            <div class="text-red-600 text-sm mt-1">{{ number_format($negativePercent, 1) }}%</div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
            <div class="text-4xl font-bold text-gray-600 mb-2">{{ $sentimentBreakdown['neutral'] ?? 0 }}</div>
            <div class="text-gray-700 font-medium">تقييمات محايدة</div>
            <div class="text-gray-600 text-sm mt-1">{{ number_format($neutralPercent, 1) }}%</div>
        </div>
    </div>

    <!-- Sample Reviews -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-6">نماذج من التقييمات</h3>

        <div class="space-y-4">
            @forelse($reviews->take(5) as $review)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold">
                                {{ mb_substr($review->author_name ?? 'م', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $review->author_name ?? 'مجهول' }}</p>
                                <p class="text-xs text-gray-500">{{ $review->review_time?->diffForHumans() ?? '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Star Rating -->
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                            <!-- Sentiment Badge -->
                            @php
                                $sentiment = $review->rating >= 4 ? 'positive' : ($review->rating <= 2 ? 'negative' : 'neutral');
                                $sentimentConfig = [
                                    'positive' => ['bg' => 'green', 'label' => 'إيجابي'],
                                    'negative' => ['bg' => 'red', 'label' => 'سلبي'],
                                    'neutral' => ['bg' => 'gray', 'label' => 'محايد'],
                                ];
                                $sentConfig = $sentimentConfig[$sentiment];
                            @endphp
                            <span class="px-2 py-1 bg-{{ $sentConfig['bg'] }}-100 text-{{ $sentConfig['bg'] }}-700 text-xs rounded-full">
                                {{ $sentConfig['label'] }}
                            </span>
                        </div>
                    </div>
                    @if($review->text)
                        <p class="mt-3 text-gray-700 text-sm leading-relaxed">
                            {{ Str::limit($review->text, 300) }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    لا توجد تقييمات لعرضها
                </div>
            @endforelse
        </div>
    </div>
</div>
