<x-filament-panels::page>
    @if($competition)
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold">{{ $competition->display_name }}</h2>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            @foreach($competition->enabled_metrics as $metric)
                <x-filament::button
                    :color="$selectedMetric === $metric->value ? 'primary' : 'gray'"
                    wire:click="selectMetric('{{ $metric->value }}')"
                >
                    {{ $metric->getLabel() }}
                </x-filament::button>
            @endforeach
        </div>

        {{ $this->table }}
    @else
        <div class="text-center py-12">
            <x-heroicon-o-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2">لا توجد مسابقة</h3>
        </div>
    @endif
</x-filament-panels::page>
