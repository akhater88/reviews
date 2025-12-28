<div class="bg-white rounded-2xl shadow-sm p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">تفاصيل النقاط</h3>

    <div class="space-y-4">
        <!-- Rating Score (25%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">التقييم (25%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->overall_rating ?? 0, 1) }}</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-yellow-500 rounded-full transition-all duration-500"
                    style="width: {{ min((($score->overall_rating ?? 0) / 5) * 100, 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Sentiment Score (30%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">تحليل المشاعر (30%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->sentiment_score ?? 0, 1) }}</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-green-500 rounded-full transition-all duration-500"
                    style="width: {{ min(($score->sentiment_score ?? 0), 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Response Rate (15%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">معدل الرد (15%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->response_rate ?? 0, 1) }}%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-blue-500 rounded-full transition-all duration-500"
                    style="width: {{ min($score->response_rate ?? 0, 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Volume Score (10%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">حجم المراجعات (10%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->review_volume_score ?? 0, 1) }}</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-purple-500 rounded-full transition-all duration-500"
                    style="width: {{ min(($score->review_volume_score ?? 0), 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Trend Score (10%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">اتجاه التحسن (10%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->trend_score ?? 0, 1) }}</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                    style="width: {{ min(($score->trend_score ?? 0), 100) }}%"
                ></div>
            </div>
        </div>

        <!-- Keyword Score (10%) -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-gray-700 text-sm">الكلمات المفتاحية (10%)</span>
                <span class="font-bold text-gray-900">{{ number_format($score->keyword_score ?? 0, 1) }}</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full bg-pink-500 rounded-full transition-all duration-500"
                    style="width: {{ min(($score->keyword_score ?? 0), 100) }}%"
                ></div>
            </div>
        </div>
    </div>

    <!-- Total Score -->
    <div class="mt-6 pt-4 border-t">
        <div class="flex items-center justify-between">
            <span class="text-gray-900 font-bold">المجموع الكلي</span>
            <span class="text-2xl font-bold text-orange-600">{{ number_format($score->competition_score ?? 0, 1) }}/100</span>
        </div>
    </div>

    @if($score->updated_at)
        <p class="text-gray-400 text-xs text-center mt-4">
            آخر تحديث: {{ $score->updated_at->diffForHumans() }}
        </p>
    @endif
</div>
