@php
    $operationalData = $this->getOperationalData();
    $operationalCategories = $operationalData['operationalCategories'] ?? [];
    $aiInsights = $operationalData['aiInsights'] ?? null;
@endphp

<div class="space-y-4 sm:space-y-5" dir="rtl">
    {{-- Operational Categories --}}
    @if(!empty($operationalCategories))
        <div class="bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
            {{-- Section Header --}}
            <div class="relative z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 -mx-5 px-5 py-3 mb-4">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="h-5 w-5" style="color: rgb(168 85 247);" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">أهم التوصيات</h3>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                @foreach($operationalCategories as $category)
                    @php
                        $riskLevel = $category['riskLevel'] ?? 'medium';
                        $riskStyles = match($riskLevel) {
                            'high' => 'background: rgb(254 242 242); border-color: rgb(254 202 202);',
                            'low' => 'background: rgb(240 253 244); border-color: rgb(187 247 208);',
                            default => 'background: rgb(254 252 232); border-color: rgb(253 224 71);',
                        };
                        $riskIcon = match($riskLevel) {
                            'high' => '🔴',
                            'low' => '🟢',
                            default => '🟡',
                        };
                    @endphp

                    <div class="rounded-lg p-4 border" style="{{ $riskStyles }}">
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
                                    <div class="text-sm text-gray-700 dark:text-gray-300 p-2 rounded" style="background: rgb(249 250 251);">
                                        {{ $insight }}
                                    </div>
                                @endif
                            @endforeach

                            @if(!empty($category['specificIssues'] ?? $category['contexts'] ?? []))
                                <div class="mt-2">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">القضايا المحددة:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($category['specificIssues'] ?? $category['contexts'] ?? [], 0, 3) as $issue)
                                            <span class="text-xs px-2 py-1 rounded" style="background: rgb(254 226 226); color: rgb(153 27 27);">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
            {{-- Section Header --}}
            <div class="relative z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 -mx-5 px-5 py-3 mb-4">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cpu-chip class="h-5 w-5" style="color: rgb(168 85 247);" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ملخص تقييم الذكاء الاصطناعي</h3>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Overall Assessment --}}
                @if(!empty($aiInsights['overallAssessment']))
                    <div class="rounded-lg p-4 border" style="background: rgb(239 246 255); border-color: rgb(191 219 254);">
                        <h4 class="font-semibold mb-2" style="color: rgb(30 58 138);">التقييم العام</h4>
                        <p class="text-sm" style="color: rgb(30 64 175);">{{ $aiInsights['overallAssessment'] }}</p>
                    </div>
                @endif

                {{-- Priority Actions --}}
                @if(!empty($aiInsights['priorityActions']))
                    <div class="rounded-lg p-4 border" style="background: rgb(254 252 232); border-color: rgb(254 240 138);">
                        <h4 class="font-semibold mb-2" style="color: rgb(113 63 18);">الإجراءات ذات الأولوية</h4>
                        <ul class="space-y-1">
                            @foreach(array_slice($aiInsights['priorityActions'], 0, 3) as $action)
                                <li class="text-sm flex items-start gap-2" style="color: rgb(133 77 14);">
                                    <span style="color: rgb(234 179 8); margin-top: 0.25rem;">•</span>
                                    <span>{{ $action }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Competitive Insights --}}
                @if(!empty($aiInsights['competitiveInsights']))
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                        @if(!empty($aiInsights['competitiveInsights']['strongestAdvantage']))
                            <div class="rounded-lg p-3 border" style="background: rgb(240 253 244); border-color: rgb(187 247 208);">
                                <h5 class="font-medium text-sm mb-1" style="color: rgb(20 83 45);">أقوى ميزة</h5>
                                <p class="text-xs" style="color: rgb(22 101 52);">{{ $aiInsights['competitiveInsights']['strongestAdvantage'] }}</p>
                            </div>
                        @endif

                        @if(!empty($aiInsights['competitiveInsights']['biggestWeakness']))
                            <div class="rounded-lg p-3 border" style="background: rgb(254 242 242); border-color: rgb(254 202 202);">
                                <h5 class="font-medium text-sm mb-1" style="color: rgb(127 29 29);">أكبر نقطة ضعف</h5>
                                <p class="text-xs" style="color: rgb(153 27 27);">{{ $aiInsights['competitiveInsights']['biggestWeakness'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- No Data State --}}
    @if(empty($operationalCategories) && empty($aiInsights))
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-lg p-8 text-center">
            <x-heroicon-o-light-bulb class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">لا توجد توصيات</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم العثور على توصيات لهذا المطعم</p>
        </div>
    @endif
</div>
