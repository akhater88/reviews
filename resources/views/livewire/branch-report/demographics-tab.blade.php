<div class="space-y-4" dir="rtl">
    {{-- Demographics Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Gender Cards Grid - 3 columns --}}
        <div class="p-6">
            @if(!empty($data['categories']))
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    @foreach(($data['categories'] ?? []) as $category)
                        @php
                            $genderName = $category['category'] ?? '';
                            $percentage = $category['percentage'] ?? 0;
                            $totalReviews = $category['totalReviews'] ?? 0;
                            $avgRating = $category['averageRating'] ?? 0;
                            $positiveCount = $category['positiveCount'] ?? 0;
                            $negativeCount = $category['negativeCount'] ?? 0;

                            // Star color based on rating
                            $starColor = match(true) {
                                $avgRating >= 4 => 'color: rgb(34 197 94);',
                                $avgRating >= 3 => 'color: rgb(234 179 8);',
                                default => 'color: rgb(239 68 68);',
                            };

                            // Colors based on gender
                            $genderColors = match($genderName) {
                                'ذكور' => [
                                    'bg' => 'background: linear-gradient(to bottom right, rgb(239 246 255), rgb(219 234 254)); border-color: rgb(191 219 254);',
                                    'icon' => 'background: rgb(59 130 246);',
                                    'text' => 'color: rgb(29 78 216);',
                                ],
                                'إناث' => [
                                    'bg' => 'background: linear-gradient(to bottom right, rgb(253 242 248), rgb(252 231 243)); border-color: rgb(251 207 232);',
                                    'icon' => 'background: rgb(236 72 153);',
                                    'text' => 'color: rgb(190 24 93);',
                                ],
                                default => [
                                    'bg' => 'background: linear-gradient(to bottom right, rgb(249 250 251), rgb(243 244 246)); border-color: rgb(229 231 235);',
                                    'icon' => 'background: rgb(107 114 128);',
                                    'text' => 'color: rgb(75 85 99);',
                                ],
                            };
                        @endphp

                        {{-- Gender Card --}}
                        <div class="rounded-xl border" style="padding: 1.25rem; {{ $genderColors['bg'] }}">
                            {{-- Header with Icon and Name --}}
                            <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="{{ $genderColors['icon'] }}">
                                    @if($genderName === 'ذكور')
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                    @elseif($genderName === 'إناث')
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                    @else
                                        <x-heroicon-o-users class="w-6 h-6 text-white" />
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $genderName }}</h3>
                                    <p class="text-sm" style="{{ $genderColors['text'] }}">{{ number_format($percentage, 1) }}%</p>
                                </div>
                            </div>

                            {{-- Rating --}}
                            <div class="flex items-center gap-2" style="margin-bottom: 1rem;">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                    @endfor
                                </div>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($avgRating, 1) }}</span>
                            </div>

                            {{-- Stats --}}
                            <div class="text-sm text-gray-600 dark:text-gray-400" style="margin-bottom: 0.75rem;">
                                {{ number_format($totalReviews) }} مراجعة
                            </div>

                            {{-- Feedback Count Split --}}
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                                <div class="text-center rounded-lg" style="padding: 0.5rem; background: rgb(240 253 244);">
                                    <div class="text-lg font-bold" style="color: rgb(22 163 74);">
                                        {{ number_format($positiveCount) }}
                                    </div>
                                    <div class="text-xs" style="color: rgb(21 128 61);">إيجابي</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.5rem; background: rgb(254 242 242);">
                                    <div class="text-lg font-bold" style="color: rgb(220 38 38);">
                                        {{ number_format($negativeCount) }}
                                    </div>
                                    <div class="text-xs" style="color: rgb(185 28 28);">سلبي</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-users class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات ديموغرافية</h3>
                    <p class="text-gray-500 dark:text-gray-400">سيتم تحليل البيانات الديموغرافية بعد تحليل المراجعات</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Category Details Sections --}}
    @if(!empty($data['categories']))
        @foreach(($data['categories'] ?? []) as $category)
            @php
                $genderName = $category['category'] ?? '';
                $percentage = $category['percentage'] ?? 0;
                $avgRating = $category['averageRating'] ?? 0;
                $positiveCount = $category['positiveCount'] ?? 0;
                $topPositives = $category['topPositives'] ?? [];
                $topNegatives = $category['topNegatives'] ?? [];

                // Colors based on gender
                $headerColor = match($genderName) {
                    'ذكور' => 'color: rgb(29 78 216);',
                    'إناث' => 'color: rgb(190 24 93);',
                    default => 'color: rgb(75 85 99);',
                };
            @endphp

            <div class="rounded-xl overflow-hidden" style="background: linear-gradient(to bottom right, rgb(250 245 255), rgb(245 243 255)); border: 1px solid rgb(233 213 255);">
                {{-- Section Header --}}
                <div class="text-left p-4">
                    <span class="text-sm text-gray-500">تفاصيل فئة:</span>
                    <span class="text-lg font-bold" style="{{ $headerColor }}">{{ $genderName }}</span>
                </div>

                {{-- Positives & Negatives Cards --}}
                <div class="px-4 pb-4">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        {{-- Positives Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl" style="padding: 1.25rem;">
                            <div class="flex items-center gap-2" style="margin-bottom: 1rem;">
                                <x-heroicon-s-hand-thumb-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                <h4 class="font-bold text-gray-900 dark:text-white">أبرز الإيجابيات</h4>
                            </div>
                            <div class="space-y-3">
                                @forelse(collect($topPositives)->filter(fn($q) => is_string($q) && trim($q))->take(4) as $quote)
                                    <div class="flex gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: rgb(34 197 94); margin-top: 0.5rem;"></span>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">"{{ trim($quote) }}"</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">لا توجد تعليقات إيجابية</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Negatives Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl" style="padding: 1.25rem;">
                            <div class="flex items-center gap-2" style="margin-bottom: 1rem;">
                                <x-heroicon-s-hand-thumb-down class="w-5 h-5" style="color: rgb(239 68 68);" />
                                <h4 class="font-bold text-gray-900 dark:text-white">أبرز السلبيات</h4>
                            </div>
                            <div class="space-y-3">
                                @forelse(collect($topNegatives)->filter(fn($q) => is_string($q) && trim($q))->take(4) as $quote)
                                    <div class="flex gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: rgb(239 68 68); margin-top: 0.5rem;"></span>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">"{{ trim($quote) }}"</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">لا توجد تعليقات سلبية</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="border-t" style="border-color: rgb(233 213 255);">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr);">
                        {{-- Positives Count --}}
                        <div class="text-center py-4 border-l" style="border-color: rgb(233 213 255);">
                            <div class="text-2xl font-bold" style="color: rgb(34 197 94);">{{ number_format($positiveCount) }}</div>
                            <div class="text-sm text-gray-500">إيجابيات</div>
                        </div>

                        {{-- Rating --}}
                        <div class="text-center py-4 border-l" style="border-color: rgb(233 213 255);">
                            <div class="text-2xl font-bold flex items-center justify-center gap-1">
                                <span style="color: rgb(234 179 8);">{{ number_format($avgRating, 1) }}</span>
                                <x-heroicon-o-star class="w-5 h-5" style="color: rgb(234 179 8);" />
                            </div>
                            <div class="text-sm text-gray-500">التقييم</div>
                        </div>

                        {{-- Percentage --}}
                        <div class="text-center py-4">
                            <div class="text-2xl font-bold" style="color: rgb(168 85 247);">{{ number_format($percentage, 2) }}%</div>
                            <div class="text-sm text-gray-500">النسبة</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
