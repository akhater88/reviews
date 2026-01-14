{{-- Enhanced Landing Page Header --}}
<header
    x-data="{
        scrolled: false,
        mobileMenuOpen: false,
        init() {
            window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 50 });
        }
    }"
    :class="scrolled ? 'shadow-lg shadow-black/20' : ''"
    class="fixed top-0 left-0 right-0 z-50 bg-gray-900 transition-all duration-300"
>
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2 sm:gap-3 group">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="TABsense Logo"
                    class="h-8 sm:h-10 w-auto brightness-0 invert transition-transform group-hover:scale-105"
                />
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex items-center gap-1 xl:gap-2">
                <a
                    href="#features"
                    class="relative px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors text-sm group"
                    @click.prevent="document.getElementById('features').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    <span class="relative z-10">{{ __('app.navFeatures') }}</span>
                    <span class="absolute inset-0 bg-gray-800 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <a
                    href="#how-it-works"
                    class="relative px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors text-sm group"
                    @click.prevent="document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    <span class="relative z-10">{{ __('app.navHowItWorks') }}</span>
                    <span class="absolute inset-0 bg-gray-800 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <a
                    href="#pricing"
                    class="relative px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors text-sm group"
                    @click.prevent="document.getElementById('pricing').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    <span class="relative z-10">{{ __('app.navPricing') }}</span>
                    <span class="absolute inset-0 bg-gray-800 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <a
                    href="#faq"
                    class="relative px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors text-sm group"
                    @click.prevent="document.getElementById('faq').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    <span class="relative z-10">{{ __('app.navFAQ') }}</span>
                    <span class="absolute inset-0 bg-gray-800 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
            </div>

            {{-- Right Side Actions --}}
            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Subscribe Button (Desktop) --}}
                <a
                    href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                    target="_blank"
                    class="hidden sm:inline-flex items-center gap-2 text-gray-300 hover:text-white font-medium transition-colors text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    {{ __('app.navSubscribe') }}
                </a>

                {{-- CTA Button --}}
                <a
                    href="{{ route('get-started') }}"
                    class="group relative inline-flex items-center gap-2 bg-gradient-to-l from-[#df625b] to-[#e87b75] hover:from-[#c55550] hover:to-[#d66d67] text-white px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold text-sm transition-all shadow-lg shadow-red-500/30 hover:shadow-xl hover:shadow-red-500/40 hover:-translate-y-0.5 overflow-hidden"
                >
                    <span class="relative z-10">{{ __('app.navGetStarted') }}</span>
                    <svg class="w-4 h-4 relative z-10 group-hover:-translate-x-1 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                    {{-- Shine Effect --}}
                    <div class="absolute inset-0 bg-gradient-to-l from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                </a>

                {{-- Mobile Menu Button --}}
                <button
                    @click="mobileMenuOpen = true"
                    class="lg:hidden p-2.5 text-gray-300 hover:text-white hover:bg-gray-800 rounded-xl transition-colors"
                    aria-label="Toggle menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- Mobile Menu Overlay --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileMenuOpen = false"
        x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
    ></div>

    {{-- Mobile Menu Slide-in Panel --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-cloak
        class="fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-gray-900 shadow-2xl z-50 lg:hidden overflow-y-auto"
    >
        {{-- Menu Header --}}
        <div class="sticky top-0 bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-2">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="TABsense Logo"
                    class="h-8 w-auto brightness-0 invert"
                />
            </a>
            <button
                @click="mobileMenuOpen = false"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-xl transition-colors"
                aria-label="Close menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Menu Content --}}
        <div class="p-6">
            {{-- Navigation Links --}}
            <nav class="space-y-2">
                <a
                    href="#features"
                    class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-800 transition-colors group"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('features').scrollIntoView({ behavior: 'smooth', block: 'start' }), 150)"
                >
                    <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ __('app.navFeatures') }}</p>
                        <p class="text-sm text-gray-400">اكتشف ميزات المنصة</p>
                    </div>
                </a>

                <a
                    href="#how-it-works"
                    class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-800 transition-colors group"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' }), 150)"
                >
                    <div class="w-10 h-10 bg-purple-500/20 rounded-xl flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ __('app.navHowItWorks') }}</p>
                        <p class="text-sm text-gray-400">كيف تعمل المنصة</p>
                    </div>
                </a>

                <a
                    href="#pricing"
                    class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-800 transition-colors group"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('pricing').scrollIntoView({ behavior: 'smooth', block: 'start' }), 150)"
                >
                    <div class="w-10 h-10 bg-green-500/20 rounded-xl flex items-center justify-center group-hover:bg-green-500/30 transition-colors">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ __('app.navPricing') }}</p>
                        <p class="text-sm text-gray-400">الأسعار والباقات</p>
                    </div>
                </a>

                <a
                    href="#faq"
                    class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-800 transition-colors group"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('faq').scrollIntoView({ behavior: 'smooth', block: 'start' }), 150)"
                >
                    <div class="w-10 h-10 bg-orange-500/20 rounded-xl flex items-center justify-center group-hover:bg-orange-500/30 transition-colors">
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ __('app.navFAQ') }}</p>
                        <p class="text-sm text-gray-400">الأسئلة الشائعة</p>
                    </div>
                </a>
            </nav>

            {{-- Divider --}}
            <hr class="my-6 border-gray-800" />

            {{-- Subscribe Link --}}
            <a
                href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                target="_blank"
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-800 transition-colors group"
            >
                <div class="w-10 h-10 bg-gray-700 rounded-xl flex items-center justify-center group-hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-white">{{ __('app.navSubscribe') }}</p>
                    <p class="text-sm text-gray-400">سجل للحصول على التحديثات</p>
                </div>
            </a>

            {{-- CTA Button --}}
            <div class="mt-6">
                <a
                    href="{{ route('get-started') }}"
                    @click="mobileMenuOpen = false"
                    class="flex items-center justify-center gap-2 w-full bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-red-500/30"
                >
                    {{ __('app.navGetStarted') }}
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>

            {{-- Footer Info --}}
            <div class="mt-8 pt-6 border-t border-gray-800">
                <p class="text-sm text-gray-500 text-center">
                    تحتاج مساعدة؟
                    <a href="mailto:support@tabsense.ai" class="text-blue-400 hover:underline">تواصل معنا</a>
                </p>
            </div>
        </div>
    </div>
</header>
