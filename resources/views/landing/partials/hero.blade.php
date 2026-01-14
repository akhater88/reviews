{{-- Hero Section --}}
<section class="relative min-h-screen overflow-hidden pt-20 bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Decorative Blobs --}}
    <div class="absolute w-72 h-72 sm:w-96 sm:h-96 bg-blue-200/50 rounded-full blur-3xl -top-20 -right-20 opacity-60"></div>
    <div class="absolute w-64 h-64 sm:w-80 sm:h-80 bg-purple-200/50 rounded-full blur-3xl bottom-20 -left-20 opacity-60"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            {{-- Text Content --}}
            <div class="text-center lg:text-right order-2 lg:order-1 animate-fade-in-up">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                    </span>
                    {{ __('app.heroTagline') }}
                </div>

                {{-- Main Heading --}}
                <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold leading-tight mb-6">
                    {{ __('app.heroTitle1') }}
                    <span class="text-transparent bg-clip-text bg-gradient-to-l from-blue-600 to-purple-600">
                        {{ __('app.heroTitleHighlight') }}
                    </span>
                </h1>

                {{-- Subheading --}}
                <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-xl mx-auto lg:mx-0 lg:mr-0">
                    {{ __('app.heroSubtitle') }}
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a
                        href="{{ route('get-started') }}"
                        class="bg-[#df625b] hover:bg-[#c55550] text-white px-6 sm:px-8 py-3 sm:py-4 rounded-full font-semibold text-base sm:text-lg transition-all hover:shadow-xl hover:shadow-red-500/20 flex items-center justify-center gap-2"
                    >
                        {{ __('app.heroCTAPrimary') }}
                        <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a
                        href="#how-it-works"
                        @click.prevent="document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="bg-white hover:bg-gray-50 text-gray-800 px-6 sm:px-8 py-3 sm:py-4 rounded-full font-semibold text-base sm:text-lg border-2 border-gray-200 transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('app.heroCTASecondary') }}
                    </a>
                </div>

                {{-- Trust Indicators --}}
                <div class="mt-10 sm:mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-gray-500 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('app.heroTrust1') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('app.heroTrust2') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('app.heroTrust3') }}</span>
                    </div>
                </div>
            </div>

            {{-- Dashboard Preview --}}
            <div class="relative order-1 lg:order-2 animate-fade-in-left">
                <div class="relative z-10">
                    {{-- Main Dashboard Image --}}
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
                        <div class="bg-gray-100 px-4 py-3 flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        {{-- Dashboard Preview Placeholder --}}
                        <div class="aspect-video bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-8">
                            <div class="text-center">
                                <svg class="w-16 h-16 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg">لوحة تحكم TABsense</p>
                            </div>
                        </div>
                    </div>

                    {{-- Floating Stats Card - Reviews --}}
                    <div class="absolute -bottom-4 sm:-bottom-6 -right-2 sm:-right-6 bg-white rounded-xl shadow-xl p-3 sm:p-4 border border-gray-100 animate-float">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900">+47%</div>
                                <div class="text-xs sm:text-sm text-gray-500">{{ __('app.heroStatReviews') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating Rating Card --}}
                    <div class="absolute -top-2 sm:-top-4 -left-2 sm:-left-4 bg-white rounded-xl shadow-xl p-3 sm:p-4 border border-gray-100 animate-float-delayed">
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="font-bold text-sm sm:text-base">4.8</span>
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 mt-1">{{ __('app.heroStatRating') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce hidden sm:block">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
</section>
