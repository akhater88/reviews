<div class="space-y-4" dir="rtl">
    {{-- Employees Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Tab Toggle --}}
        <div class="border-b border-gray-100 dark:border-gray-700">
            <div class="grid grid-cols-2">
                <button
                    wire:click="setActiveView('performance')"
                    class="py-4 text-center text-sm font-medium transition-colors {{ $activeView === 'performance' ? 'text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                >
                    الأداء
                </button>
                <button
                    wire:click="setActiveView('overview')"
                    class="py-4 text-center text-sm font-medium transition-colors {{ $activeView === 'overview' ? 'text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                >
                    نظرة عامة
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 space-y-4">
            @if($activeView === 'overview')
                {{-- Overview Cards --}}
                @if(!empty($data['overview']))
                    {{-- Most Positive Employee --}}
                    @if(!empty($data['overview']['mostPositiveEmployee']))
                        @php $employee = $data['overview']['mostPositiveEmployee']; @endphp
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                            {{-- Header --}}
                            <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background: rgb(34 197 94);">
                                        {{ mb_substr($employee['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                        <span class="text-xs" style="color: rgb(34 197 94);">الموظف الأكثر إيجابية</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee['totalMentions'] ?? 0 }} ذكر
                                    </div>
                                    <div class="text-xl font-bold flex items-center gap-1" style="color: rgb(34 197 94);">
                                        {{ number_format($employee['averageRating'] ?? 5, 1) }}
                                        <span>⭐</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Feedback Count Split --}}
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                                    <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                        {{ $employee['positiveCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                                    <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                        {{ $employee['negativeCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                                </div>
                            </div>

                            {{-- Quote Examples --}}
                            <div class="space-y-3">
                                @if(!empty($employee['positiveKeywords']))
                                    <div class="rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244); border-right: 4px solid rgb(74 222 128);">
                                        <div class="text-xs font-medium" style="color: rgb(22 163 74); margin-bottom: 0.25rem;">
                                            أبرز الكلمات الإيجابية:
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(array_slice($employee['positiveKeywords'], 0, 3) as $keyword)
                                                <span class="text-sm px-2 py-0.5 rounded-full" style="background: rgb(187 247 208); color: rgb(21 128 61);">{{ $keyword }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Most Mentioned Employee --}}
                    @if(!empty($data['overview']['mostMentionedEmployee']))
                        @php $employee = $data['overview']['mostMentionedEmployee']; @endphp
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                            {{-- Header --}}
                            <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background: rgb(168 85 247);">
                                        {{ mb_substr($employee['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                        <span class="text-xs" style="color: rgb(168 85 247);">الموظف الأكثر ذكراً</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee['totalMentions'] ?? 0 }} ذكر
                                    </div>
                                    <div class="text-xl font-bold flex items-center gap-1" style="color: rgb(168 85 247);">
                                        {{ number_format($employee['averageRating'] ?? 5, 1) }}
                                        <span>⭐</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Feedback Count Split --}}
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                                    <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                        {{ $employee['positiveCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                                    <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                        {{ $employee['negativeCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                                </div>
                            </div>

                            {{-- Quote Examples --}}
                            <div class="space-y-3">
                                @if(!empty($employee['positiveKeywords']))
                                    <div class="rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244); border-right: 4px solid rgb(74 222 128);">
                                        <div class="text-xs font-medium" style="color: rgb(22 163 74); margin-bottom: 0.25rem;">
                                            أبرز الكلمات الإيجابية:
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(array_slice($employee['positiveKeywords'], 0, 3) as $keyword)
                                                <span class="text-sm px-2 py-0.5 rounded-full" style="background: rgb(187 247 208); color: rgb(21 128 61);">{{ $keyword }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Most Negative Employee --}}
                    @if(!empty($data['overview']['mostNegativeEmployee']))
                        @php $employee = $data['overview']['mostNegativeEmployee']; @endphp
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                            {{-- Header --}}
                            <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background: rgb(239 68 68);">
                                        {{ mb_substr($employee['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                        <span class="text-xs" style="color: rgb(239 68 68);">يحتاج تطوير</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee['totalMentions'] ?? 0 }} ذكر
                                    </div>
                                    <div class="text-xl font-bold flex items-center gap-1" style="color: rgb(239 68 68);">
                                        {{ number_format($employee['averageRating'] ?? 0, 1) }}
                                        <span>⭐</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Feedback Count Split --}}
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                                    <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                        {{ $employee['positiveCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(21 128 61);">تعليقات إيجابية</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                                    <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                        {{ $employee['negativeCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(185 28 28);">تعليقات سلبية</div>
                                </div>
                            </div>

                            {{-- Quote Examples --}}
                            <div class="space-y-3">
                                @if(!empty($employee['negativeKeywords']))
                                    <div class="rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242); border-right: 4px solid rgb(248 113 113);">
                                        <div class="text-xs font-medium" style="color: rgb(220 38 38); margin-bottom: 0.25rem;">
                                            نقاط التحسين:
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(array_slice($employee['negativeKeywords'], 0, 3) as $keyword)
                                                <span class="text-sm px-2 py-0.5 rounded-full" style="background: rgb(254 202 202); color: rgb(185 28 28);">{{ $keyword }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">لا توجد بيانات موظفين</p>
                    </div>
                @endif
            @else
                {{-- Performance Table View --}}
                @if(!empty($data['performance']))
                    @foreach($data['performance'] as $employee)
                        @php
                            $rating = $employee['averageRating'] ?? 0;
                            $starColor = match(true) {
                                $rating >= 4 => 'color: rgb(34 197 94);',
                                $rating >= 3 => 'color: rgb(234 179 8);',
                                default => 'color: rgb(239 68 68);',
                            };
                            $trend = $employee['performanceNote'] ?? 'stable';
                        @endphp
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" style="padding: 1rem;">
                            {{-- Header --}}
                            <div class="flex items-center justify-between" style="margin-bottom: 1rem;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background: rgb(99 102 241);">
                                        {{ mb_substr($employee['name'], 0, 1) }}
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $employee['totalMentions'] ?? 0 }} ذكر
                                    </div>
                                    <div class="text-xl font-bold flex items-center gap-1" style="{{ $starColor }}">
                                        {{ number_format($rating, 1) }}
                                        <span>⭐</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats Row --}}
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(240 253 244);">
                                    <div class="text-2xl font-bold" style="color: rgb(22 163 74);">
                                        {{ $employee['positiveCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(21 128 61);">إيجابيات</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(254 242 242);">
                                    <div class="text-2xl font-bold" style="color: rgb(220 38 38);">
                                        {{ $employee['negativeCount'] ?? 0 }}
                                    </div>
                                    <div class="text-sm" style="color: rgb(185 28 28);">سلبيات</div>
                                </div>
                                <div class="text-center rounded-lg" style="padding: 0.75rem; background: rgb(243 244 246);">
                                    @if($trend === 'up')
                                        <div class="flex items-center justify-center gap-1">
                                            <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                            <span class="text-lg font-bold" style="color: rgb(34 197 94);">متحسن</span>
                                        </div>
                                    @elseif($trend === 'down')
                                        <div class="flex items-center justify-center gap-1">
                                            <x-heroicon-o-arrow-trending-down class="w-5 h-5" style="color: rgb(239 68 68);" />
                                            <span class="text-lg font-bold" style="color: rgb(239 68 68);">يحتاج تحسين</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-1">
                                            <x-heroicon-o-minus class="w-5 h-5 text-gray-500" />
                                            <span class="text-lg font-bold text-gray-600">مستقر</span>
                                        </div>
                                    @endif
                                    <div class="text-sm text-gray-500">الاتجاه</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">لم يتم ذكر أي موظفين في المراجعات</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Best & Needs Improvement Summary --}}
    @if(!empty($data['overview']['mostPositiveEmployee']) || !empty($data['overview']['mostNegativeEmployee']))
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            {{-- Best Employee --}}
            @if(!empty($data['overview']['mostPositiveEmployee']))
                @php $employee = $data['overview']['mostPositiveEmployee']; @endphp
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(240 253 244), rgb(220 252 231)); border-color: rgb(187 247 208);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(34 197 94);">
                            <x-heroicon-o-trophy class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(22 163 74);">أفضل موظف</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($employee['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>
            @endif

            {{-- Needs Improvement --}}
            @if(!empty($data['overview']['mostNegativeEmployee']))
                @php $employee = $data['overview']['mostNegativeEmployee']; @endphp
                <div class="rounded-xl border" style="padding: 1.5rem; background: linear-gradient(to bottom right, rgb(254 242 242), rgb(254 226 226)); border-color: rgb(254 202 202);">
                    <div class="flex items-center gap-3" style="margin-bottom: 1rem;">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: rgb(239 68 68);">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm" style="color: rgb(220 38 38);">يحتاج تطوير</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($employee['averageRating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" />
                            @endfor
                        </div>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 0, 1) }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
