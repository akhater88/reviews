<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">🏆 المسابقات النشطة</x-slot>
        @if(count($activeCompetitions) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($activeCompetitions as $comp)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border-l-4 border-primary-500">
                        <h3 class="font-bold">{{ $comp['name'] }}</h3>
                        <div class="mt-2 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">المتبقي:</span>
                                <span class="{{ $comp['remaining_days'] <= 3 ? 'text-danger-600' : 'text-success-600' }}">{{ $comp['remaining_days'] }} يوم</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">فروعي:</span>
                                <span class="font-medium">{{ $comp['my_branches'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $comp['progress'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">لا توجد مسابقات نشطة</p>
        @endif
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">📊 ترتيبي الحالي</x-slot>
        @if(count($myRankings) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-right py-2">المسابقة</th>
                            <th class="text-right py-2">الفرع</th>
                            <th class="text-center py-2">المركز</th>
                            <th class="text-center py-2">النقاط</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myRankings as $ranking)
                            <tr class="border-b">
                                <td class="py-2">{{ $ranking['competition'] }}</td>
                                <td class="py-2">{{ $ranking['branch'] }}</td>
                                <td class="py-2 text-center text-lg">
                                    @if($ranking['rank'] == 1) 🥇
                                    @elseif($ranking['rank'] == 2) 🥈
                                    @elseif($ranking['rank'] == 3) 🥉
                                    @else #{{ $ranking['rank'] }}
                                    @endif
                                </td>
                                <td class="py-2 text-center font-medium">{{ number_format($ranking['score'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">لا توجد نتائج بعد</p>
        @endif
    </x-filament::section>

    <x-filament::section class="mt-6">
        <x-slot name="heading">🎉 جوائزي</x-slot>
        @if(count($myWinnings) > 0)
            <div class="space-y-3">
                @foreach($myWinnings as $win)
                    <div class="flex items-center gap-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="text-3xl">
                            @if($win['rank'] == 1) 🥇
                            @elseif($win['rank'] == 2) 🥈
                            @elseif($win['rank'] == 3) 🥉
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">{{ $win['competition'] }}</div>
                            <div class="text-sm text-gray-500">{{ $win['branch'] }}</div>
                        </div>
                        <div class="text-left">
                            @if($win['prize'])
                                <div class="font-medium">🎁 {{ $win['prize'] }}</div>
                            @endif
                            <x-filament::badge size="sm">{{ $win['prize_status'] }}</x-filament::badge>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">لم تفز بعد - استمر في تحسين أدائك!</p>
        @endif
    </x-filament::section>
</x-filament-panels::page>
