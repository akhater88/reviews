{{-- CTA Section --}}
<section class="py-16 sm:py-20 lg:py-32 bg-gradient-to-br from-blue-600 to-purple-700">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-6">
                {{ __('app.ctaTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-blue-100 mb-8">
                {{ __('app.ctaSubtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a
                    href="{{ route('get-started') }}"
                    class="bg-white hover:bg-blue-50 text-blue-600 px-8 py-4 rounded-full font-semibold text-lg transition-all hover:shadow-xl flex items-center justify-center gap-2"
                >
                    {{ __('app.ctaPrimary') }}
                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                <a
                    href="mailto:support@tabsense.net"
                    class="bg-transparent hover:bg-white/10 text-white px-8 py-4 rounded-full font-semibold text-lg border-2 border-white/50 transition-all flex items-center justify-center gap-2"
                >
                    {{ __('app.ctaSecondary') }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-blue-200 text-sm">
                {{ __('app.ctaContact') }} support@tabsense.net
            </p>
        </div>
    </div>
</section>
