<div class="space-y-6" dir="rtl">
    {{-- Overview Cards --}}
    @if(!empty($data['overview']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Most Positive --}}
            @if(!empty($data['overview']['mostPositiveEmployee']))
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-6 border border-green-200 dark:border-green-700">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-face-smile class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-green-600 dark:text-green-400">الموظف الأكثر إيجابية</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostPositiveEmployee']['name'] }}</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">عدد الذكر</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostPositiveEmployee']['totalMentions'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ number_format($data['overview']['mostPositiveEmployee']['averageRating'] ?? 0, 1) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Most Mentioned --}}
            @if(!empty($data['overview']['mostMentionedEmployee']))
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-blue-600 dark:text-blue-400">الموظف الأكثر ذكراً</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostMentionedEmployee']['name'] }}</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">عدد الذكر</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostMentionedEmployee']['totalMentions'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ number_format($data['overview']['mostMentionedEmployee']['averageRating'] ?? 0, 1) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Most Negative --}}
            @if(!empty($data['overview']['mostNegativeEmployee']))
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl p-6 border border-red-200 dark:border-red-700">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-sm text-red-600 dark:text-red-400">يحتاج تحسين</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostNegativeEmployee']['name'] }}</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">عدد الذكر</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $data['overview']['mostNegativeEmployee']['totalMentions'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">التقييم</span>
                            <p class="font-bold text-gray-900 dark:text-white">{{ number_format($data['overview']['mostNegativeEmployee']['averageRating'] ?? 0, 1) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Performance Table --}}
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
</div>
