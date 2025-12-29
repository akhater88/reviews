<div dir="rtl">
    @if(!empty($data['performance']) && count($data['performance']) > 0)
        {{-- Section Header --}}
        <div class="relative z-20 rounded-lg shadow-sm mb-4" style="background: linear-gradient(to left, rgb(236 253 245), rgb(240 253 244)); border: 1px solid rgb(167 243 208);">
            <div class="px-4 py-3 text-right">
                <h2 class="text-lg font-bold text-gray-900">الموظفين</h2>
            </div>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-lg">
            <div class="p-3 sm:p-5">
                <section class="space-y-4 sm:space-y-6">
                    {{-- Section Title with Badge --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-trophy class="w-5 h-5 sm:w-6 sm:h-6" style="color: rgb(22 163 74);" />
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">الموظفين</h2>
                        </div>
                        <div class="w-fit text-xs sm:text-sm px-2 py-1 rounded-full font-medium" style="background: rgb(220 252 231); color: rgb(21 128 61);">
                            {{ count($data['performance'] ?? []) }} موظفين
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="w-full">
                        <div class="rounded-lg p-1 mb-6" style="display: grid; grid-template-columns: repeat(2, 1fr); height: 2.5rem; background: rgb(243 244 246);">
                            <button
                                wire:click="setActiveView('overview')"
                                class="text-sm px-2 rounded-md transition-all {{ $activeView === 'overview' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                            >
                                نظرة عامة
                            </button>
                            <button
                                wire:click="setActiveView('performance')"
                                class="text-sm px-2 rounded-md transition-all {{ $activeView === 'performance' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                            >
                                الأداء
                            </button>
                        </div>

                        @if($activeView === 'overview')
                            {{-- Overview Tab Content --}}
                            <div class="space-y-6">
                                {{-- Overview Employee Cards Grid --}}
                                @if(!empty($data['overview']))
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                                        @foreach(['mostPositiveEmployee', 'mostMentionedEmployee', 'mostNegativeEmployee'] as $employeeKey)
                                            @if(!empty($data['overview'][$employeeKey]))
                                                @php
                                                    $employee = $data['overview'][$employeeKey];
                                                    $rating = $employee['averageRating'] ?? 0;
                                                    $isLowPerformance = $rating < 3;
                                                    $roleLabel = match($employeeKey) {
                                                        'mostPositiveEmployee' => 'الموظف الأكثر إيجابية',
                                                        'mostMentionedEmployee' => 'الموظف الأكثر ذكراً',
                                                        'mostNegativeEmployee' => 'يحتاج تطوير',
                                                        default => ''
                                                    };
                                                @endphp
                                                {{-- Employee Card --}}
                                                <div class="cursor-pointer transition-all duration-300 hover:shadow-xl hover:scale-[1.02] rounded-lg border {{ $isLowPerformance ? 'border-red-200 ring-1 ring-red-300' : 'border-gray-200' }}" style="{{ $isLowPerformance ? 'background: rgb(254 242 242);' : 'background: white;' }}">
                                                    <div class="pb-3 pt-4 px-4">
                                                        <div class="flex items-center gap-3">
                                                            {{-- Avatar --}}
                                                            <div class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-lg shadow-sm ring-2 ring-white {{ $isLowPerformance ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                                                {{ mb_substr($employee['name'] ?? 'م', 0, 1) }}
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <h3 class="text-lg sm:text-xl font-bold truncate {{ $isLowPerformance ? 'text-red-700' : 'text-gray-900' }}">
                                                                    {{ $employee['name'] ?? 'غير محدد' }}
                                                                </h3>
                                                                <p class="text-sm text-gray-600">{{ $roleLabel }}</p>
                                                            </div>
                                                            <div class="flex flex-col items-center gap-1">
                                                                @if($isLowPerformance)
                                                                    <x-heroicon-o-arrow-trending-down class="w-5 h-5" style="color: rgb(239 68 68);" />
                                                                @else
                                                                    <x-heroicon-o-arrow-trending-up class="w-5 h-5" style="color: rgb(34 197 94);" />
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="pt-0 px-4 pb-4">
                                                        <div class="space-y-3">
                                                            {{-- Mentions --}}
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-sm text-gray-600">عدد مرات الذكر</span>
                                                                <div class="px-2 py-1 rounded-md text-xs font-medium border h-[23px] {{ $isLowPerformance ? 'border-red-300 text-red-600' : 'border-gray-300 text-gray-600' }}">
                                                                    {{ $employee['totalMentions'] ?? 0 }}
                                                                </div>
                                                            </div>
                                                            {{-- Rating --}}
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-sm text-gray-600">التقييم</span>
                                                                <div class="flex items-center gap-2">
                                                                    <x-heroicon-s-star class="w-4 h-4 {{ $isLowPerformance ? 'text-red-500' : 'text-yellow-500' }}" />
                                                                    <span class="font-medium text-lg {{ $isLowPerformance ? 'text-red-600' : 'text-gray-900' }}">
                                                                        {{ number_format($rating, 1) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            {{-- Progress Bar --}}
                                                            <div class="mt-3">
                                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                                    <div class="h-2 rounded-full transition-all duration-300 {{ $isLowPerformance ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ ($rating / 5) * 100 }}%;"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    {{-- Employee Details Section --}}
                                    @foreach(['mostPositiveEmployee', 'mostMentionedEmployee', 'mostNegativeEmployee'] as $employeeKey)
                                        @if(!empty($data['overview'][$employeeKey]))
                                            @php
                                                $employee = $data['overview'][$employeeKey];
                                                $rating = $employee['averageRating'] ?? 0;
                                                $isLowPerformance = $rating < 3;
                                                $topPositives = $employee['topPositives'] ?? [];
                                                $improvementPoints = $employee['improvementPoints'] ?? [];
                                            @endphp
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg {{ $isLowPerformance ? '' : '' }}" style="{{ $isLowPerformance ? 'background: linear-gradient(to left, rgb(254 242 242), rgb(255 247 237));' : 'background: linear-gradient(to left, rgb(240 253 244), rgb(239 246 255));' }}">
                                                {{-- Detail Header --}}
                                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                                    <h3 class="flex items-center gap-3 text-lg font-bold text-gray-900 dark:text-white">
                                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $isLowPerformance ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                                            {{ mb_substr($employee['name'] ?? 'م', 0, 1) }}
                                                        </div>
                                                        تفاصيل {{ $employee['name'] ?? 'غير محدد' }}
                                                        @if($isLowPerformance)
                                                            <div class="text-white text-xs px-2 py-1 rounded-full font-medium" style="background: rgb(220 38 38);">
                                                                أداء ضعيف
                                                            </div>
                                                        @endif
                                                    </h3>
                                                </div>
                                                <div class="p-4">
                                                    {{-- Positives and Improvements Grid --}}
                                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                                                        {{-- Positives --}}
                                                        <div class="space-y-4">
                                                            <h4 class="font-medium flex items-center gap-2 text-gray-900 dark:text-white">
                                                                <x-heroicon-o-hand-thumb-up class="w-4 h-4" style="color: rgb(34 197 94);" />
                                                                أبرز الإيجابيات
                                                            </h4>
                                                            <div class="flex flex-wrap gap-2">
                                                                @forelse($topPositives as $positive)
                                                                    <div class="px-3 py-1 rounded-full w-fit font-medium text-sm" style="background: rgb(220 252 231); color: rgb(21 128 61);">
                                                                        {{ $positive }}
                                                                    </div>
                                                                @empty
                                                                    <div class="text-gray-500 text-sm">لا توجد نقاط إيجابية مسجلة</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                        {{-- Improvements --}}
                                                        <div class="space-y-4">
                                                            <h4 class="font-medium flex items-center gap-2 text-gray-900 dark:text-white">
                                                                <x-heroicon-o-hand-thumb-down class="w-4 h-4" style="color: rgb(239 68 68);" />
                                                                نقاط التحسين
                                                            </h4>
                                                            <div class="flex flex-wrap gap-2">
                                                                @forelse($improvementPoints as $improvement)
                                                                    <div class="px-3 py-1 rounded-full w-fit font-medium text-sm" style="background: rgb(254 226 226); color: rgb(185 28 28);">
                                                                        {{ $improvement }}
                                                                    </div>
                                                                @empty
                                                                    <div class="text-gray-500 text-sm">لا توجد نقاط تحسين مسجلة</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Statistics --}}
                                                    <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; text-align: center;">
                                                            <div>
                                                                <div class="text-2xl font-bold {{ $isLowPerformance ? 'text-red-600' : 'text-blue-600' }}">
                                                                    {{ $employee['totalMentions'] ?? 0 }}
                                                                </div>
                                                                <div class="text-sm text-gray-600">مرات الذكر</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-2xl font-bold {{ $isLowPerformance ? 'text-red-600' : 'text-green-600' }}">
                                                                    {{ number_format($rating, 1) }}
                                                                </div>
                                                                <div class="text-sm text-gray-600">التقييم</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-2xl font-bold" style="color: rgb(147 51 234);">
                                                                    {{ count($topPositives) }}
                                                                </div>
                                                                <div class="text-sm text-gray-600">إيجابيات</div>
                                                            </div>
                                                            <div>
                                                                <div class="text-2xl font-bold {{ $isLowPerformance ? 'text-red-600' : 'text-orange-600' }}">
                                                                    {{ count($improvementPoints) }}
                                                                </div>
                                                                <div class="text-sm text-gray-600">تحسينات</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="text-center py-12">
                                        <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                        <p class="text-gray-500 dark:text-gray-400">لا توجد بيانات موظفين</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Performance Tab Content --}}
                            <div class="space-y-6">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                                    @forelse($data['performance'] ?? [] as $employee)
                                        @php
                                            $rating = $employee['averageRating'] ?? 0;
                                            $isLowPerformance = $rating < 3;
                                            $trend = $employee['performanceNote'] ?? 'stable';
                                        @endphp
                                        <div class="border rounded-lg transition-all duration-200 hover:shadow-md {{ $isLowPerformance ? 'border-red-200' : 'border-gray-200' }}" style="{{ $isLowPerformance ? 'background: rgb(254 242 242);' : 'background: white;' }}">
                                            {{-- Card Header --}}
                                            <div class="pb-3 p-4 border-b border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-between">
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="text-base sm:text-lg font-bold truncate {{ $isLowPerformance ? 'text-red-700' : 'text-gray-900' }}">
                                                            {{ $employee['name'] ?? 'غير محدد' }}
                                                        </h3>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <x-heroicon-s-star class="w-4 h-4 {{ $isLowPerformance ? 'text-red-500' : 'text-yellow-500' }}" />
                                                        <span class="font-medium {{ $isLowPerformance ? 'text-red-600' : 'text-gray-900' }}">
                                                            {{ number_format($rating, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Card Content --}}
                                            <div class="p-4">
                                                <div class="space-y-3">
                                                    <div class="flex justify-between text-sm">
                                                        <span class="text-gray-600">مرات الذكر</span>
                                                        <span class="inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $isLowPerformance ? 'border-red-300 text-red-600' : 'border-gray-300 text-gray-700' }}">
                                                            {{ $employee['totalMentions'] ?? 0 }}
                                                        </span>
                                                    </div>
                                                    {{-- Progress Bar --}}
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full transition-all duration-300 {{ $isLowPerformance ? 'bg-red-500' : 'bg-gray-800' }}" style="width: {{ ($rating / 5) * 100 }}%;"></div>
                                                    </div>
                                                    {{-- Performance Status --}}
                                                    <div class="flex items-center gap-2 text-sm">
                                                        @if($trend === 'up')
                                                            <x-heroicon-o-arrow-trending-up class="w-3 h-3" style="color: rgb(34 197 94);" />
                                                            <span>أداء متحسن</span>
                                                        @elseif($trend === 'down')
                                                            <x-heroicon-o-arrow-trending-down class="w-3 h-3" style="color: rgb(239 68 68);" />
                                                            <span>يحتاج تحسين عاجل</span>
                                                        @else
                                                            <x-heroicon-o-chart-bar class="w-3 h-3" style="color: rgb(59 130 246);" />
                                                            <span>أداء مستقر</span>
                                                        @endif
                                                    </div>
                                                    {{-- Low Performance Warning --}}
                                                    @if($isLowPerformance)
                                                        <div class="mt-2 p-2 rounded-lg border" style="background: rgb(254 226 226); border-color: rgb(254 202 202);">
                                                            <p class="text-xs font-medium" style="color: rgb(185 28 28);">
                                                                ⚠️ يحتاج تدخل إداري فوري
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-3 text-center py-12">
                                            <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                            <p class="text-gray-500 dark:text-gray-400">لم يتم ذكر أي موظفين في المراجعات</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    @else
        {{-- No Data State --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-lg p-8 text-center">
            <x-heroicon-o-user class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">لا توجد بيانات موظفين</h3>
            <p class="text-gray-500 dark:text-gray-400">لم يتم العثور على بيانات الموظفين لهذا المطعم</p>
        </div>
    @endif
</div>
