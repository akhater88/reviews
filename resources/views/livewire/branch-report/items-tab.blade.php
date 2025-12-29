<div class="space-y-6" dir="rtl">
    {{-- Keyword Groups Grid --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <x-heroicon-o-hashtag class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الكلمات الأكثر تكراراً</h3>
                <div class="mr-auto px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                    {{ count($data['keywordGroups'] ?? []) }} كلمة أساسية
                </div>
            </div>
        </div>
        <div class="p-4">
            @if(!empty($data['keywordGroups']))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($data['keywordGroups'] as $group)
                        @php
                            $sentiment = $group['sentiment'] ?? 'neutral';
                            $bgColor = match($sentiment) {
                                'positive' => 'background: rgb(236 253 245); border-color: rgb(167 243 208);',
                                'negative' => 'background: rgb(254 242 242); border-color: rgb(254 202 202);',
                                'mixed' => 'background: rgb(254 252 232); border-color: rgb(254 240 138);',
                                default => 'background: rgb(249 250 251); border-color: rgb(229 231 235);',
                            };
                            $textColor = match($sentiment) {
                                'positive' => 'color: rgb(4 120 87);',
                                'negative' => 'color: rgb(185 28 28);',
                                'mixed' => 'color: rgb(161 98 7);',
                                default => 'color: rgb(55 65 81);',
                            };
                            $synonyms = $group['synonyms'] ?? [];
                        @endphp
                        <div class="relative rounded-lg border p-4 transition-all hover:shadow-md" style="{{ $bgColor }}">
                            {{-- Frequency Number - Top Left --}}
                            <div class="absolute top-2 left-2 text-lg font-bold" style="{{ $textColor }}">
                                {{ $group['frequency'] ?? 0 }}
                            </div>

                            {{-- Main Keyword - Center --}}
                            <div class="flex flex-col items-center justify-center min-h-16 text-center">
                                <span class="font-semibold text-lg" style="{{ $textColor }}">
                                    {{ $group['mainKeyword'] ?? '' }}
                                </span>

                                {{-- Synonyms Preview - Below keyword --}}
                                @if(!empty($synonyms))
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                        {{ implode('، ', array_slice($synonyms, 0, 2)) }}
                                        @if(count($synonyms) > 2)
                                            <span>... و{{ count($synonyms) - 2 }} أخرى</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Sentiment Indicator - Bottom Right --}}
                            <div class="absolute bottom-2 right-2">
                                @if($sentiment === 'positive')
                                    <x-heroicon-o-arrow-trending-up class="w-4 h-4" style="color: rgb(5 150 105);" />
                                @elseif($sentiment === 'negative')
                                    <x-heroicon-o-arrow-trending-down class="w-4 h-4" style="color: rgb(220 38 38);" />
                                @elseif($sentiment === 'mixed')
                                    <x-heroicon-o-hashtag class="w-4 h-4" style="color: rgb(202 138 4);" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-hashtag class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">لا توجد كلمات مفتاحية</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Food Items Section --}}
    @if(!empty($data['foodItems']))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-star class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">العناصر الغذائية</h3>
                    <div class="mr-auto px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                        {{ count($data['foodItems'] ?? []) }} صنف
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($data['foodItems'] as $item)
                        @php
                            $sentiment = $item['sentiment'] ?? 'neutral';
                            $bgColor = match($sentiment) {
                                'positive' => 'background: rgb(236 253 245); border-color: rgb(167 243 208);',
                                'negative' => 'background: rgb(254 242 242); border-color: rgb(254 202 202);',
                                'mixed' => 'background: rgb(254 252 232); border-color: rgb(254 240 138);',
                                default => 'background: rgb(249 250 251); border-color: rgb(229 231 235);',
                            };
                            $textColor = match($sentiment) {
                                'positive' => 'color: rgb(4 120 87);',
                                'negative' => 'color: rgb(185 28 28);',
                                'mixed' => 'color: rgb(161 98 7);',
                                default => 'color: rgb(55 65 81);',
                            };
                        @endphp
                        <div class="relative rounded-lg border p-4 transition-all hover:shadow-md" style="{{ $bgColor }}">
                            {{-- Mentions Number - Top Left --}}
                            <div class="absolute top-2 left-2 text-lg font-bold" style="{{ $textColor }}">
                                {{ $item['mentions'] ?? 0 }}
                            </div>

                            {{-- Food Name - Center --}}
                            <div class="flex flex-col items-center justify-center h-16 text-center">
                                <span class="font-semibold text-base" style="{{ $textColor }}">
                                    {{ $item['name'] ?? '' }}
                                </span>
                            </div>

                            {{-- Sentiment Indicator - Bottom Right --}}
                            <div class="absolute bottom-2 right-2">
                                @if($sentiment === 'positive')
                                    <x-heroicon-o-arrow-trending-up class="w-4 h-4" style="color: rgb(5 150 105);" />
                                @elseif($sentiment === 'negative')
                                    <x-heroicon-o-arrow-trending-down class="w-4 h-4" style="color: rgb(220 38 38);" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
