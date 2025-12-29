@php
    $operationalData = $this->getOperationalData();
    $operationalCategories = $operationalData['operationalCategories'] ?? [];
    $aiInsights = $operationalData['aiInsights'] ?? null;
@endphp

<div dir="rtl">
    {{-- Operational Categories --}}
    @if(!empty($operationalCategories))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700" style="padding: 1.5rem; margin-bottom: 1.25rem;">
            {{-- Section Header --}}
            <div class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700" style="margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 1rem 1.5rem;">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="h-5 w-5" style="color: rgb(168 85 247);" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">أهم التوصيات</h3>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
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

                    <div class="rounded-lg border" style="{{ $riskStyles }} padding: 1.25rem;">
                        <div class="flex justify-between items-start" style="margin-bottom: 1rem;">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $riskIcon }}</span>
                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ $category['name'] ?? $category['issue'] ?? 'فئة غير محددة' }}
                                </h4>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach(array_slice($category['insights'] ?? [$category['solution'] ?? ''], 0, 2) as $insight)
                                @if($insight)
                                    <div class="text-sm text-gray-700 dark:text-gray-300 rounded" style="background: rgb(249 250 251); padding: 0.75rem;">
                                        {{ $insight }}
                                    </div>
                                @endif
                            @endforeach

                            @if(!empty($category['specificIssues'] ?? $category['contexts'] ?? []))
                                <div style="margin-top: 0.5rem;">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400" style="margin-bottom: 0.5rem;">القضايا المحددة:</p>
                                    <div class="flex flex-wrap" style="gap: 0.5rem;">
                                        @foreach(array_slice($category['specificIssues'] ?? $category['contexts'] ?? [], 0, 3) as $issue)
                                            <span class="text-xs rounded" style="background: rgb(254 226 226); color: rgb(153 27 27); padding: 0.375rem 0.625rem;">
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
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700" style="padding: 1.5rem; margin-bottom: 1.25rem;">
            {{-- Section Header --}}
            <div class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700" style="margin: -1.5rem -1.5rem 1.5rem -1.5rem; padding: 1rem 1.5rem;">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cpu-chip class="h-5 w-5" style="color: rgb(168 85 247);" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ملخص تقييم الذكاء الاصطناعي</h3>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Overall Assessment --}}
                @if(!empty($aiInsights['overallAssessment']))
                    <div class="rounded-lg border" style="background: rgb(239 246 255); border-color: rgb(191 219 254); padding: 1.25rem;">
                        <h4 class="font-semibold" style="color: rgb(30 58 138); margin-bottom: 0.75rem;">التقييم العام</h4>
                        <p class="text-sm" style="color: rgb(30 64 175); line-height: 1.6;">{{ $aiInsights['overallAssessment'] }}</p>
                    </div>
                @endif

                {{-- Priority Actions --}}
                @if(!empty($aiInsights['priorityActions']))
                    <div class="rounded-lg border" style="background: rgb(254 252 232); border-color: rgb(254 240 138); padding: 1.25rem;">
                        <h4 class="font-semibold" style="color: rgb(113 63 18); margin-bottom: 0.75rem;">الإجراءات ذات الأولوية</h4>
                        <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
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
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        @if(!empty($aiInsights['competitiveInsights']['strongestAdvantage']))
                            <div class="rounded-lg border" style="background: rgb(240 253 244); border-color: rgb(187 247 208); padding: 1rem;">
                                <h5 class="font-medium text-sm" style="color: rgb(20 83 45); margin-bottom: 0.5rem;">أقوى ميزة</h5>
                                <p class="text-xs" style="color: rgb(22 101 52); line-height: 1.5;">{{ $aiInsights['competitiveInsights']['strongestAdvantage'] }}</p>
                            </div>
                        @endif

                        @if(!empty($aiInsights['competitiveInsights']['biggestWeakness']))
                            <div class="rounded-lg border" style="background: rgb(254 242 242); border-color: rgb(254 202 202); padding: 1rem;">
                                <h5 class="font-medium text-sm" style="color: rgb(127 29 29); margin-bottom: 0.5rem;">أكبر نقطة ضعف</h5>
                                <p class="text-xs" style="color: rgb(153 27 27); line-height: 1.5;">{{ $aiInsights['competitiveInsights']['biggestWeakness'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- No Data State --}}
    @if(empty($operationalCategories) && empty($aiInsights))
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-lg text-center" style="padding: 2.5rem;">
            <x-heroicon-o-light-bulb class="w-12 h-12 text-gray-300 mx-auto" style="margin-bottom: 1rem;" />
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300" style="margin-bottom: 0.5rem;">لا توجد توصيات</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم العثور على توصيات لهذا المطعم</p>
        </div>
    @endif
</div>
