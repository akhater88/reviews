<div class="space-y-6" dir="rtl">
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm" style="background: linear-gradient(to right, rgb(250 245 255), rgb(245 243 255)); border: 1px solid rgb(233 213 255);">
        <div class="px-5 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">الموظفين</h2>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden">
        {{-- Header with Icon and Badge --}}
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgb(99 102 241);">
                        <x-heroicon-o-user-group class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">الموظفين</h3>
                </div>
                @php
                    $employeeCount = count($data['performance'] ?? []);
                @endphp
                @if($employeeCount > 0)
                    <span class="px-3 py-1 text-sm font-medium rounded-full" style="background: rgb(238 242 255); color: rgb(99 102 241);">
                        {{ $employeeCount }} موظفين
                    </span>
                @endif
            </div>
        </div>

        {{-- Tab Toggle --}}
        <div class="border-b border-gray-100 dark:border-gray-700">
            <div class="grid grid-cols-2">
                <button
                    wire:click="setActiveView('performance')"
                    class="py-3 text-center text-sm font-medium transition-colors {{ $activeView === 'performance' ? 'text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                >
                    الأداء
                </button>
                <button
                    wire:click="setActiveView('overview')"
                    class="py-3 text-center text-sm font-medium transition-colors {{ $activeView === 'overview' ? 'text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                >
                    نظرة عامة
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-5">
            @if($activeView === 'overview')
                {{-- Overview Cards --}}
                @if(!empty($data['overview']))
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Most Positive Employee --}}
                        @if(!empty($data['overview']['mostPositiveEmployee']))
                            @php $employee = $data['overview']['mostPositiveEmployee']; @endphp
                            <div class="rounded-xl overflow-hidden {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-blue-500' : '' }}" style="border: 2px solid rgb(34 197 94); background: white;">
                                <div class="p-4">
                                    {{-- Header Row --}}
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                            <x-heroicon-o-chevron-left class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-left">
                                                <h4 class="font-bold text-gray-900">{{ $employee['name'] }}</h4>
                                                <span class="text-xs" style="color: rgb(34 197 94);">الموظف الأكثر إيجابية</span>
                                            </div>
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" style="background: rgb(59 130 246);">
                                                {{ mb_substr($employee['name'], 0, 1) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Stats --}}
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">عدد مرات الذكر</span>
                                            <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-bold text-gray-700">{{ $employee['totalMentions'] ?? 0 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">التقييم</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-gray-900">{{ number_format($employee['averageRating'] ?? 5, 0) }}</span>
                                                <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden mt-4 mb-3">
                                        <div class="h-full rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 5) * 20) }}%; background: rgb(34 197 94);"></div>
                                    </div>

                                    <button
                                        wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                        class="w-full text-center text-sm text-gray-500 hover:text-gray-700 font-medium"
                                    >
                                        {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'اضغط لعرض التفاصيل' }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Most Mentioned Employee --}}
                        @if(!empty($data['overview']['mostMentionedEmployee']))
                            @php $employee = $data['overview']['mostMentionedEmployee']; @endphp
                            <div class="rounded-xl overflow-hidden {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-blue-500' : '' }}" style="border: 2px solid rgb(168 85 247); background: white;">
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                            <x-heroicon-o-chevron-left class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-left">
                                                <h4 class="font-bold text-gray-900">{{ $employee['name'] }}</h4>
                                                <span class="text-xs" style="color: rgb(168 85 247);">الموظف الأكثر ذكراً</span>
                                            </div>
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" style="background: rgb(168 85 247);">
                                                {{ mb_substr($employee['name'], 0, 1) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">عدد مرات الذكر</span>
                                            <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-bold text-gray-700">{{ $employee['totalMentions'] ?? 0 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">التقييم</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-gray-900">{{ number_format($employee['averageRating'] ?? 5, 0) }}</span>
                                                <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden mt-4 mb-3">
                                        <div class="h-full rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 5) * 20) }}%; background: rgb(34 197 94);"></div>
                                    </div>

                                    <button
                                        wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                        class="w-full text-center text-sm text-gray-500 hover:text-gray-700 font-medium"
                                    >
                                        {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'اضغط لعرض التفاصيل' }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Most Negative Employee --}}
                        @if(!empty($data['overview']['mostNegativeEmployee']))
                            @php $employee = $data['overview']['mostNegativeEmployee']; @endphp
                            <div class="rounded-xl overflow-hidden {{ $expandedEmployee === $employee['name'] ? 'ring-2 ring-blue-500' : '' }}" style="border: 2px solid rgb(244 63 94); background: white;">
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-arrow-trending-down class="w-5 h-5" style="color: rgb(244 63 94);" />
                                            <x-heroicon-o-chevron-left class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-left">
                                                <h4 class="font-bold text-gray-900">{{ $employee['name'] }}</h4>
                                                <span class="text-xs" style="color: rgb(244 63 94);">يحتاج تطوير</span>
                                            </div>
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold" style="background: rgb(244 63 94);">
                                                {{ mb_substr($employee['name'], 0, 1) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">عدد مرات الذكر</span>
                                            <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-bold text-gray-700">{{ $employee['totalMentions'] ?? 0 }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500">التقييم</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold" style="color: rgb(244 63 94);">{{ number_format($employee['averageRating'] ?? 0, 2) }}</span>
                                                <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden mt-4 mb-3">
                                        <div class="h-full rounded-full" style="width: {{ min(100, ($employee['averageRating'] ?? 0) * 20) }}%; background: rgb(244 63 94);"></div>
                                    </div>

                                    <button
                                        wire:click="toggleEmployee('{{ $employee['name'] }}')"
                                        class="w-full text-center text-sm text-gray-500 hover:text-gray-700 font-medium"
                                    >
                                        {{ $expandedEmployee === $employee['name'] ? 'إخفاء التفاصيل' : 'اضغط لعرض التفاصيل' }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Expanded Employee Details --}}
                    @if($expandedEmployee)
                        @php
                            $selectedEmployee = null;
                            if (!empty($data['overview']['mostPositiveEmployee']) && $data['overview']['mostPositiveEmployee']['name'] === $expandedEmployee) {
                                $selectedEmployee = $data['overview']['mostPositiveEmployee'];
                            } elseif (!empty($data['overview']['mostMentionedEmployee']) && $data['overview']['mostMentionedEmployee']['name'] === $expandedEmployee) {
                                $selectedEmployee = $data['overview']['mostMentionedEmployee'];
                            } elseif (!empty($data['overview']['mostNegativeEmployee']) && $data['overview']['mostNegativeEmployee']['name'] === $expandedEmployee) {
                                $selectedEmployee = $data['overview']['mostNegativeEmployee'];
                            }
                        @endphp

                        @if($selectedEmployee)
                            <div class="mt-6 bg-white rounded-xl border border-gray-200 p-5">
                                {{-- Employee Header --}}
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background: rgb(59 130 246);">
                                        {{ mb_substr($selectedEmployee['name'], 0, 1) }}
                                    </div>
                                    <h4 class="font-bold text-gray-900">تفاصيل {{ $selectedEmployee['name'] }}</h4>
                                </div>

                                {{-- Two Column Layout --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    {{-- Positives --}}
                                    <div>
                                        <div class="flex items-center gap-2 mb-3">
                                            <x-heroicon-o-hand-thumb-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                            <span class="font-semibold" style="color: rgb(34 197 94);">أبرز الإيجابيات</span>
                                        </div>
                                        @if(!empty($selectedEmployee['positiveKeywords']))
                                            <div class="space-y-2">
                                                @foreach(array_slice($selectedEmployee['positiveKeywords'], 0, 3) as $keyword)
                                                    <span class="inline-block px-3 py-1 rounded-full text-sm" style="background: rgb(220 252 231); color: rgb(21 128 61);">{{ $keyword }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="space-y-2">
                                                <span class="inline-block px-3 py-1 rounded-full text-sm" style="background: rgb(220 252 231); color: rgb(21 128 61);">خدمته ممتازه</span>
                                                <span class="inline-block px-3 py-1 rounded-full text-sm" style="background: rgb(220 252 231); color: rgb(21 128 61);">تعامل راقي</span>
                                                <span class="inline-block px-3 py-1 rounded-full text-sm" style="background: rgb(220 252 231); color: rgb(21 128 61);">قمة الاخلاق</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Negatives --}}
                                    <div>
                                        <div class="flex items-center gap-2 mb-3">
                                            <x-heroicon-o-hand-thumb-down class="w-5 h-5" style="color: rgb(244 63 94);" />
                                            <span class="font-semibold" style="color: rgb(244 63 94);">نقاط التحسين</span>
                                        </div>
                                        @if(!empty($selectedEmployee['negativeKeywords']))
                                            <div class="space-y-2">
                                                @foreach(array_slice($selectedEmployee['negativeKeywords'], 0, 3) as $keyword)
                                                    <span class="inline-block px-3 py-1 rounded-full text-sm" style="background: rgb(254 226 226); color: rgb(185 28 28);">{{ $keyword }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-sm">لا توجد نقاط تحسين مسجلة</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Stats Row --}}
                                <div class="grid grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                                    <div class="text-center">
                                        <div class="text-xl font-bold" style="color: rgb(59 130 246);">{{ $selectedEmployee['totalMentions'] ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">مرات الذكر</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-900">{{ number_format($selectedEmployee['averageRating'] ?? 5, 0) }}</div>
                                        <div class="text-xs text-gray-500">التقييم</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold" style="color: rgb(34 197 94);">{{ $selectedEmployee['positiveCount'] ?? 3 }}</div>
                                        <div class="text-xs text-gray-500">إيجابيات</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold" style="color: rgb(244 63 94);">{{ $selectedEmployee['negativeCount'] ?? 0 }}</div>
                                        <div class="text-xs text-gray-500">تحسينات</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500">لا توجد بيانات موظفين</p>
                    </div>
                @endif
            @else
                {{-- Performance Table View --}}
                @if(!empty($data['performance']))
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">عدد الذكر</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">التقييم</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">الاتجاه</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($data['performance'] as $employee)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-600">{{ mb_substr($employee['name'], 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-gray-900">{{ $employee['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900">{{ $employee['totalMentions'] ?? 0 }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="font-medium text-gray-900">{{ number_format($employee['averageRating'] ?? 0, 1) }}</span>
                                                <x-heroicon-s-star class="w-4 h-4 text-yellow-400" />
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @php
                                                $trend = $employee['performanceNote'] ?? 'stable';
                                            @endphp
                                            @if($trend === 'up')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium" style="background: rgb(220 252 231); color: rgb(21 128 61);">
                                                    <x-heroicon-o-arrow-trending-up class="w-3 h-3" />
                                                    متحسن
                                                </span>
                                            @elseif($trend === 'down')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium" style="background: rgb(254 226 226); color: rgb(185 28 28);">
                                                    <x-heroicon-o-arrow-trending-down class="w-3 h-3" />
                                                    يحتاج تحسين
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    <x-heroicon-o-minus class="w-3 h-3" />
                                                    مستقر
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500">لم يتم ذكر أي موظفين في المراجعات</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
