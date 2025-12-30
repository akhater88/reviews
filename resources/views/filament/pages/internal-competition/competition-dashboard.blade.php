<x-filament-panels::page>
    @if($competition)
        <div class="mb-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ $competition->display_name }}</h2>
                    <p class="text-gray-500 mt-1">{{ $competition->description }}</p>
                </div>
                <x-filament::badge :color="$competition->status->getColor()">
                    {{ $competition->status->getLabel() }}
                </x-filament::badge>
            </div>
            @if($competition->status->value === 'active')
                <div class="mt-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>التقدم</span>
                        <span class="font-medium">{{ $stats['progress'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-primary-600 h-3 rounded-full" style="width: {{ $stats['progress'] }}%"></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ $stats['remaining_days'] }} يوم متبقي</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $stats['total_tenants'] }}</div>
                    <div class="text-sm text-gray-500">المستأجرين</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $stats['total_branches'] }}</div>
                    <div class="text-sm text-gray-500">الفروع</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">{{ $stats['total_prizes'] }}</div>
                    <div class="text-sm text-gray-500">الجوائز</div>
                </div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">{{ number_format($stats['total_prize_value']) }}</div>
                    <div class="text-sm text-gray-500">قيمة الجوائز (ر.س)</div>
                </div>
            </x-filament::section>
        </div>

        @if(!empty($benchmarkSummary))
            <x-filament::section class="mb-6">
                <x-slot name="heading">📊 ملخص الأداء (ROI)</x-slot>
                <div class="text-center p-4">
                    <div class="text-4xl font-bold {{ ($benchmarkSummary['overall_improvement_score'] ?? 0) >= 50 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $benchmarkSummary['overall_improvement_score'] ?? 0 }}%
                    </div>
                    <div class="text-sm text-gray-500">نسبة التحسن الإجمالية</div>
                    <p class="mt-2">{{ $benchmarkSummary['summary'] ?? '' }}</p>
                </div>
            </x-filament::section>
        @endif
    @else
        <div class="text-center py-12">
            <x-heroicon-o-trophy class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium">لا توجد مسابقة محددة</h3>
        </div>
    @endif
</x-filament-panels::page>
