<x-filament-panels::page>
    {{-- Header Section --}}
    <div class="mb-6" dir="rtl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Branch Info --}}
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-xl flex items-center justify-center">
                    <x-heroicon-o-building-storefront class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $branch->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($branch->city && $branch->country)
                            {{ $branch->city }}، {{ $branch->country }}
                        @elseif($branch->address)
                            {{ $branch->address }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                @if($latestAnalysis)
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        آخر تحليل: {{ $latestAnalysis->completed_at?->diffForHumans() ?? $latestAnalysis->created_at->diffForHumans() }}
                    </div>
                @endif

                <x-filament::button wire:click="startNewAnalysis" icon="heroicon-o-arrow-path">
                    تحليل جديد
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- No Data State --}}
    @if(!$hasData)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center" dir="rtl">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-chart-bar class="w-10 h-10 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا توجد بيانات تحليل</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">لم يتم تحليل مراجعات هذا الفرع بعد. ابدأ التحليل الآن للحصول على رؤى مفصلة.</p>
            <x-filament::button wire:click="startNewAnalysis" icon="heroicon-o-play">
                بدء التحليل
            </x-filament::button>
        </div>
    @else
        {{-- Tabs Navigation --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl mb-6" dir="rtl">
            <nav class="flex overflow-x-auto border-b border-gray-200 dark:border-gray-700">
                @php
                    $tabs = [
                        'overview' => ['label' => 'نظرة عامة', 'icon' => 'heroicon-o-chart-pie'],
                        'categories' => ['label' => 'الفئات', 'icon' => 'heroicon-o-tag'],
                        'demographics' => ['label' => 'الديموغرافيا', 'icon' => 'heroicon-o-users'],
                        'employees' => ['label' => 'الموظفين', 'icon' => 'heroicon-o-user-group'],
                        'items' => ['label' => 'العناصر', 'icon' => 'heroicon-o-squares-2x2'],
                        'recommendations' => ['label' => 'التوصيات', 'icon' => 'heroicon-o-light-bulb'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <button
                        wire:click="setActiveTab('{{ $key }}')"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                            {{ $activeTab === $key
                                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                    >
                        <x-dynamic-component :component="$tab['icon']" class="w-5 h-5" />
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="space-y-6">
            @if($activeTab === 'overview')
                @livewire('branch-report.overview-tab', ['branch' => $branch, 'data' => $this->getOverviewData(), 'sentiment' => $this->getSentimentData(), 'operational' => $this->getOperationalData()], key('overview-tab'))
            @elseif($activeTab === 'categories')
                @livewire('branch-report.categories-tab', ['branch' => $branch, 'data' => $this->getCategoryData()], key('categories-tab'))
            @elseif($activeTab === 'demographics')
                @livewire('branch-report.demographics-tab', ['branch' => $branch, 'data' => $this->getGenderData()], key('demographics-tab'))
            @elseif($activeTab === 'employees')
                @livewire('branch-report.employees-tab', ['branch' => $branch, 'data' => $this->getEmployeesData()], key('employees-tab'))
            @elseif($activeTab === 'items')
                @livewire('branch-report.items-tab', ['branch' => $branch, 'data' => $this->getKeywordsData()], key('items-tab'))
            @elseif($activeTab === 'recommendations')
                @livewire('branch-report.recommendations-tab', ['branch' => $branch, 'data' => $this->getRecommendationsData()], key('recommendations-tab'))
            @endif
        </div>
    @endif
</x-filament-panels::page>
