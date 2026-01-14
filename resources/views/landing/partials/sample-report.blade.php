{{-- Sample Report Section --}}
<section class="py-16 sm:py-20 lg:py-32 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.sampleReportTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.sampleReportTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.sampleReportSubtitle') }}
            </p>
        </div>

        {{-- Report Preview --}}
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-4 sm:p-6 lg:p-8 shadow-xl border border-blue-100">
                {{-- Overview Section --}}
                <div class="bg-white rounded-2xl p-4 sm:p-6 mb-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        {{ __('app.sampleOverview') }}
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="text-center p-3 sm:p-4 bg-yellow-50 rounded-xl">
                            <div class="text-2xl sm:text-3xl font-bold text-yellow-600">4.5</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ __('app.sampleRating') }} ⭐</div>
                        </div>
                        <div class="text-center p-3 sm:p-4 bg-blue-50 rounded-xl">
                            <div class="text-2xl sm:text-3xl font-bold text-blue-600">1,234</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ __('app.sampleReviews') }}</div>
                        </div>
                        <div class="text-center p-3 sm:p-4 bg-green-50 rounded-xl">
                            <div class="text-2xl sm:text-3xl font-bold text-green-600">78%</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ __('app.samplePositive') }} 😊</div>
                        </div>
                        <div class="text-center p-3 sm:p-4 bg-purple-50 rounded-xl">
                            <div class="text-2xl sm:text-3xl font-bold text-purple-600">+12%</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ __('app.sampleImprovement') }} 📈</div>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    {{-- Recommendations --}}
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            {{ __('app.sampleRecommendationsTitle') }}
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3 text-sm sm:text-base">
                                <span class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span class="text-gray-700">{{ __('app.sampleRecommendation1') }}</span>
                            </li>
                            <li class="flex items-start gap-3 text-sm sm:text-base">
                                <span class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span class="text-gray-700">{{ __('app.sampleRecommendation2') }}</span>
                            </li>
                            <li class="flex items-start gap-3 text-sm sm:text-base">
                                <span class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span class="text-gray-700">{{ __('app.sampleRecommendation3') }}</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Keywords --}}
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                            {{ __('app.sampleKeywordsTitle') }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-green-600 font-medium mb-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                    </svg>
                                    إيجابي
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['ممتاز', 'لذيذ', 'نظيف', 'سريع'] as $keyword)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                            {{ $keyword }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-red-600 font-medium mb-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                                    </svg>
                                    يحتاج تحسين
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['بطيء', 'مزدحم', 'غالي'] as $keyword)
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                            {{ $keyword }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="text-center mt-8 sm:mt-12">
                <a
                    href="{{ route('get-started') }}"
                    class="inline-flex items-center gap-2 bg-[#df625b] hover:bg-[#c55550] text-white px-8 py-4 rounded-full font-semibold text-lg transition-all hover:shadow-xl"
                >
                    {{ __('app.sampleCTA') }}
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
