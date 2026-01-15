<div class="space-y-6">
    <!-- Response Rate & Stats -->
    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4">إحصائيات التشغيل</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">إجمالي التقييمات</span>
                    <span class="font-bold text-gray-900">{{ number_format($result->total_reviews ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">متوسط التقييم</span>
                    <span class="font-bold text-yellow-600">{{ number_format($result->average_rating ?? 0, 1) }} / 5</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">النتيجة الإجمالية</span>
                    <span class="font-bold text-blue-600">{{ number_format($result->overall_score ?? 0, 1) }} / 10</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4">التقدير</h3>
            <div class="text-center py-4">
                @php
                    $grade = $result->getGrade();
                    $gradeColor = $result->getGradeColor();
                    $gradeColorClasses = [
                        'green' => 'from-green-400 to-green-600',
                        'blue' => 'from-blue-400 to-blue-600',
                        'yellow' => 'from-yellow-400 to-yellow-600',
                        'orange' => 'from-orange-400 to-orange-600',
                        'red' => 'from-red-400 to-red-600',
                    ];
                @endphp
                <div class="w-24 h-24 mx-auto bg-gradient-to-br {{ $gradeColorClasses[$gradeColor] ?? 'from-gray-400 to-gray-600' }} rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                    {{ $grade }}
                </div>
                <p class="mt-4 text-gray-600">
                    @switch($grade)
                        @case('A+')
                        @case('A')
                            أداء ممتاز! استمر على هذا المستوى
                            @break
                        @case('B')
                            أداء جيد مع فرص للتحسين
                            @break
                        @case('C')
                            أداء مقبول، يحتاج بعض التحسينات
                            @break
                        @case('D')
                            أداء ضعيف، يحتاج تحسينات جوهرية
                            @break
                        @default
                            يحتاج اهتماماً عاجلاً
                    @endswitch
                </p>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-4">توزيع التقييمات</h3>
        @php
            $categoryScores = $result->category_scores ?? [];
        @endphp

        @if(count($categoryScores) > 0)
            <div class="space-y-3">
                @foreach($categoryScores as $category => $score)
                    @php
                        $categoryLabels = [
                            'food_quality' => 'جودة الطعام',
                            'service' => 'الخدمة',
                            'cleanliness' => 'النظافة',
                            'value_for_money' => 'القيمة مقابل السعر',
                            'ambiance' => 'الأجواء',
                        ];
                        $label = $categoryLabels[$category] ?? $category;
                        $percentage = ($score / 10) * 100;
                        $color = $score >= 7 ? 'green' : ($score >= 5 ? 'yellow' : 'red');
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $label }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($score, 1) }}/10</span>
                        </div>
                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-{{ $color }}-500 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                لا تتوفر بيانات الفئات
            </div>
        @endif
    </div>

    <!-- Pro Tip -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 mb-1">الرد على التقييمات يحسّن صورتك</h4>
                <p class="text-blue-800 text-sm">
                    الرد على التقييمات السلبية يظهر اهتمامك بالعملاء ويحسّن صورة المحل. اشترك في سُمعة للحصول على ردود ذكية مقترحة.
                </p>
            </div>
        </div>
    </div>
</div>
