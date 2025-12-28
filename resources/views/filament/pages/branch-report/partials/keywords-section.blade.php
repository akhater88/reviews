@php
    $keywordsData = $this->getKeywordsData();
    $keywordGroups = $keywordsData['keywordGroups'] ?? [];
    $foodItems = $keywordsData['foodItems'] ?? [];
@endphp

@if(!empty($keywordGroups) || !empty($foodItems))
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm border border-teal-100 dark:border-teal-800" style="background: linear-gradient(to right, rgb(240 253 250), rgb(236 254 255));">
        <div class="px-5 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">الكلمات المفتاحية</h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl">
        <div class="p-5 sm:p-6 space-y-8">

            {{-- Keyword Groups --}}
            @if(!empty($keywordGroups))
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-o-hashtag class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">الكلمات الأكثر تكراراً</h3>
                        <span class="mr-auto px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                            {{ count($keywordGroups) }} كلمة أساسية
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($keywordGroups as $keyword)
                            @php
                                $sentiment = $keyword['sentiment'] ?? 'neutral';
                                $bgColor = match($sentiment) {
                                    'positive' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
                                    'negative' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                    'mixed' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                                    default => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600',
                                };
                                $textColor = match($sentiment) {
                                    'positive' => 'text-emerald-700 dark:text-emerald-300',
                                    'negative' => 'text-red-700 dark:text-red-300',
                                    'mixed' => 'text-yellow-700 dark:text-yellow-300',
                                    default => 'text-gray-700 dark:text-gray-300',
                                };
                            @endphp

                            <div class="relative rounded-xl border p-4 transition-all hover:shadow-md {{ $bgColor }}">
                                {{-- Frequency Number --}}
                                <div class="absolute top-2 left-2 text-lg font-bold {{ $textColor }}">
                                    {{ $keyword['frequency'] ?? 0 }}
                                </div>

                                {{-- Main Keyword --}}
                                <div class="flex flex-col items-center justify-center min-h-16 text-center">
                                    <span class="font-semibold text-lg {{ $textColor }}">
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

                                {{-- Sentiment Icon --}}
                                <div class="absolute bottom-2 right-2">
                                    @if($sentiment === 'positive')
                                        <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                    @elseif($sentiment === 'negative')
                                        <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    @else
                                        <x-heroicon-o-minus class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                                    @endif
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
                        <x-heroicon-o-star class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">العناصر الغذائية</h3>
                        <span class="mr-auto px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">
                            {{ count($foodItems) }} صنف
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($foodItems as $item)
                            @php
                                $sentiment = $item['sentiment'] ?? 'neutral';
                                $bgColor = match($sentiment) {
                                    'positive' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
                                    'negative' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                    'mixed' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                                    default => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600',
                                };
                                $textColor = match($sentiment) {
                                    'positive' => 'text-emerald-700 dark:text-emerald-300',
                                    'negative' => 'text-red-700 dark:text-red-300',
                                    'mixed' => 'text-yellow-700 dark:text-yellow-300',
                                    default => 'text-gray-700 dark:text-gray-300',
                                };
                            @endphp

                            <div class="relative rounded-xl border p-4 transition-all hover:shadow-md {{ $bgColor }}">
                                {{-- Mentions --}}
                                <div class="absolute top-2 left-2 text-lg font-bold {{ $textColor }}">
                                    {{ $item['mentions'] ?? 0 }}
                                </div>

                                {{-- Food Name --}}
                                <div class="flex flex-col items-center justify-center h-16 text-center">
                                    <span class="font-semibold text-base {{ $textColor }}">
                                        {{ $item['name'] }}
                                    </span>
                                </div>

                                {{-- Sentiment Icon --}}
                                <div class="absolute bottom-2 right-2">
                                    @if($sentiment === 'positive')
                                        <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                    @elseif($sentiment === 'negative')
                                        <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
@endif
