<x-filament-panels::page>
    {{-- Header with Filters --}}
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">ترتيب الفروع</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $dateRangeLabel }}</p>
            </div>

            <x-filament::button wire:click="exportReport" icon="heroicon-o-arrow-down-tray" color="gray">
                تصدير التقرير
            </x-filament::button>
        </div>
    </div>

    {{-- Filters Form --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-6 border border-gray-200 dark:border-gray-700">
        <form wire:submit.prevent="loadRankings">
            {{ $this->form }}
        </form>
    </div>

    {{-- Podium Section --}}
    @if(!empty($topThree['first']))
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 text-center">المراكز الأولى</h3>

            <div class="flex flex-col md:flex-row items-end justify-center gap-4 md:gap-8">
                {{-- 2nd Place --}}
                @if(!empty($topThree['second']))
                    <div class="order-2 md:order-1 w-full md:w-64">
                        <div class="bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 text-center relative border-2 border-gray-300 dark:border-gray-600">
                            {{-- Medal --}}
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold text-lg">2</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="w-16 h-16 bg-gray-300 dark:bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <x-heroicon-o-building-storefront class="w-8 h-8 text-gray-600 dark:text-gray-300" />
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $topThree['second']['branch_name'] }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $topThree['second']['branch_city'] }}</p>

                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center justify-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-4 h-4 {{ $i <= round($topThree['second']['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                        @endfor
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-1">{{ number_format($topThree['second']['rating'], 1) }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-400">{{ $topThree['second']['performance_score'] }}%</p>
                                </div>

                                {{-- Badges --}}
                                @if(!empty($topThree['second']['badges']))
                                    <div class="flex flex-wrap justify-center gap-1 mt-3">
                                        @foreach($topThree['second']['badges'] as $badge)
                                            @php
                                                $badgeClasses = match($badge['color']) {
                                                    'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                    'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                    'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                    'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                    'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                                };
                                            @endphp
                                            <span class="text-xs px-2 py-1 rounded-full {{ $badgeClasses }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-600">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $topThree['second']['manager_name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $topThree['second']['manager_email'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 1st Place --}}
                <div class="order-1 md:order-2 w-full md:w-72">
                    <div class="bg-gradient-to-b from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 rounded-xl p-8 text-center relative border-2 border-yellow-400 dark:border-yellow-600 shadow-xl transform md:scale-110">
                        {{-- Crown/Trophy --}}
                        <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                            <div class="w-14 h-14 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg">
                                <x-heroicon-s-trophy class="w-8 h-8 text-yellow-800" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="w-20 h-20 bg-yellow-200 dark:bg-yellow-700 rounded-full mx-auto mb-3 flex items-center justify-center ring-4 ring-yellow-400">
                                <x-heroicon-o-building-storefront class="w-10 h-10 text-yellow-700 dark:text-yellow-200" />
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $topThree['first']['branch_name'] }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $topThree['first']['branch_city'] }}</p>

                            <div class="mt-4 space-y-2">
                                <div class="flex items-center justify-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($topThree['first']['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                    @endfor
                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-1">{{ number_format($topThree['first']['rating'], 1) }}</span>
                                </div>
                                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $topThree['first']['performance_score'] }}%</p>
                            </div>

                            {{-- Badges --}}
                            @if(!empty($topThree['first']['badges']))
                                <div class="flex flex-wrap justify-center gap-1 mt-3">
                                    @foreach($topThree['first']['badges'] as $badge)
                                        @php
                                            $badgeClasses = match($badge['color']) {
                                                'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                            };
                                        @endphp
                                        <span class="text-xs px-2 py-1 rounded-full {{ $badgeClasses }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 pt-4 border-t border-yellow-300 dark:border-yellow-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $topThree['first']['manager_name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $topThree['first']['manager_email'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3rd Place --}}
                @if(!empty($topThree['third']))
                    <div class="order-3 w-full md:w-64">
                        <div class="bg-gradient-to-b from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 rounded-xl p-6 text-center relative border-2 border-orange-300 dark:border-orange-700">
                            {{-- Medal --}}
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                <div class="w-10 h-10 bg-orange-400 rounded-full flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold text-lg">3</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="w-16 h-16 bg-orange-200 dark:bg-orange-700 rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <x-heroicon-o-building-storefront class="w-8 h-8 text-orange-600 dark:text-orange-300" />
                                </div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $topThree['third']['branch_name'] }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $topThree['third']['branch_city'] }}</p>

                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center justify-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-4 h-4 {{ $i <= round($topThree['third']['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                                        @endfor
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-1">{{ number_format($topThree['third']['rating'], 1) }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-orange-500">{{ $topThree['third']['performance_score'] }}%</p>
                                </div>

                                {{-- Badges --}}
                                @if(!empty($topThree['third']['badges']))
                                    <div class="flex flex-wrap justify-center gap-1 mt-3">
                                        @foreach($topThree['third']['badges'] as $badge)
                                            @php
                                                $badgeClasses = match($badge['color']) {
                                                    'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                    'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                    'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                    'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                    'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                                };
                                            @endphp
                                            <span class="text-xs px-2 py-1 rounded-full {{ $badgeClasses }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 pt-4 border-t border-orange-300 dark:border-orange-700">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $topThree['third']['manager_name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $topThree['third']['manager_email'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Full Rankings Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">جدول الترتيب الكامل</h3>
        </div>

        @if($rankings->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full" dir="rtl">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الترتيب</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الفرع والمدير</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">درجة الأداء</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">التقييم</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">المراجعات</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">النمو</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">معدل الاستجابة</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الإنجازات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rankings as $branch)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $branch['rank'] <= 3 ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">
                                {{-- Rank --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($branch['rank'] === 1)
                                            <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                                                <x-heroicon-s-trophy class="w-5 h-5 text-yellow-800" />
                                            </div>
                                        @elseif($branch['rank'] === 2)
                                            <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">2</span>
                                            </div>
                                        @elseif($branch['rank'] === 3)
                                            <div class="w-8 h-8 bg-orange-400 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">3</span>
                                            </div>
                                        @else
                                            <span class="w-8 h-8 flex items-center justify-center text-gray-500 dark:text-gray-400 font-medium">{{ $branch['rank'] }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Branch & Manager --}}
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $branch['branch_name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $branch['branch_city'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $branch['manager_name'] }}</p>
                                    </div>
                                </td>

                                {{-- Performance Score --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $branch['performance_score'] }}%</span>
                                        <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full mt-1">
                                            @php
                                                $scoreColor = match(true) {
                                                    $branch['performance_score'] >= 80 => 'bg-green-500',
                                                    $branch['performance_score'] >= 60 => 'bg-yellow-500',
                                                    default => 'bg-red-500',
                                                };
                                            @endphp
                                            <div class="{{ $scoreColor }} h-2 rounded-full" style="width: {{ min($branch['performance_score'], 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Rating --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-heroicon-s-star class="w-5 h-5 text-yellow-400" />
                                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($branch['rating'], 1) }}</span>
                                    </div>
                                </td>

                                {{-- Reviews --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="text-gray-900 dark:text-white">{{ $branch['total_reviews'] }}</span>
                                </td>

                                {{-- Growth --}}
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $growthColor = $branch['growth'] > 0 ? 'text-green-600' : ($branch['growth'] < 0 ? 'text-red-600' : 'text-gray-500');
                                        $growthIcon = $branch['growth'] > 0 ? 'heroicon-o-arrow-trending-up' : ($branch['growth'] < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-minus');
                                    @endphp
                                    <div class="flex items-center justify-center gap-1 {{ $growthColor }}">
                                        <x-dynamic-component :component="$growthIcon" class="w-4 h-4" />
                                        <span class="font-medium">{{ number_format(abs($branch['growth']), 1) }}%</span>
                                    </div>
                                </td>

                                {{-- Response Rate --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $branch['response_rate'] }}%</span>
                                        @if($branch['avg_response_time'])
                                            <span class="text-xs text-gray-400">{{ $branch['avg_response_time'] }} ساعة</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Badges --}}
                                <td class="px-6 py-4">
                                    @if(!empty($branch['badges']))
                                        <div class="flex flex-wrap justify-center gap-1">
                                            @foreach($branch['badges'] as $badge)
                                                @php
                                                    $badgeClasses = match($badge['color']) {
                                                        'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                                        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $badgeClasses }}" title="{{ $badge['label'] }}">
                                                    @if($badge['icon'] === 'trophy')
                                                        <x-heroicon-o-trophy class="w-3 h-3" />
                                                    @elseif($badge['icon'] === 'trending-up')
                                                        <x-heroicon-o-arrow-trending-up class="w-3 h-3" />
                                                    @elseif($badge['icon'] === 'chat')
                                                        <x-heroicon-o-chat-bubble-left-right class="w-3 h-3" />
                                                    @elseif($badge['icon'] === 'star')
                                                        <x-heroicon-o-star class="w-3 h-3" />
                                                    @elseif($badge['icon'] === 'bolt')
                                                        <x-heroicon-o-bolt class="w-3 h-3" />
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <x-heroicon-o-trophy class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات</h3>
                <p class="text-gray-500 dark:text-gray-400">لا توجد فروع أو مراجعات في الفترة المحددة</p>
            </div>
        @endif
    </div>

    {{-- Legend --}}
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">دليل الإنجازات</h4>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
                    <x-heroicon-o-trophy class="w-3 h-3" />
                    أفضل أداء شامل
                </span>
                <span class="text-xs text-gray-500">المركز الأول</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                    <x-heroicon-o-arrow-trending-up class="w-3 h-3" />
                    أعلى نمو
                </span>
                <span class="text-xs text-gray-500">أكبر تحسن</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <x-heroicon-o-chat-bubble-left-right class="w-3 h-3" />
                    الأكثر مراجعات
                </span>
                <span class="text-xs text-gray-500">أكثر المراجعات</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                    <x-heroicon-o-star class="w-3 h-3" />
                    أعلى تقييم
                </span>
                <span class="text-xs text-gray-500">أفضل متوسط تقييم</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                    <x-heroicon-o-bolt class="w-3 h-3" />
                    أسرع استجابة
                </span>
                <span class="text-xs text-gray-500">أعلى معدل رد</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
