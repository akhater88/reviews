@php
    $data = $card['data'] ?? [];
    $distribution = $data['sentimentDistribution'] ?? ['positive' => 0, 'neutral' => 0, 'negative' => 0];
    $quotes = $data['customerQuotes'] ?? ['positive' => [], 'neutral' => [], 'negative' => []];

    $positive = $distribution['positive'] ?? 0;
    $neutral = $distribution['neutral'] ?? 0;
    $negative = $distribution['negative'] ?? 0;
@endphp

<div class="space-y-6">
    {{-- Sub-section Header --}}
    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">المشاعر العامّة</h3>
    </div>

    {{-- Sentiment Counter Cards --}}
    <div class="grid grid-cols-3 gap-3 sm:gap-5">
        {{-- Positive --}}
        <div class="p-4 sm:p-5 bg-green-50 dark:bg-green-900/20 rounded-xl border-2 {{ $positive >= 50 ? 'border-green-300 dark:border-green-600 shadow-md' : 'border-green-200 dark:border-green-800' }} text-center transition-all">
            <div class="text-2xl sm:text-3xl font-bold text-green-700 dark:text-green-300 mb-1 sm:mb-2">
                {{ $positive }}%
            </div>
            <div class="text-xs sm:text-sm font-medium text-green-600 dark:text-green-400">إيجابي</div>
            @if($positive >= 50)
                <div class="text-xs text-green-500 dark:text-green-400 mt-1 font-medium">مهيمن</div>
            @endif
        </div>

        {{-- Neutral --}}
        <div class="p-4 sm:p-5 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border-2 {{ $neutral >= 50 ? 'border-yellow-300 dark:border-yellow-600 shadow-md' : 'border-yellow-200 dark:border-yellow-800' }} text-center transition-all">
            <div class="text-2xl sm:text-3xl font-bold text-yellow-700 dark:text-yellow-300 mb-1 sm:mb-2">
                {{ $neutral }}%
            </div>
            <div class="text-xs sm:text-sm font-medium text-yellow-600 dark:text-yellow-400">محايد</div>
            @if($neutral >= 50)
                <div class="text-xs text-yellow-500 dark:text-yellow-400 mt-1 font-medium">مهيمن</div>
            @endif
        </div>

        {{-- Negative --}}
        <div class="p-4 sm:p-5 bg-red-50 dark:bg-red-900/20 rounded-xl border-2 {{ $negative >= 50 ? 'border-red-300 dark:border-red-600 shadow-md' : 'border-red-200 dark:border-red-800' }} text-center transition-all">
            <div class="text-2xl sm:text-3xl font-bold text-red-700 dark:text-red-300 mb-1 sm:mb-2">
                {{ $negative }}%
            </div>
            <div class="text-xs sm:text-sm font-medium text-red-600 dark:text-red-400">سلبي</div>
            @if($negative >= 50)
                <div class="text-xs text-red-500 dark:text-red-400 mt-1 font-medium">مهيمن</div>
            @endif
        </div>
    </div>

    {{-- Customer Quotes --}}
    <div class="space-y-4">
        {{-- Positive Quotes --}}
        @if($positive > 0 && count(array_filter($quotes['positive'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs sm:text-sm font-semibold text-green-700 dark:text-green-400 mb-3">أمثلة من المراجعات الإيجابية:</div>
                <div class="space-y-2 sm:space-y-3">
                    @foreach(array_slice(array_filter($quotes['positive'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed border-r-2 border-green-200 dark:border-green-700 pr-3">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Neutral Quotes --}}
        @if($neutral > 0 && count(array_filter($quotes['neutral'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-4 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs sm:text-sm font-medium text-yellow-700 dark:text-yellow-400 mb-3">أمثلة من المراجعات المحايدة:</div>
                <div class="space-y-2">
                    @foreach(array_slice(array_filter($quotes['neutral'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed border-r-2 border-yellow-200 dark:border-yellow-700 pr-3">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Negative Quotes --}}
        @if($negative > 0 && count(array_filter($quotes['negative'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-4 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-xs sm:text-sm font-medium text-red-700 dark:text-red-400 mb-3">أمثلة من المراجعات السلبية:</div>
                <div class="space-y-2">
                    @foreach(array_slice(array_filter($quotes['negative'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed border-r-2 border-red-200 dark:border-red-700 pr-3">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
