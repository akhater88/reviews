{{-- How It Works Section --}}
<section id="how-it-works" class="py-16 sm:py-20 lg:py-32 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.howItWorksTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.howItWorksTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.howItWorksSubtitle') }}
            </p>
        </div>

        {{-- Steps --}}
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 sm:gap-12 relative">
                {{-- Connecting Line (Desktop) --}}
                <div class="hidden md:block absolute top-16 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-blue-200 via-purple-200 to-green-200"></div>

                {{-- Step 1 --}}
                <div class="text-center relative">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 bg-blue-100 rounded-full flex items-center justify-center mx-auto relative z-10">
                            <svg class="w-10 h-10 sm:w-14 sm:h-14 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            1
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-3">{{ __('app.howItWorksStep1Title') }}</h3>
                    <p class="text-gray-600 text-sm sm:text-base">{{ __('app.howItWorksStep1Desc') }}</p>
                </div>

                {{-- Step 2 --}}
                <div class="text-center relative">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 bg-purple-100 rounded-full flex items-center justify-center mx-auto relative z-10">
                            <svg class="w-10 h-10 sm:w-14 sm:h-14 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            2
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-3">{{ __('app.howItWorksStep2Title') }}</h3>
                    <p class="text-gray-600 text-sm sm:text-base">{{ __('app.howItWorksStep2Desc') }}</p>
                </div>

                {{-- Step 3 --}}
                <div class="text-center relative">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 bg-green-100 rounded-full flex items-center justify-center mx-auto relative z-10">
                            <svg class="w-10 h-10 sm:w-14 sm:h-14 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            3
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold mb-3">{{ __('app.howItWorksStep3Title') }}</h3>
                    <p class="text-gray-600 text-sm sm:text-base">{{ __('app.howItWorksStep3Desc') }}</p>
                </div>
            </div>

            {{-- CTA --}}
            <div class="text-center mt-12 sm:mt-16">
                <a
                    href="{{ route('get-started') }}"
                    class="inline-flex items-center gap-2 bg-[#df625b] hover:bg-[#c55550] text-white px-8 py-4 rounded-full font-semibold text-lg transition-all hover:shadow-xl"
                >
                    {{ __('app.howItWorksCTA') }}
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
