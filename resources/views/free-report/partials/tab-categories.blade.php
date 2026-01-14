<div class="space-y-6">
    @php
        $categoryScores = $result->category_scores ?? [];
        $categoryLabels = [
            'food_quality' => 'جودة الطعام',
            'service' => 'الخدمة',
            'cleanliness' => 'النظافة',
            'value_for_money' => 'القيمة مقابل السعر',
            'ambiance' => 'الأجواء',
        ];
    @endphp

    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-6">تحليل الفئات</h3>

        @if(count($categoryScores) > 0)
            <div class="space-y-6">
                @foreach($categoryScores as $key => $score)
                    @php
                        $label = $categoryLabels[$key] ?? $key;
                        $percentage = ($score / 10) * 100;
                        $color = $score >= 8 ? 'green' : ($score >= 6 ? 'blue' : ($score >= 4 ? 'yellow' : 'red'));
                    @endphp

                    <div class="border-b border-gray-100 pb-4 last:border-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ $label }}</span>
                            <span class="text-sm font-bold text-{{ $color }}-600">{{ number_format($score, 1) }}/10</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-{{ $color }}-500 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-gray-500">لا تتوفر بيانات فئات كافية</p>
            </div>
        @endif
    </div>

    <!-- Best & Worst Categories -->
    @if(count($categoryScores) > 0)
        @php
            $sortedCategories = collect($categoryScores)->map(function($score, $key) use ($categoryLabels) {
                return [
                    'key' => $key,
                    'label' => $categoryLabels[$key] ?? $key,
                    'score' => $score,
                ];
            })->sortByDesc('score');

            $bestCategory = $sortedCategories->first();
            $worstCategory = $sortedCategories->last();
        @endphp

        <div class="grid md:grid-cols-2 gap-6">
            @if($bestCategory)
                <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm text-green-700">أفضل فئة</p>
                            <p class="font-bold text-green-900">{{ $bestCategory['label'] }}</p>
                        </div>
                    </div>
                    <p class="text-green-700 text-sm">{{ number_format($bestCategory['score'], 1) }}/10</p>
                </div>
            @endif

            @if($worstCategory && $worstCategory['score'] < 7)
                <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm text-red-700">تحتاج تحسين</p>
                            <p class="font-bold text-red-900">{{ $worstCategory['label'] }}</p>
                        </div>
                    </div>
                    <p class="text-red-700 text-sm">{{ number_format($worstCategory['score'], 1) }}/10</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Category Analysis Info -->
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-purple-900 mb-1">تحليل متقدم للفئات</h4>
                <p class="text-purple-800 text-sm">
                    اشترك في TABsense للحصول على تحليل مفصل لكل فئة مع توصيات محددة للتحسين ومقارنة مع المنافسين.
                </p>
            </div>
        </div>
    </div>
</div>
