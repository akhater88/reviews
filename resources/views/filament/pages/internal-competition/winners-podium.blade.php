<x-filament-panels::page>
    @if($competition)
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold mb-2">🏆 الفائزون 🏆</h1>
            <h2 class="text-xl text-gray-600">{{ $competition->display_name }}</h2>
        </div>

        @foreach($podiums as $metricValue => $podiumData)
            <x-filament::section class="mb-8">
                <x-slot name="heading">{{ $podiumData['metric_label'] ?? $metricValue }}</x-slot>
                <div class="flex justify-center items-end gap-4 py-8">
                    {{-- 2nd Place --}}
                    <div class="text-center">
                        @if(isset($podiumData['podium']['second']))
                            <div class="text-4xl mb-2">🥈</div>
                            <div class="font-bold">{{ $podiumData['podium']['second']['winner_name'] ?? '' }}</div>
                            <div class="text-sm text-gray-500">{{ $podiumData['podium']['second']['branch_name'] ?? '' }}</div>
                            <div class="text-lg font-semibold text-info-600">{{ number_format($podiumData['podium']['second']['score'] ?? 0, 0) }} نقطة</div>
                            <div class="w-24 h-20 bg-gray-300 mx-auto mt-4 rounded-t-lg flex items-center justify-center text-2xl font-bold">2</div>
                        @endif
                    </div>
                    {{-- 1st Place --}}
                    <div class="text-center">
                        @if(isset($podiumData['podium']['first']))
                            <div class="text-5xl mb-2">🥇</div>
                            <div class="font-bold text-lg">{{ $podiumData['podium']['first']['winner_name'] ?? '' }}</div>
                            <div class="text-sm text-gray-500">{{ $podiumData['podium']['first']['branch_name'] ?? '' }}</div>
                            <div class="text-xl font-semibold text-success-600">{{ number_format($podiumData['podium']['first']['score'] ?? 0, 0) }} نقطة</div>
                            <div class="w-28 h-28 bg-yellow-400 mx-auto mt-4 rounded-t-lg flex items-center justify-center text-3xl font-bold">1</div>
                        @endif
                    </div>
                    {{-- 3rd Place --}}
                    <div class="text-center">
                        @if(isset($podiumData['podium']['third']))
                            <div class="text-3xl mb-2">🥉</div>
                            <div class="font-bold">{{ $podiumData['podium']['third']['winner_name'] ?? '' }}</div>
                            <div class="text-sm text-gray-500">{{ $podiumData['podium']['third']['branch_name'] ?? '' }}</div>
                            <div class="text-lg font-semibold text-warning-600">{{ number_format($podiumData['podium']['third']['score'] ?? 0, 0) }} نقطة</div>
                            <div class="w-20 h-16 bg-orange-400 mx-auto mt-4 rounded-t-lg flex items-center justify-center text-2xl font-bold">3</div>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    @else
        <div class="text-center py-12">
            <x-heroicon-o-trophy class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2">لا توجد نتائج</h3>
        </div>
    @endif
</x-filament-panels::page>
