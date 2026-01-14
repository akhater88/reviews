<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Reviews -->
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">إجمالي التقييمات</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ number_format($result->total_reviews ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Average Rating -->
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">متوسط التقييم</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ number_format($result->average_rating ?? 0, 1) }}</p>
            </div>
        </div>
    </div>

    <!-- Positive Percentage -->
    @php
        $percentages = $result ? $result->getSentimentPercentages() : ['positive' => 0, 'negative' => 0];
    @endphp
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">تقييمات إيجابية</p>
                <p class="text-xl sm:text-2xl font-bold text-green-600">{{ number_format($percentages['positive'], 0) }}%</p>
            </div>
        </div>
    </div>

    <!-- Needs Attention -->
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">تحتاج انتباه</p>
                <p class="text-xl sm:text-2xl font-bold text-red-600">{{ number_format($percentages['negative'], 0) }}%</p>
            </div>
        </div>
    </div>
</div>
