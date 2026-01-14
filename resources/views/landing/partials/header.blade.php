{{-- Landing Page Header --}}
<header
    x-data="{ scrolled: false, mobileMenuOpen: false }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 })"
    :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-transparent'"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
>
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2 sm:gap-3">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="TABsense Logo"
                    class="h-8 sm:h-10 w-auto"
                />
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden lg:flex items-center gap-6 xl:gap-8">
                <a
                    href="#features"
                    class="text-gray-600 hover:text-blue-600 font-medium transition-colors text-sm"
                    @click.prevent="document.getElementById('features').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    {{ __('app.navFeatures') }}
                </a>
                <a
                    href="#how-it-works"
                    class="text-gray-600 hover:text-blue-600 font-medium transition-colors text-sm"
                    @click.prevent="document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    {{ __('app.navHowItWorks') }}
                </a>
                <a
                    href="#pricing"
                    class="text-gray-600 hover:text-blue-600 font-medium transition-colors text-sm"
                    @click.prevent="document.getElementById('pricing').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    {{ __('app.navPricing') }}
                </a>
                <a
                    href="#faq"
                    class="text-gray-600 hover:text-blue-600 font-medium transition-colors text-sm"
                    @click.prevent="document.getElementById('faq').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                >
                    {{ __('app.navFAQ') }}
                </a>
            </div>

            {{-- Right Side Actions --}}
            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Subscribe Button (Desktop) --}}
                <a
                    href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                    target="_blank"
                    class="hidden sm:inline-flex text-gray-600 hover:text-blue-600 font-medium transition-colors text-sm"
                >
                    {{ __('app.navSubscribe') }}
                </a>

                {{-- CTA Button --}}
                <a
                    href="{{ route('get-started') }}"
                    class="bg-[#df625b] hover:bg-[#c55550] text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-semibold text-sm transition-all hover:shadow-lg"
                >
                    {{ __('app.navGetStarted') }}
                </a>

                {{-- Mobile Menu Button --}}
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-2 text-gray-600"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            x-cloak
            class="lg:hidden bg-white border-t py-4"
        >
            <div class="flex flex-col gap-2">
                <a
                    href="#features"
                    class="text-gray-600 hover:text-blue-600 font-medium px-4 py-3"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('features').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                >
                    {{ __('app.navFeatures') }}
                </a>
                <a
                    href="#how-it-works"
                    class="text-gray-600 hover:text-blue-600 font-medium px-4 py-3"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('how-it-works').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                >
                    {{ __('app.navHowItWorks') }}
                </a>
                <a
                    href="#pricing"
                    class="text-gray-600 hover:text-blue-600 font-medium px-4 py-3"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('pricing').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                >
                    {{ __('app.navPricing') }}
                </a>
                <a
                    href="#faq"
                    class="text-gray-600 hover:text-blue-600 font-medium px-4 py-3"
                    @click="mobileMenuOpen = false; setTimeout(() => document.getElementById('faq').scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)"
                >
                    {{ __('app.navFAQ') }}
                </a>
                <hr class="my-2" />
                <a
                    href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                    target="_blank"
                    class="text-gray-600 hover:text-blue-600 font-medium px-4 py-3"
                >
                    {{ __('app.navSubscribe') }}
                </a>
                <a
                    href="{{ route('get-started') }}"
                    class="bg-[#df625b] text-white text-center mx-4 py-3 rounded-full font-semibold"
                    @click="mobileMenuOpen = false"
                >
                    {{ __('app.navGetStarted') }}
                </a>
            </div>
        </div>
    </nav>
</header>
