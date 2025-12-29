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
</div>
