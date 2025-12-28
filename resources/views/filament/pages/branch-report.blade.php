<x-filament-panels::page>
    {{-- Main Container with Gradient Background --}}
    <div class="min-h-screen -m-6 p-6 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800" dir="rtl">

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

                {{-- Section 1: Overview --}}
                @include('filament.pages.branch-report.partials.overview-section')

                {{-- Section 2: Keywords --}}
                @include('filament.pages.branch-report.partials.keywords-section')

                {{-- Section 3: Recommendations --}}
                @include('filament.pages.branch-report.partials.recommendations-section')

                {{-- Footer --}}
                <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                    <p>تم إنشاء هذا التقرير بواسطة تابسينس - {{ now()->format('Y/m/d') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
