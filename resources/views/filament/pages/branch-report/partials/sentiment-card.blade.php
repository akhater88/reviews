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
        <div class="p-4 sm:p-5 rounded-xl text-center transition-all {{ $positive >= 50 ? 'shadow-md' : '' }}" style="background-color: rgb(240 253 244); border: 2px solid {{ $positive >= 50 ? 'rgb(134 239 172)' : 'rgb(187 247 208)' }};">
            <div class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2" style="color: rgb(21 128 61);">
                {{ $positive }}%
            </div>
            <div class="text-xs sm:text-sm font-medium" style="color: rgb(22 163 74);">إيجابي</div>
            @if($positive >= 50)
                <div class="text-xs mt-1 font-medium" style="color: rgb(34 197 94);">مهيمن</div>
            @endif
        </div>

        {{-- Neutral --}}
        <div class="p-4 sm:p-5 rounded-xl text-center transition-all {{ $neutral >= 50 ? 'shadow-md' : '' }}" style="background-color: rgb(254 252 232); border: 2px solid {{ $neutral >= 50 ? 'rgb(250 204 21)' : 'rgb(253 224 71)' }};">
            <div class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2" style="color: rgb(161 98 7);">
                {{ $neutral }}%
            </div>
            <div class="text-xs sm:text-sm font-medium" style="color: rgb(202 138 4);">محايد</div>
            @if($neutral >= 50)
                <div class="text-xs mt-1 font-medium" style="color: rgb(234 179 8);">مهيمن</div>
            @endif
        </div>

        {{-- Negative --}}
        <div class="p-4 sm:p-5 rounded-xl text-center transition-all {{ $negative >= 50 ? 'shadow-md' : '' }}" style="background-color: rgb(254 242 242); border: 2px solid {{ $negative >= 50 ? 'rgb(252 165 165)' : 'rgb(254 202 202)' }};">
            <div class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2" style="color: rgb(185 28 28);">
                {{ $negative }}%
            </div>
            <div class="text-xs sm:text-sm font-medium" style="color: rgb(220 38 38);">سلبي</div>
            @if($negative >= 50)
                <div class="text-xs mt-1 font-medium" style="color: rgb(239 68 68);">مهيمن</div>
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
