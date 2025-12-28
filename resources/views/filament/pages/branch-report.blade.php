<x-filament-panels::page>
    {{-- Main Container with Gradient Background --}}
    <div class="min-h-screen -mx-4 -my-4 sm:-mx-6 sm:-my-6 lg:-mx-8 px-4 py-6 sm:px-6 lg:px-8" style="background: linear-gradient(to bottom right, rgb(239 246 255), rgb(224 231 255));" dir="rtl">

        @if(!$this->hasAnalysisData())
            {{-- No Data State --}}
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-chart-bar class="w-10 h-10 text-gray-400" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">لا توجد بيانات تحليل</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">لم يتم تحليل مراجعات هذا الفرع بعد. ابدأ التحليل الآن للحصول على رؤى مفصلة.</p>
                    <x-filament::button wire:click="startNewAnalysis" icon="heroicon-o-play" size="lg">
                        بدء التحليل
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Restaurant Header --}}
                @include('filament.pages.branch-report.partials.restaurant-header')

                {{-- Tabs Navigation --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <nav class="flex overflow-x-auto">
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
                                class="flex items-center gap-2 px-5 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors flex-1 justify-center
                                    {{ $activeTab === $key
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/20'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                            >
                                <x-dynamic-component :component="$tab['icon']" class="w-5 h-5" />
                                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="space-y-6">
                    @if($activeTab === 'overview')
                        @include('filament.pages.branch-report.partials.overview-section')
                    @elseif($activeTab === 'categories')
                        @livewire('branch-report.categories-tab', ['branch' => $branch, 'data' => $this->getCategoryData()], key('categories-tab'))
                    @elseif($activeTab === 'demographics')
                        @livewire('branch-report.demographics-tab', ['branch' => $branch, 'data' => $this->getGenderData()], key('demographics-tab'))
                    @elseif($activeTab === 'employees')
                        @livewire('branch-report.employees-tab', ['branch' => $branch, 'data' => $this->getEmployeesData()], key('employees-tab'))
                    @elseif($activeTab === 'items')
                        @include('filament.pages.branch-report.partials.keywords-section')
                    @elseif($activeTab === 'recommendations')
                        @include('filament.pages.branch-report.partials.recommendations-section')
                    @endif
                </div>

                {{-- Footer --}}
                <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                    <p>تم إنشاء هذا التقرير بواسطة تابسينس - {{ now()->format('Y/m/d') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
