<div class="space-y-4" dir="rtl">
    {{-- Categories Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Categories List --}}
        <div class="p-6 space-y-4">
            @forelse(($data['categories'] ?? $data ?? []) as $category)
                @php
                    $rating = $category['rating'] ?? 0;
                    $totalMentions = $category['totalMentions'] ?? $category['mentions'] ?? 0;
                    $positiveCount = $category['positiveCount'] ?? 0;
                    $negativeCount = $category['negativeCount'] ?? 0;
                    $positiveExamples = $category['positiveExamples'] ?? $category['topPhrasesPositive'] ?? [];
                    $negativeExamples = $category['negativeExamples'] ?? $category['topPhrasesNegative'] ?? [];

                    // Star color based on rating
                    $starColor = match(true) {
                        $rating >= 4 => 'color: rgb(34 197 94);',
                        $rating >= 3 => 'color: rgb(234 179 8);',
                        default => 'color: rgb(239 68 68);',
                    };
                @endphp

                {{-- Category Card --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                    {{-- Header with Category Name on Right, Star Rating and Mentions on Left --}}
                    <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                        <div class="text-right">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $category['name'] }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($totalMentions) }} إشارة
                            </div>
                            <div class="text-xl font-bold flex items-center gap-1" style="{{ $starColor }}">
                                {{ number_format($rating, 1) }}
                                <span>⭐</span>
                            </div>
                        </div>
                    </div>

                    {{-- Authentic Feedback Count Split --}}
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        {{-- Positive Feedback --}}
                        <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                            <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                {{ number_format($positiveCount) }}
                            </div>
                            <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                        </div>

                        {{-- Negative Feedback --}}
                        <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                            <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                {{ number_format($negativeCount) }}
                            </div>
                            <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                        </div>
                    </div>

                    {{-- Customer Quote Examples --}}
                    <div class="space-y-3">
                        {{-- Positive Quote --}}
                        @if(!empty($positiveExamples) && is_array($positiveExamples) && count(array_filter($positiveExamples, fn($q) => is_string($q) && trim($q))) > 0)
                            @php $firstPositive = collect($positiveExamples)->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
                            @if($firstPositive)
                                <div class="rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244); border-right: 4px solid rgb(74 222 128);">
                                    <div class="text-xs font-medium" style="color: rgb(22 163 74); margin-bottom: 0.25rem;">
                                        مثال إيجابي:
                                    </div>
                                    <p class="text-sm" style="color: rgb(21 128 61);">
                                        "{{ trim($firstPositive) }}"
                                    </p>
                                </div>
                            @endif
                        @endif

                        {{-- Negative Quote --}}
                        @if(!empty($negativeExamples) && is_array($negativeExamples) && count(array_filter($negativeExamples, fn($q) => is_string($q) && trim($q))) > 0)
                            @php $firstNegative = collect($negativeExamples)->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
                            @if($firstNegative)
                                <div class="rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242); border-right: 4px solid rgb(248 113 113);">
                                    <div class="text-xs font-medium" style="color: rgb(220 38 38); margin-bottom: 0.25rem;">
                                        مثال سلبي:
                                    </div>
                                    <p class="text-sm" style="color: rgb(185 28 28);">
                                        "{{ trim($firstNegative) }}"
                                    </p>
                                </div>
                            @endif
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
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            {{-- Best Category --}}
            @if(!empty($data['bestCategory']))
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(240 253 244), rgb(220 252 231)); border-color: rgb(187 247 208);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                            <x-heroicon-o-trophy class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(22 163 74);">أفضل فئة</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['bestCategory']['name'] }}</h3>
                        </div>
                    </div>
                    @if(!empty($data['bestCategory']['rating']))
                        <div class="flex items-center gap-2">
                            <div class="flex">
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
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(254 242 242), rgb(254 226 226)); border-color: rgb(254 202 202);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(239 68 68);">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(220 38 38);">فئة تحتاج تحسين</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['worstCategory']['name'] }}</h3>
                        </div>
                    </div>
                    @if(!empty($data['worstCategory']['rating']))
                        <div class="flex items-center gap-2">
                            <div class="flex">
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
