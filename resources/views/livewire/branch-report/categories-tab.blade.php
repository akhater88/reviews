<div class="space-y-6" dir="rtl">
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm border border-blue-100 dark:border-blue-800" style="background: linear-gradient(to right, rgb(239 246 255), rgb(238 242 255));">
        <div class="px-5 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">تحليل الفئات</h2>
        </div>
    </div>

    {{-- Categories Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Header with Icon --}}
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(99 102 241);">
                    <x-heroicon-o-tag class="w-6 h-6 text-white" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">تحليل الفئات</h3>
                @php $categoryCount = count($data['categories'] ?? $data ?? []); @endphp
                @if($categoryCount > 0)
                    <span class="mr-auto px-3 py-1.5 text-sm font-medium rounded-full" style="background-color: rgb(238 242 255); color: rgb(99 102 241);">
                        {{ $categoryCount }} فئة
                    </span>
                @endif
            </div>
        </div>

        {{-- Categories List --}}
        <div class="p-6 space-y-6">
            @forelse(($data['categories'] ?? $data ?? []) as $category)
                @php
                    $rating = $category['rating'] ?? 0;
                    $totalMentions = $category['totalMentions'] ?? $category['mentions'] ?? 0;
                    $positiveCount = $category['positiveCount'] ?? 0;
                    $negativeCount = $category['negativeCount'] ?? 0;
                    $positiveExamples = $category['positiveExamples'] ?? [];
                    $negativeExamples = $category['negativeExamples'] ?? [];
                @endphp

                <div class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                    {{-- Category Header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $category['name'] }}</h4>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($totalMentions) }} إشارة</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($rating, 1) }}</span>
                                <x-heroicon-s-star class="w-5 h-5 text-yellow-400" />
                            </div>
                        </div>
                    </div>

                    {{-- Positive/Negative Counts --}}
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div class="text-center p-4 rounded-xl" style="background-color: rgb(240 253 244);">
                            <div class="text-2xl font-bold" style="color: rgb(22 163 74);">{{ number_format($positiveCount) }}</div>
                            <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                        </div>
                        <div class="text-center p-4 rounded-xl" style="background-color: rgb(254 242 242);">
                            <div class="text-2xl font-bold" style="color: rgb(220 38 38);">{{ number_format($negativeCount) }}</div>
                            <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                        </div>
                    </div>

                    {{-- Quote Examples --}}
                    <div class="space-y-3">
                        @if(!empty($positiveExamples) && is_array($positiveExamples))
                            <div class="p-4 rounded-lg" style="background-color: rgb(240 253 244); border-right: 4px solid rgb(74 222 128);">
                                <div class="text-xs font-medium mb-1" style="color: rgb(22 163 74);">مثال إيجابي:</div>
                                <p class="text-sm" style="color: rgb(21 128 61);">"{{ is_string($positiveExamples[0] ?? '') ? $positiveExamples[0] : '' }}"</p>
                            </div>
                        @endif

                        @if(!empty($negativeExamples) && is_array($negativeExamples))
                            <div class="p-4 rounded-lg" style="background-color: rgb(254 242 242); border-right: 4px solid rgb(248 113 113);">
                                <div class="text-xs font-medium mb-1" style="color: rgb(220 38 38);">مثال سلبي:</div>
                                <p class="text-sm" style="color: rgb(185 28 28);">"{{ is_string($negativeExamples[0] ?? '') ? $negativeExamples[0] : '' }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <x-heroicon-o-tag class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">لا توجد بيانات فئات متاحة</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Best & Worst Categories Summary --}}
    @if(!empty($data['bestCategory']) || !empty($data['worstCategory']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Best Category --}}
            @if(!empty($data['bestCategory']))
                <div class="rounded-xl p-6 border border-green-200 dark:border-green-700" style="background: linear-gradient(to bottom right, rgb(240 253 244), rgba(187 247 208, 0.5));">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                            <x-heroicon-o-trophy class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-green-600 dark:text-green-400">أفضل فئة</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['bestCategory']['name'] }}</h3>
                        </div>
                    </div>
                    @if(!empty($data['bestCategory']['rating']))
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($data['bestCategory']['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($data['bestCategory']['rating'], 1) }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Worst Category --}}
            @if(!empty($data['worstCategory']))
                <div class="rounded-xl p-6 border border-red-200 dark:border-red-700" style="background: linear-gradient(to bottom right, rgb(254 242 242), rgba(254 202 202, 0.5));">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-red-600 dark:text-red-400">فئة تحتاج تحسين</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['worstCategory']['name'] }}</h3>
                        </div>
                    </div>
                    @if(!empty($data['worstCategory']['rating']))
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($data['worstCategory']['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($data['worstCategory']['rating'], 1) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
