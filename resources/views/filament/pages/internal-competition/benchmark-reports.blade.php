<x-filament-panels::page>
    @if($competition && !empty($overallBenchmark['comparisons']))
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold">{{ $competition->display_name }}</h2>
            <p class="text-sm text-gray-500">تقرير مقارنة الأداء</p>
        </div>

        <x-filament::section class="mb-6">
            <x-slot name="heading">📈 المقارنة الإجمالية</x-slot>
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
                        @foreach($overallBenchmark['comparisons'] as $comparison)
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

        @if(!empty($roiSummary))
            <x-filament::section>
                <x-slot name="heading">📊 ملخص ROI</x-slot>
                <div class="text-center p-4">
                    <p class="text-lg">{{ $roiSummary['summary'] ?? '' }}</p>
                </div>
            </x-filament::section>
        @endif
    @else
        <div class="text-center py-12">
            <x-heroicon-o-scale class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2">لا توجد بيانات</h3>
        </div>
    @endif
</x-filament-panels::page>
