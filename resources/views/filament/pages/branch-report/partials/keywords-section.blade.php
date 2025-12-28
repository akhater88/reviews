@php
    $keywordsData = $this->getKeywordsData();
    $keywordGroups = $keywordsData['keywordGroups'] ?? [];
    $foodItems = $keywordsData['foodItems'] ?? [];
@endphp

@if(!empty($keywordGroups) || !empty($foodItems))
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm" style="background: linear-gradient(to right, rgb(240 253 250), rgb(236 254 255)); border: 1px solid rgb(204 251 241);">
        <div class="px-5 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">الكلمات المفتاحية والعناصر الغذائية</h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl">
        <div class="p-5 sm:p-6 space-y-8">

            {{-- Keyword Groups --}}
            @if(!empty($keywordGroups))
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-lg font-bold text-gray-600 dark:text-gray-400">#</span>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الكلمات الأكثر تكراراً</h3>
                        <span class="mr-auto px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">
                            {{ count($keywordGroups) }} كلمة أساسية
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($keywordGroups as $keyword)
                            @php
                                $sentiment = $keyword['sentiment'] ?? 'neutral';
                                $bgStyle = match($sentiment) {
                                    'positive' => 'background-color: rgb(220 252 231); border-color: rgb(187 247 208);',
                                    'negative' => 'background-color: rgb(254 226 226); border-color: rgb(254 202 202);',
                                    'mixed' => 'background-color: rgb(254 249 195); border-color: rgb(253 224 71);',
                                    default => 'background-color: rgb(249 250 251); border-color: rgb(229 231 235);',
                                };
                                $textStyle = match($sentiment) {
                                    'positive' => 'color: rgb(21 128 61);',
                                    'negative' => 'color: rgb(185 28 28);',
                                    'mixed' => 'color: rgb(161 98 7);',
                                    default => 'color: rgb(55 65 81);',
                                };
                            @endphp

                            <div class="relative rounded-xl p-4 transition-all hover:shadow-md" style="{{ $bgStyle }} border-width: 1px; border-style: solid;">
                                {{-- Frequency Number --}}
                                <div class="absolute top-3 left-3 text-lg font-bold" style="{{ $textStyle }}">
                                    {{ $keyword['frequency'] ?? 0 }}
                                </div>

                                {{-- Main Keyword --}}
                                <div class="flex flex-col items-center justify-center min-h-20 text-center px-2 py-3">
                                    <span class="font-semibold text-lg" style="{{ $textStyle }}">
                                        {{ $keyword['mainKeyword'] }}
                                    </span>

                                    {{-- Synonyms Preview --}}
                                    @if(!empty($keyword['synonyms']))
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                            {{ implode('، ', array_slice($keyword['synonyms'], 0, 2)) }}
                                            @if(count($keyword['synonyms']) > 2)
                                                <span>... و{{ count($keyword['synonyms']) - 2 }} أخرى</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Expand Icon --}}
                                <div class="absolute bottom-3 left-3">
                                    <x-heroicon-o-arrows-pointing-out class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Food Items --}}
            @if(!empty($foodItems))
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-o-cube class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">العناصر الغذائية</h3>
                        <span class="mr-auto px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">
                            {{ count($foodItems) }} صنف
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($foodItems as $item)
                            @php
                                $sentiment = $item['sentiment'] ?? 'positive';
                                $bgStyle = match($sentiment) {
                                    'positive' => 'background-color: rgb(220 252 231); border-color: rgb(187 247 208);',
                                    'negative' => 'background-color: rgb(254 226 226); border-color: rgb(254 202 202);',
                                    'mixed' => 'background-color: rgb(254 249 195); border-color: rgb(253 224 71);',
                                    default => 'background-color: rgb(220 252 231); border-color: rgb(187 247 208);',
                                };
                                $textStyle = match($sentiment) {
                                    'positive' => 'color: rgb(21 128 61);',
                                    'negative' => 'color: rgb(185 28 28);',
                                    'mixed' => 'color: rgb(161 98 7);',
                                    default => 'color: rgb(21 128 61);',
                                };
                            @endphp

                            <div class="relative rounded-xl p-4 transition-all hover:shadow-md" style="{{ $bgStyle }} border-width: 1px; border-style: solid;">
                                {{-- Mentions --}}
                                <div class="absolute top-3 left-3 text-lg font-bold" style="{{ $textStyle }}">
                                    {{ $item['mentions'] ?? 0 }}
                                </div>

                                {{-- Food Name --}}
                                <div class="flex flex-col items-center justify-center h-16 text-center">
                                    <span class="font-semibold text-base" style="{{ $textStyle }}">
                                        {{ $item['name'] }}
                                    </span>
                                </div>

                                {{-- Expand Icon --}}
                                <div class="absolute bottom-3 left-3">
                                    <x-heroicon-o-arrows-pointing-out class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
@endif
