<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Positive Keywords -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                </span>
                كلمات إيجابية
            </h3>

            @php
                $keywordAnalysis = $result->keyword_analysis ?? [];
                $positiveKeywords = $keywordAnalysis['positive_keywords'] ?? [];
            @endphp

            @if(count($positiveKeywords) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($positiveKeywords as $keyword)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            {{ $keyword }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">لا توجد كلمات إيجابية محددة</p>
            @endif
        </div>

        <!-- Negative Keywords -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                    </svg>
                </span>
                كلمات تحتاج تحسين
            </h3>

            @php
                $negativeKeywords = $keywordAnalysis['negative_keywords'] ?? [];
            @endphp

            @if(count($negativeKeywords) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($negativeKeywords as $keyword)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            {{ $keyword }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">لا توجد كلمات سلبية محددة</p>
            @endif
        </div>
    </div>

    <!-- Keyword Analysis Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 mb-1">كيف نحلل الكلمات المفتاحية؟</h4>
                <p class="text-blue-800 text-sm">
                    يقوم الذكاء الاصطناعي باستخراج الكلمات الأكثر تكراراً في تقييمات عملائك وتصنيفها حسب السياق إيجابي أو سلبي. هذا يساعدك على فهم ما يحبه عملاؤك وما يحتاج تحسين.
                </p>
            </div>
        </div>
    </div>
</div>
