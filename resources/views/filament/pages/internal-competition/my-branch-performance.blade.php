<x-filament-panels::page>
    @if($competition && $branch)
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold">{{ $branch->name }}</h2>
            <p class="text-sm text-gray-500">أداء الفرع في: {{ $competition->display_name }}</p>
        </div>

        <x-filament::section>
            <x-slot name="heading">📊 النتائج حسب المعيار</x-slot>
            @if(count($scores) > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($scores as $metricKey => $score)
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-center">
                            <div class="text-sm text-gray-500 mb-2">{{ $score['metric'] }}</div>
                            <div class="text-3xl font-bold mb-1">
                                @if($score['rank'] == 1) 🥇
                                @elseif($score['rank'] == 2) 🥈
                                @elseif($score['rank'] == 3) 🥉
                                @else #{{ $score['rank'] }}
                                @endif
                            </div>
                            <div class="text-xl font-semibold text-primary-600">{{ number_format($score['score'], 0) }} نقطة</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">لا توجد نتائج بعد</p>
            @endif
        </x-filament::section>

        @if(!empty($benchmark['comparisons']))
            <x-filament::section class="mt-6">
                <x-slot name="heading">📈 مقارنة الأداء</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-right py-2">المعيار</th>
                                <th class="text-center py-2">قبل</th>
                                <th class="text-center py-2">خلال</th>
                                <th class="text-center py-2">التغيير</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($benchmark['comparisons'] as $comparison)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $comparison['metric_label'] }}</td>
                                    <td class="py-2 text-center">{{ number_format($comparison['before_value'], 2) }}</td>
                                    <td class="py-2 text-center">{{ number_format($comparison['during_value'], 2) }}</td>
                                    <td class="py-2 text-center {{ $comparison['is_improvement'] ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $comparison['change'] >= 0 ? '+' : '' }}{{ $comparison['change_percentage'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    @else
        <div class="text-center py-12">
            <x-heroicon-o-building-storefront class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2">اختر فرع ومسابقة</h3>
        </div>
    @endif
</x-filament-panels::page>
