@php
    $operationalData = $this->getOperationalData();
    $operationalCategories = $operationalData['operationalCategories'] ?? [];
    $aiInsights = $operationalData['aiInsights'] ?? null;
@endphp

@if(!empty($operationalCategories) || !empty($aiInsights))
    {{-- Section Header --}}
    <div class="rounded-xl shadow-sm border border-purple-100 dark:border-purple-800" style="background: linear-gradient(to right, rgb(250 245 255), rgb(245 243 255));">
        <div class="px-5 py-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">التوصيات</h2>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl">
        <div class="p-5 sm:p-6 space-y-6">

            {{-- Operational Categories --}}
            @if(!empty($operationalCategories))
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-purple-500 dark:text-purple-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">أهم التوصيات</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($operationalCategories as $category)
                            @php
                                $riskLevel = $category['riskLevel'] ?? 'medium';
                                $riskColor = match($riskLevel) {
                                    'high' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                    'low' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                                    default => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-700',
                                };
                                $riskIcon = match($riskLevel) {
                                    'high' => '🔴',
                                    'low' => '🟢',
                                    default => '🟡',
                                };
                            @endphp

                            <div class="rounded-xl p-4 border {{ $riskColor }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">{{ $riskIcon }}</span>
                                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                                            {{ $category['name'] ?? $category['issue'] ?? 'فئة غير محددة' }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    @foreach(array_slice($category['insights'] ?? [$category['solution'] ?? ''], 0, 2) as $insight)
                                        @if($insight)
                                            <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-2 rounded">
                                                {{ $insight }}
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(!empty($category['specificIssues'] ?? $category['contexts'] ?? []))
                                        <div class="mt-2">
                                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">القضايا المحددة:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($category['specificIssues'] ?? $category['contexts'] ?? [], 0, 3) as $issue)
                                                    <span class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded">
                                                        {{ $issue }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- AI Insights Summary --}}
            @if($aiInsights)
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-o-cpu-chip class="w-5 h-5 text-purple-500 dark:text-purple-400" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ملخّص تقييم الذكاء الاصطناعي</h3>
                    </div>

                    <div class="space-y-4">
                        {{-- Overall Assessment --}}
                        @if(!empty($aiInsights['overallAssessment']))
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                                <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">التقييم العام</h4>
                                <p class="text-sm text-blue-800 dark:text-blue-300">{{ $aiInsights['overallAssessment'] }}</p>
                            </div>
                        @endif

                        {{-- Priority Actions --}}
                        @if(!empty($aiInsights['priorityActions']))
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                                <h4 class="font-semibold text-yellow-900 dark:text-yellow-200 mb-2">الإجراءات ذات الأولوية</h4>
                                <ul class="space-y-1">
                                    @foreach(array_slice($aiInsights['priorityActions'], 0, 3) as $action)
                                        <li class="text-sm text-yellow-800 dark:text-yellow-300 flex items-start gap-2">
                                            <span class="text-yellow-500 dark:text-yellow-400 mt-1">•</span>
                                            <span>{{ $action }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Competitive Insights --}}
                        @if(!empty($aiInsights['competitiveInsights']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @if(!empty($aiInsights['competitiveInsights']['strongestAdvantage']))
                                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3">
                                        <h5 class="font-medium text-green-900 dark:text-green-200 text-sm mb-1">أقوى ميزة</h5>
                                        <p class="text-xs text-green-800 dark:text-green-300">{{ $aiInsights['competitiveInsights']['strongestAdvantage'] }}</p>
                                    </div>
                                @endif

                                @if(!empty($aiInsights['competitiveInsights']['biggestWeakness']))
                                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3">
                                        <h5 class="font-medium text-red-900 dark:text-red-200 text-sm mb-1">أكبر نقطة ضعف</h5>
                                        <p class="text-xs text-red-800 dark:text-red-300">{{ $aiInsights['competitiveInsights']['biggestWeakness'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
@endif
