<div class="space-y-6" dir="rtl">
    {{-- Keyword Groups Grid --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-hashtag class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الكلمات الأكثر تكراراً</h3>
                </div>
                <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                    {{ count($data['keywordGroups'] ?? []) }} كلمة أساسية
                </div>
            </div>
        </div>
        <div class="p-4">
            @if(!empty($data['keywordGroups']))
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    @foreach($data['keywordGroups'] as $group)
                        @php
                            $sentiment = $group['sentiment'] ?? 'neutral';
                            $bgColor = match($sentiment) {
                                'positive' => 'background: rgb(240 253 244); border-right: 4px solid rgb(34 197 94);',
                                'negative' => 'background: rgb(254 242 242); border-right: 4px solid rgb(239 68 68);',
                                'mixed' => 'background: rgb(254 252 232); border-right: 4px solid rgb(234 179 8);',
                                default => 'background: rgb(249 250 251); border-right: 4px solid rgb(156 163 175);',
                            };
                            $textColor = match($sentiment) {
                                'positive' => 'color: rgb(21 128 61);',
                                'negative' => 'color: rgb(185 28 28);',
                                'mixed' => 'color: rgb(161 98 7);',
                                default => 'color: rgb(55 65 81);',
                            };
                            $iconColor = match($sentiment) {
                                'positive' => 'color: rgb(34 197 94);',
                                'negative' => 'color: rgb(239 68 68);',
                                'mixed' => 'color: rgb(234 179 8);',
                                default => 'color: rgb(156 163 175);',
                            };
                            $synonyms = $group['synonyms'] ?? [];
                        @endphp
                        <div class="relative rounded-lg p-4" style="{{ $bgColor }}">
                            {{-- Frequency Number - Top Right (RTL) --}}
                            <div class="absolute top-3 right-3 text-xl font-bold" style="{{ $textColor }}">
                                {{ $group['frequency'] ?? 0 }}
                            </div>

                            {{-- Main Keyword - Center --}}
                            <div class="flex flex-col items-center justify-center text-center" style="min-height: 80px; padding-top: 1rem;">
                                <span class="font-bold text-lg" style="{{ $textColor }}">
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

                            {{-- Sentiment Indicator - Bottom Left (RTL) --}}
                            <div class="absolute bottom-3 left-3">
                                @if($sentiment === 'positive')
                                    <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="{{ $iconColor }}" />
                                @elseif($sentiment === 'negative')
                                    <x-heroicon-o-arrow-trending-down class="w-5 h-5" style="{{ $iconColor }}" />
                                @elseif($sentiment === 'mixed')
                                    <x-heroicon-o-minus class="w-5 h-5" style="{{ $iconColor }}" />
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
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-star class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">العناصر الغذائية</h3>
                    </div>
                    <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                        {{ count($data['foodItems'] ?? []) }} صنف
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    @foreach($data['foodItems'] as $item)
                        @php
                            $sentiment = $item['sentiment'] ?? 'neutral';
                            $bgColor = match($sentiment) {
                                'positive' => 'background: rgb(240 253 244); border-right: 4px solid rgb(34 197 94);',
                                'negative' => 'background: rgb(254 242 242); border-right: 4px solid rgb(239 68 68);',
                                'mixed' => 'background: rgb(254 252 232); border-right: 4px solid rgb(234 179 8);',
                                default => 'background: rgb(249 250 251); border-right: 4px solid rgb(156 163 175);',
                            };
                            $textColor = match($sentiment) {
                                'positive' => 'color: rgb(21 128 61);',
                                'negative' => 'color: rgb(185 28 28);',
                                'mixed' => 'color: rgb(161 98 7);',
                                default => 'color: rgb(55 65 81);',
                            };
                            $iconColor = match($sentiment) {
                                'positive' => 'color: rgb(34 197 94);',
                                'negative' => 'color: rgb(239 68 68);',
                                'mixed' => 'color: rgb(234 179 8);',
                                default => 'color: rgb(156 163 175);',
                            };
                        @endphp
                        <div class="relative rounded-lg p-4" style="{{ $bgColor }}">
                            {{-- Mentions Number - Top Right (RTL) --}}
                            <div class="absolute top-3 right-3 text-xl font-bold" style="{{ $textColor }}">
                                {{ $item['mentions'] ?? 0 }}
                            </div>

                            {{-- Food Name - Center --}}
                            <div class="flex flex-col items-center justify-center text-center" style="min-height: 60px; padding-top: 1rem;">
                                <span class="font-bold text-base" style="{{ $textColor }}">
                                    {{ $item['name'] ?? '' }}
                                </span>
                            </div>

                            {{-- Sentiment Indicator - Bottom Left (RTL) --}}
                            <div class="absolute bottom-3 left-3">
                                @if($sentiment === 'positive')
                                    <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="{{ $iconColor }}" />
                                @elseif($sentiment === 'negative')
                                    <x-heroicon-o-arrow-trending-down class="w-5 h-5" style="{{ $iconColor }}" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
