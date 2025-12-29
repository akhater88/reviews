<div class="space-y-4" dir="rtl">
    {{-- Demographics Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Gender Cards List --}}
        <div class="p-6 space-y-4">
            @forelse(($data['categories'] ?? []) as $category)
                @php
                    $genderName = $category['category'] ?? '';
                    $percentage = $category['percentage'] ?? 0;
                    $totalReviews = $category['totalReviews'] ?? 0;
                    $avgRating = $category['averageRating'] ?? 0;
                    $positiveCount = $category['positiveCount'] ?? 0;
                    $negativeCount = $category['negativeCount'] ?? 0;
                    $totalAnalyzed = $data['summary']['totalAnalyzed'] ?? 300;

                    // Star color based on rating
                    $starColor = match(true) {
                        $avgRating >= 4 => 'color: rgb(34 197 94);',
                        $avgRating >= 3 => 'color: rgb(234 179 8);',
                        default => 'color: rgb(239 68 68);',
                    };

                    // Colors based on gender
                    $genderColors = match($genderName) {
                        'ذكور' => [
                            'badge' => 'background: rgb(219 234 254); color: rgb(29 78 216);',
                            'icon' => 'background: rgb(59 130 246);',
                        ],
                        'إناث' => [
                            'badge' => 'background: rgb(252 231 243); color: rgb(190 24 93);',
                            'icon' => 'background: rgb(236 72 153);',
                        ],
                        default => [
                            'badge' => 'background: rgb(243 244 246); color: rgb(75 85 99);',
                            'icon' => 'background: rgb(107 114 128);',
                        ],
                    };
                @endphp

                {{-- Gender Card --}}
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                    {{-- Header with Gender Name on Right, Star Rating and Count on Left --}}
                    <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                        <div class="text-right flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="{{ $genderColors['icon'] }}">
                                @if($genderName === 'ذكور')
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                @elseif($genderName === 'إناث')
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                @else
                                    <x-heroicon-o-user class="w-5 h-5 text-white" />
                                @endif
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $genderName }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ number_format($totalReviews) }} مراجعة ({{ number_format($percentage, 1) }}%)
                            </div>
                            <div class="text-xl font-bold flex items-center gap-1" style="{{ $starColor }}">
                                {{ number_format($avgRating, 1) }}
                                <span>⭐</span>
                            </div>
                        </div>
                    </div>

                    {{-- Feedback Count Split --}}
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

                    {{-- Quote Examples --}}
                    <div class="space-y-3">
                        {{-- Positive Quote --}}
                        @if(!empty($category['topPositives']) && is_array($category['topPositives']))
                            @php $firstPositive = collect($category['topPositives'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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
                        @if(!empty($category['topNegatives']) && is_array($category['topNegatives']))
                            @php $firstNegative = collect($category['topNegatives'])->filter(fn($q) => is_string($q) && trim($q))->first(); @endphp
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
                    <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات ديموغرافية</h3>
                    <p class="text-gray-500 dark:text-gray-400">سيتم تحليل البيانات الديموغرافية بعد تحليل المراجعات</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Gender Distribution Summary --}}
    @if(!empty($data['categories']) && count($data['categories']) >= 2)
        @php
            $maleData = collect($data['categories'])->firstWhere('category', 'ذكور');
            $femaleData = collect($data['categories'])->firstWhere('category', 'إناث');
        @endphp
        @if($maleData && $femaleData)
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                {{-- Male Summary --}}
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(239 246 255), rgb(219 234 254)); border-color: rgb(191 219 254);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(59 130 246);">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(29 78 216);">ذكور</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($maleData['percentage'] ?? 0, 1) }}%</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($maleData['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($maleData['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>

                {{-- Female Summary --}}
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(253 242 248), rgb(252 231 243)); border-color: rgb(251 207 232);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(236 72 153);">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(190 24 93);">إناث</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($femaleData['percentage'] ?? 0, 1) }}%</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($femaleData['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($femaleData['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
