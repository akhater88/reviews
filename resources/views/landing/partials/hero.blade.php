{{-- Enhanced Hero Section --}}
<section class="relative min-h-[90vh] overflow-hidden bg-gradient-to-br from-indigo-50/80 via-white to-purple-50/50">
    {{-- Animated Background Elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        {{-- Floating Shapes --}}
        <div class="absolute top-20 right-10 w-72 h-72 bg-indigo-200/30 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl animate-float-slower"></div>
        <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-red-200/20 rounded-full blur-3xl animate-float"></div>

        {{-- Grid Pattern --}}
        <div class="absolute inset-0 bg-grid-pattern opacity-[0.02]"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 pb-16 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left Content --}}
            <div class="text-center lg:text-right order-2 lg:order-1">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-full shadow-sm mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-medium text-gray-700">{{ __('app.heroTagline') }}</span>
                </div>

                {{-- Main Heading --}}
                <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-normal sm:leading-relaxed mb-6">
                    <span class="block">حوّل تقييمات عملائك</span>
                    <span class="block mt-3">
                        إلى
                        <span class="relative inline-block py-1">
                            <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-l from-indigo-600 to-purple-600">
                                رؤى قابلة للتنفيذ
                            </span>
                            <span class="absolute bottom-1 sm:bottom-2 right-0 left-0 h-3 sm:h-4 bg-indigo-200/50 -z-10 transform -skew-x-3"></span>
                        </span>
                    </span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-lg sm:text-xl text-gray-600 leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0 lg:mr-0">
                    {{ __('app.heroSubtitle') }}
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-end mb-8">
                    {{-- Primary CTA --}}
                    <a
                        href="{{ route('get-started') }}"
                        class="group relative inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-bold text-lg rounded-2xl shadow-lg shadow-red-500/25 hover:shadow-xl hover:shadow-red-500/30 transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                    >
                        <span class="relative z-10">{{ __('app.heroCTAPrimary') }}</span>
                        <svg class="w-5 h-5 relative z-10 group-hover:-translate-x-1 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                        {{-- Shine Effect --}}
                        <div class="absolute inset-0 bg-gradient-to-l from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                    </a>

                    {{-- Secondary CTA --}}
                    <a
                        href="#how-it-works"
                        onclick="event.preventDefault(); document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="group inline-flex items-center justify-center gap-3 px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 font-semibold rounded-2xl hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                    >
                        <span class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5 text-blue-600 mr-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        <span>{{ __('app.heroCTASecondary') }}</span>
                    </a>
                </div>

                {{-- Trust Indicators --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-end gap-6 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span>{{ __('app.heroTrust1') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span>{{ __('app.heroTrust2') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span>{{ __('app.heroTrust3') }}</span>
                    </div>
                </div>
            </div>

            {{-- Right Content - Dashboard Mockup --}}
            <div class="order-1 lg:order-2 relative">
                <div class="relative mx-auto max-w-lg lg:max-w-none">
                    {{-- Main Dashboard Card --}}
                    <div class="relative bg-white rounded-3xl shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform hover:scale-[1.02] transition-transform duration-500">
                        {{-- Browser Header --}}
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-400 flex items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span dir="ltr">app.getsumaa.app</span>
                                </div>
                            </div>
                        </div>

                        {{-- Dashboard Content --}}
                        <div class="p-6">
                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">لوحة تحكم سُمعة</p>
                                        <p class="text-xs text-gray-500">مطعم الشرق</p>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-1">
                                        <span class="text-lg font-bold text-gray-900">4.8</span>
                                        <div class="flex">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= 4 ? 'text-yellow-400 fill-yellow-400' : 'text-yellow-400' }}" viewBox="0 0 24 24" fill="{{ $i <= 4 ? 'currentColor' : 'none' }}" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">متوسط التقييم</p>
                                </div>
                            </div>

                            {{-- Stats Grid --}}
                            <div class="grid grid-cols-3 gap-3 mb-6">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-bold text-blue-600">847</p>
                                    <p class="text-xs text-blue-700/70">تقييم</p>
                                </div>
                                <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-bold text-green-600">92%</p>
                                    <p class="text-xs text-green-700/70">إيجابي</p>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-bold text-purple-600">+12%</p>
                                    <p class="text-xs text-purple-700/70">هذا الشهر</p>
                                </div>
                            </div>

                            {{-- Mini Chart --}}
                            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-700">تحليل المشاعر</span>
                                    <span class="text-xs text-green-600 bg-green-100 px-2 py-0.5 rounded-full">↑ 5%</span>
                                </div>
                                <div class="h-16 flex items-end gap-1">
                                    @php $chartBars = [40, 55, 45, 60, 50, 70, 65, 80, 75, 85, 90, 95]; @endphp
                                    @foreach ($chartBars as $index => $height)
                                        <div
                                            class="flex-1 rounded-t transition-all duration-500 {{ $index === count($chartBars) - 1 ? 'bg-blue-500' : 'bg-blue-200' }}"
                                            style="height: {{ $height }}%"
                                        ></div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- AI Recommendation Preview --}}
                            <div class="bg-gradient-to-l from-purple-50 to-blue-50 border border-purple-100 rounded-xl p-3">
                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-purple-900">توصية ذكية</p>
                                        <p class="text-xs text-purple-700/80">تحسين سرعة الخدمة سيرفع تقييمك بنسبة 15%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating Elements --}}
                    {{-- Rating Badge --}}
                    <div class="absolute -left-4 top-1/4 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-4 border border-gray-100 animate-float hidden lg:block">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white fill-white" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">4.8</p>
                                <p class="text-xs text-gray-500">متوسط تحسن التقييم</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Badge --}}
                    <div class="absolute -right-4 bottom-1/4 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-3 border border-gray-100 animate-float-delayed hidden lg:block">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">+23 تقييم</p>
                                <p class="text-xs text-gray-500">هذا الأسبوع</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Wave --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
        </svg>
    </div>
</section>
