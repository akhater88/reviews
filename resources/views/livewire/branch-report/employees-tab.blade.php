<div class="space-y-6" dir="rtl">
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm border border-purple-100 dark:border-purple-800" style="background: linear-gradient(to right, rgb(250 245 255), rgb(245 243 255));">
        <div class="px-5 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">الموظفين</h2>
            @php
                $employeeCount = count($data['performance'] ?? []);
            @endphp
            @if($employeeCount > 0)
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-600">
                    {{ $employeeCount }} موظفين
                </span>
            @endif
        </div>
    </div>

    {{-- Tab Toggle --}}
    <div class="flex gap-2 p-1 bg-gray-100 dark:bg-gray-700 rounded-lg w-fit">
        <button
            wire:click="setActiveView('overview')"
            class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $activeView === 'overview' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
        >
            نظرة عامة
        </button>
        <button
            wire:click="setActiveView('performance')"
            class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $activeView === 'performance' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
        >
            الأداء
        </button>
    </div>

    @if($activeView === 'overview')
        {{-- Overview Cards --}}
        @if(!empty($data['overview']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Most Positive Employee --}}
                @if(!empty($data['overview']['mostPositiveEmployee']))
                    @php $employee = $data['overview']['mostPositiveEmployee']; @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-green-200 dark:border-green-700 overflow-hidden shadow-sm {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-green-500' : '' }}">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-user class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-green-500" />
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                    </div>
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full">
                                        الموظف الأكثر إيجابية
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">مرات الذكر</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $employee['totalMentions'] ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 5, 0) }}</span>
                                        <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                                <div class="h-full bg-green-500 rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 5) * 20) }}%"></div>
                            </div>

                            <button
                                wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                class="w-full text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'عرض التفاصيل' }}
                            </button>
                        </div>

                        {{-- Expanded Details --}}
                        @if($expandedEmployee === $employee['name'])
                            <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-5">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ mb_substr($employee['name'], 0, 1) }}</span>
                                    </div>
                                    تفاصيل {{ $employee['name'] }}
                                </h4>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center gap-2 mb-2">
                                            <x-heroicon-o-hand-thumb-up class="w-4 h-4 text-green-500" />
                                            <span class="text-xs font-semibold text-green-600 dark:text-green-400">أبرز الإيجابيات</span>
                                        </div>
                                        @if(!empty($employee['positiveKeywords']))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($employee['positiveKeywords'], 0, 3) as $keyword)
                                                    <span class="text-xs px-2 py-1 bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300 rounded">{{ $keyword }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-500">خدمة ممتازة، تعامل راقي</p>
                                        @endif
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center gap-2 mb-2">
                                            <x-heroicon-o-hand-thumb-down class="w-4 h-4 text-red-500" />
                                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">نقاط التحسين</span>
                                        </div>
                                        @if(!empty($employee['negativeKeywords']))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($employee['negativeKeywords'], 0, 3) as $keyword)
                                                    <span class="text-xs px-2 py-1 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 rounded">{{ $keyword }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-500">لا توجد نقاط تحسين مسجلة</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['totalMentions'] ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">مرات الذكر</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 5, 0) }}</div>
                                        <div class="text-xs text-gray-500">التقييم</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-green-600">{{ $employee['positiveCount'] ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">إيجابيات</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-red-600">{{ $employee['negativeCount'] ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">تحسينات</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Most Mentioned Employee --}}
                @if(!empty($data['overview']['mostMentionedEmployee']))
                    @php $employee = $data['overview']['mostMentionedEmployee']; @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-purple-200 dark:border-purple-700 overflow-hidden shadow-sm {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-purple-500' : '' }}">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-user class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                    <span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded-full">
                                        الموظف الأكثر ذكراً
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">مرات الذكر</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $employee['totalMentions'] ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 5, 0) }}</span>
                                        <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                    </div>
                                </div>
                            </div>

                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                                <div class="h-full bg-purple-500 rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 5) * 20) }}%"></div>
                            </div>

                            <button
                                wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                class="w-full text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'عرض التفاصيل' }}
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Most Negative Employee --}}
                @if(!empty($data['overview']['mostNegativeEmployee']))
                    @php $employee = $data['overview']['mostNegativeEmployee']; @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-red-200 dark:border-red-700 overflow-hidden shadow-sm {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-red-500' : '' }}">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900/30 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-user class="w-6 h-6 text-pink-600 dark:text-pink-400" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-500" />
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $employee['name'] }}</h3>
                                    </div>
                                    <span class="text-xs px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 rounded-full">
                                        يحتاج تطوير
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">مرات الذكر</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $employee['totalMentions'] ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 0, 1) }}</span>
                                        <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                    </div>
                                </div>
                            </div>

                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                                <div class="h-full bg-red-500 rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 0) * 20) }}%"></div>
                            </div>

                            <button
                                wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                class="w-full text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                            >
                                {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'عرض التفاصيل' }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @else
        {{-- Performance Table View --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">أداء الموظفين</h3>
            </div>

            @if(!empty($data['performance']))
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الموظف</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">عدد الذكر</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">التقييم</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الاتجاه</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($data['performance'] as $employee)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                    {{ mb_substr($employee['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $employee['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-900 dark:text-white">
                                        {{ $employee['totalMentions'] ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($employee['averageRating'] ?? 0, 1) }}</span>
                                            <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $trend = $employee['performanceNote'] ?? 'stable';
                                            $trendIcon = match($trend) {
                                                'up' => 'heroicon-o-arrow-trending-up',
                                                'down' => 'heroicon-o-arrow-trending-down',
                                                default => 'heroicon-o-minus',
                                            };
                                            $trendColor = match($trend) {
                                                'up' => 'text-green-600 bg-green-100 dark:bg-green-900/30',
                                                'down' => 'text-red-600 bg-red-100 dark:bg-red-900/30',
                                                default => 'text-gray-600 bg-gray-100 dark:bg-gray-700',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $trendColor }}">
                                            <x-dynamic-component :component="$trendIcon" class="w-4 h-4" />
                                            {{ match($trend) { 'up' => 'متحسن', 'down' => 'يحتاج تحسين', default => 'مستقر' } }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">لم يتم ذكر أي موظفين في المراجعات</p>
                </div>
            @endif
        </div>
    @endif

    {{-- No Data State --}}
    @if(empty($data['overview']) && empty($data['performance']))
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
            <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات موظفين</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم ذكر أي موظفين في المراجعات المحللة</p>
        </div>
    @endif
</div>
