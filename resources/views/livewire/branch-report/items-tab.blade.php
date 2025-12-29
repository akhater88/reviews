<div class="space-y-4" dir="rtl">
    {{-- Keywords Section --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="p-6">
            @if(!empty($data['keywordGroups']))
                <div class="flex flex-wrap gap-3">
                    @foreach($data['keywordGroups'] as $keyword)
                        @php
                            $sentiment = $keyword['sentiment'] ?? 'neutral';
                            $bgStyle = match($sentiment) {
                                'positive' => 'background: rgb(240 253 244); border-color: rgb(187 247 208); color: rgb(21 128 61);',
                                'negative' => 'background: rgb(254 242 242); border-color: rgb(254 202 202); color: rgb(185 28 28);',
                                default => 'background: rgb(243 244 246); border-color: rgb(229 231 235); color: rgb(55 65 81);',
                            };
                        @endphp
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border" style="{{ $bgStyle }}">
                            <span class="font-medium">{{ $keyword['mainKeyword'] }}</span>
                            <span class="text-xs opacity-75">({{ $keyword['frequency'] ?? 0 }})</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-tag class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">لا توجد كلمات مفتاحية</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Food Items --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        <div class="p-6 space-y-4">
            @forelse(($data['foodItems'] ?? []) as $item)
                @php
                    $rating = $item['averageRating'] ?? 0;
                    $sentiment = $item['sentiment'] ?? 'neutral';

                    $starColor = match(true) {
                        $rating >= 4 => 'color: rgb(34 197 94);',
                        $rating >= 3 => 'color: rgb(234 179 8);',
                        default => 'color: rgb(239 68 68);',
                    };

                    $borderColor = match($sentiment) {
                        'positive' => 'border-color: rgb(74 222 128);',
                        'negative' => 'border-color: rgb(248 113 113);',
                        'mixed' => 'border-color: rgb(250 204 21);',
                        default => '',
                    };
                @endphp
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem; {{ $borderColor }}">
                    {{-- Header --}}
                    <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgb(251 191 36);">
                                <x-heroicon-o-cake class="w-5 h-5 text-white" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $item['mentions'] ?? 0 }} ذكر
                            </div>
                            <div class="text-xl font-bold flex items-center gap-1" style="{{ $starColor }}">
                                {{ number_format($rating, 1) }}
                                <span>⭐</span>
                            </div>
                        </div>
                    </div>

                    {{-- Feedback Count Split --}}
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                            <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                {{ $item['positiveCount'] ?? 0 }}
                            </div>
                            <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                        </div>
                        <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                            <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                {{ $item['negativeCount'] ?? 0 }}
                            </div>
                            <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                        </div>
                    </div>

                    {{-- Quote Examples --}}
                    @if(!empty($item['topPhrasesPositive']) || !empty($item['topPhrasesNegative']))
                        <div class="space-y-3" style="margin-top: 1rem;">
                            @if(!empty($item['topPhrasesPositive']) && is_array($item['topPhrasesPositive']))
                                @php $firstPositive = collect($item['topPhrasesPositive'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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

                            @if(!empty($item['topPhrasesNegative']) && is_array($item['topPhrasesNegative']))
                                @php $firstNegative = collect($item['topPhrasesNegative'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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
                    @endif
                </div>
            @empty
                <div class="text-center py-12">
                    <x-heroicon-o-cake class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">لا توجد عناصر غذائية مذكورة</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Aspects Analysis --}}
    @if(!empty($data['aspects']))
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
            <div class="p-6 space-y-4">
                @foreach($data['aspects'] as $aspect)
                    @php
                        $positiveCount = $aspect['positiveCount'] ?? 0;
                        $negativeCount = $aspect['negativeCount'] ?? 0;
                        $total = $positiveCount + $negativeCount + ($aspect['neutralCount'] ?? 0);
                        $rating = $total > 0 ? (($positiveCount - $negativeCount) / $total * 2.5 + 2.5) : 2.5;

                        $starColor = match(true) {
                            $rating >= 4 => 'color: rgb(34 197 94);',
                            $rating >= 3 => 'color: rgb(234 179 8);',
                            default => 'color: rgb(239 68 68);',
                        };
                    @endphp
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                        {{-- Header --}}
                        <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgb(99 102 241);">
                                    <x-heroicon-o-puzzle-piece class="w-5 h-5 text-white" />
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $aspect['name'] }}</h3>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format(($aspect['confidence'] ?? 0) * 100) }}% ثقة
                                </div>
                            </div>
                        </div>

                        {{-- Feedback Count Split --}}
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                            <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                                <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                    {{ number_format($positiveCount) }}
                                </div>
                                <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                            </div>
                            <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                                <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                    {{ number_format($negativeCount) }}
                                </div>
                                <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        @php
                            $positiveWidth = $total > 0 ? ($positiveCount / $total) * 100 : 0;
                            $negativeWidth = $total > 0 ? ($negativeCount / $total) * 100 : 0;
                        @endphp
                        <div class="flex h-2 rounded-full overflow-hidden" style="background: rgb(229 231 235); margin-bottom: 1rem;">
                            <div style="width: {{ $positiveWidth }}%; background: rgb(34 197 94);"></div>
                            <div style="width: {{ $negativeWidth }}%; background: rgb(239 68 68);"></div>
                        </div>

                        {{-- Quote Examples --}}
                        <div class="space-y-3">
                            @if(!empty($aspect['topPhrasesPositive']) && is_array($aspect['topPhrasesPositive']))
                                @php $firstPositive = collect($aspect['topPhrasesPositive'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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

                            @if(!empty($aspect['topPhrasesNegative']) && is_array($aspect['topPhrasesNegative']))
                                @php $firstNegative = collect($aspect['topPhrasesNegative'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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
                @endforeach
            </div>
        </div>
    @endif

    {{-- Best & Worst Items Summary --}}
    @if(!empty($data['foodItems']) && count($data['foodItems']) >= 2)
        @php
            $sortedItems = collect($data['foodItems'])->sortByDesc('averageRating');
            $bestItem = $sortedItems->first();
            $worstItem = $sortedItems->last();
        @endphp
        @if($bestItem && $worstItem && $bestItem['name'] !== $worstItem['name'])
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                {{-- Best Item --}}
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(240 253 244), rgb(220 252 231)); border-color: rgb(187 247 208);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                            <x-heroicon-o-trophy class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(22 163 74);">أفضل عنصر</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $bestItem['name'] }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($bestItem['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($bestItem['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>

                {{-- Worst Item --}}
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(254 242 242), rgb(254 226 226)); border-color: rgb(254 202 202);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(239 68 68);">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(220 38 38);">يحتاج تحسين</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $worstItem['name'] }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($worstItem['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($worstItem['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
