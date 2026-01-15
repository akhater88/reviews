<div class="space-y-6">
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
            <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </span>
            توصيات ذكية بناءً على تحليل التقييمات
        </h3>

        @php
            $recommendations = $result->recommendations ?? [];
        @endphp

        @if(count($recommendations) > 0)
            <div class="space-y-4">
                @foreach($recommendations as $index => $recommendation)
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 hover:border-gray-300 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-700 leading-relaxed">
                                    {{ is_array($recommendation) ? ($recommendation['description'] ?? $recommendation['title'] ?? '') : $recommendation }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <p class="text-gray-500">لم يتم إنشاء توصيات بعد</p>
            </div>
        @endif
    </div>

    <!-- Pro Tip -->
    <div class="bg-gradient-to-l from-purple-600 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-1">نصيحة احترافية</h4>
                <p class="text-white/80 text-sm">
                    اشترك في سُمعة للحصول على توصيات محدثة شهرياً، مع إمكانية الرد الذكي على التقييمات السلبية وتحويلها إلى فرص للتحسين.
                </p>
            </div>
        </div>
    </div>
</div>
