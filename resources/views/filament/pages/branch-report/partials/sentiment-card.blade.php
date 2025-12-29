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

    {{-- Sentiment Counter Cards - 3 columns in one row --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;">
        {{-- Positive --}}
        <div class="p-5 rounded-xl text-center transition-all" style="background: linear-gradient(135deg, rgb(240 253 244), rgb(220 252 231)); border: 2px solid {{ $positive >= 50 ? 'rgb(134 239 172)' : 'rgb(187 247 208)' }}; {{ $positive >= 50 ? 'box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);' : '' }}">
            <div class="w-10 h-10 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                <x-heroicon-o-face-smile class="w-5 h-5 text-white" />
            </div>
            <div class="text-3xl font-bold mb-1" style="color: rgb(21 128 61);">
                {{ $positive }}%
            </div>
            <div class="text-sm font-medium" style="color: rgb(22 163 74);">إيجابي</div>
            @if($positive >= 50)
                <div class="text-xs mt-1 font-semibold px-2 py-0.5 rounded-full inline-block" style="background: rgb(187 247 208); color: rgb(21 128 61);">مهيمن</div>
            @endif
        </div>

        {{-- Neutral --}}
        <div class="p-5 rounded-xl text-center transition-all" style="background: linear-gradient(135deg, rgb(254 252 232), rgb(254 249 195)); border: 2px solid {{ $neutral >= 50 ? 'rgb(250 204 21)' : 'rgb(253 224 71)' }}; {{ $neutral >= 50 ? 'box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);' : '' }}">
            <div class="w-10 h-10 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: rgb(234 179 8);">
                <x-heroicon-o-minus-circle class="w-5 h-5 text-white" />
            </div>
            <div class="text-3xl font-bold mb-1" style="color: rgb(161 98 7);">
                {{ $neutral }}%
            </div>
            <div class="text-sm font-medium" style="color: rgb(202 138 4);">محايد</div>
            @if($neutral >= 50)
                <div class="text-xs mt-1 font-semibold px-2 py-0.5 rounded-full inline-block" style="background: rgb(253 224 71); color: rgb(161 98 7);">مهيمن</div>
            @endif
        </div>

        {{-- Negative --}}
        <div class="p-5 rounded-xl text-center transition-all" style="background: linear-gradient(135deg, rgb(254 242 242), rgb(254 226 226)); border: 2px solid {{ $negative >= 50 ? 'rgb(252 165 165)' : 'rgb(254 202 202)' }}; {{ $negative >= 50 ? 'box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);' : '' }}">
            <div class="w-10 h-10 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background: rgb(239 68 68);">
                <x-heroicon-o-face-frown class="w-5 h-5 text-white" />
            </div>
            <div class="text-3xl font-bold mb-1" style="color: rgb(185 28 28);">
                {{ $negative }}%
            </div>
            <div class="text-sm font-medium" style="color: rgb(220 38 38);">سلبي</div>
            @if($negative >= 50)
                <div class="text-xs mt-1 font-semibold px-2 py-0.5 rounded-full inline-block" style="background: rgb(254 202 202); color: rgb(185 28 28);">مهيمن</div>
            @endif
        </div>
    </div>

    {{-- Customer Quotes --}}
    <div class="space-y-4">
        {{-- Positive Quotes --}}
        @if($positive > 0 && count(array_filter($quotes['positive'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-5 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-semibold mb-3" style="color: rgb(21 128 61);">أمثلة من المراجعات الإيجابية:</div>
                <div class="space-y-3">
                    @foreach(array_slice(array_filter($quotes['positive'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed pr-3" style="border-right: 3px solid rgb(134 239 172);">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Neutral Quotes --}}
        @if($neutral > 0 && count(array_filter($quotes['neutral'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-5 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-semibold mb-3" style="color: rgb(161 98 7);">أمثلة من المراجعات المحايدة:</div>
                <div class="space-y-3">
                    @foreach(array_slice(array_filter($quotes['neutral'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed pr-3" style="border-right: 3px solid rgb(253 224 71);">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Negative Quotes --}}
        @if($negative > 0 && count(array_filter($quotes['negative'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3)))
            <div class="p-5 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-sm font-semibold mb-3" style="color: rgb(185 28 28);">أمثلة من المراجعات السلبية:</div>
                <div class="space-y-3">
                    @foreach(array_slice(array_filter($quotes['negative'] ?? [], fn($q) => trim($q) && strlen(trim($q)) > 3), 0, 3) as $quote)
                        <div class="text-sm text-gray-600 dark:text-gray-300 italic leading-relaxed pr-3" style="border-right: 3px solid rgb(252 165 165);">
                            "{{ trim($quote) }}"
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
