<div class="space-y-6" dir="rtl">
    {{-- Best & Worst Categories --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Best Category --}}
        @if(!empty($data['bestCategory']))
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-6 border border-green-200 dark:border-green-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <x-heroicon-o-trophy class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-green-600 dark:text-green-400">الفئة الأفضل أداءً</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['bestCategory']['name'] }}</h3>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($data['bestCategory']['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                        @endfor
                    </div>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($data['bestCategory']['rating'] ?? 0, 1) }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $data['bestCategory']['reason'] ?? '' }}</p>
                @if(!empty($data['bestCategory']['evidenceQuote']))
                    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $data['bestCategory']['evidenceQuote'] }}"</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Worst Category --}}
        @if(!empty($data['worstCategory']))
            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl p-6 border border-red-200 dark:border-red-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-red-600 dark:text-red-400">الفئة التي تحتاج تحسين</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['worstCategory']['name'] }}</h3>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($data['worstCategory']['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                        @endfor
                    </div>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($data['worstCategory']['rating'] ?? 0, 1) }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $data['worstCategory']['reason'] ?? '' }}</p>
                @if(!empty($data['worstCategory']['evidenceQuote']))
                    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $data['worstCategory']['evidenceQuote'] }}"</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Categories List --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تحليل الفئات</h3>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse(($data['categories'] ?? []) as $category)
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        {{-- Category Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category['name'] }}</h4>
                                @php
                                    $sentimentColor = match($category['overallSentiment'] ?? 'neutral') {
                                        'positive' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'negative' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sentimentColor }}">
                                    {{ match($category['overallSentiment'] ?? 'neutral') {
                                        'positive' => 'إيجابي',
                                        'negative' => 'سلبي',
                                        default => 'محايد',
                                    } }}
                                </span>
                            </div>

                            {{-- Sentiment Bar --}}
                            <div class="flex items-center gap-4 mb-4">
                                <div class="flex-1">
                                    @php
                                        $total = ($category['positiveCount'] ?? 0) + ($category['negativeCount'] ?? 0) + ($category['mixedCount'] ?? 0);
                                        $positiveWidth = $total > 0 ? (($category['positiveCount'] ?? 0) / $total) * 100 : 0;
                                        $negativeWidth = $total > 0 ? (($category['negativeCount'] ?? 0) / $total) * 100 : 0;
                                    @endphp
                                    <div class="flex h-3 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                                        <div class="bg-green-500" style="width: {{ $positiveWidth }}%"></div>
                                        <div class="bg-red-500" style="width: {{ $negativeWidth }}%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="flex items-center gap-1 text-green-600">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        {{ $category['positiveCount'] ?? 0 }} إيجابي
                                    </span>
                                    <span class="flex items-center gap-1 text-red-600">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                        {{ $category['negativeCount'] ?? 0 }} سلبي
                                    </span>
                                </div>
                            </div>

                            {{-- Examples --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if(!empty($category['positiveExamples']))
                                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                        <p class="text-xs font-medium text-green-600 dark:text-green-400 mb-2">مثال إيجابي</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $category['positiveExamples'][0] ?? '' }}"</p>
                                    </div>
                                @endif
                                @if(!empty($category['negativeExamples']))
                                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                                        <p class="text-xs font-medium text-red-600 dark:text-red-400 mb-2">مثال سلبي</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">"{{ $category['negativeExamples'][0] ?? '' }}"</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($category['rating'] ?? 0, 1) }}</div>
                            <div class="flex justify-center lg:justify-start text-yellow-400 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-heroicon-s-star class="w-4 h-4 {{ $i <= round($category['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $category['totalMentions'] ?? 0 }} ذكر</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">لا توجد بيانات فئات متاحة</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
