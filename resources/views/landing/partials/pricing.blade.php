{{-- Pricing Section --}}
<section id="pricing" class="py-16 sm:py-20 lg:py-32 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.pricingTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.pricingTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.pricingSubtitle') }}
            </p>
        </div>

        {{-- Pricing Cards --}}
        <div class="grid md:grid-cols-3 gap-6 sm:gap-8 max-w-6xl mx-auto">
            {{-- Starter Plan --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-gray-200 hover:border-blue-200 transition-colors">
                <h3 class="text-xl font-bold mb-2">{{ __('app.pricingStarterName') }}</h3>
                <div class="mb-6">
                    <span class="text-4xl font-extrabold">{{ __('app.pricingStarterPrice') }}</span>
                    <span class="text-gray-500 text-sm">{{ __('app.pricingStarterPeriod') }}</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingStarterFeature1') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingStarterFeature2') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingStarterFeature3') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingStarterFeature4') }}
                    </li>
                </ul>
                <a
                    href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                    target="_blank"
                    class="block w-full py-3 text-center rounded-full border-2 border-gray-300 text-gray-700 font-semibold hover:border-blue-500 hover:text-blue-600 transition-colors"
                >
                    {{ __('app.pricingStarterCTA') }}
                </a>
            </div>

            {{-- Pro Plan (Featured) --}}
            <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-6 sm:p-8 text-white relative transform md:-translate-y-4 shadow-xl">
                {{-- Badge --}}
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="bg-yellow-400 text-yellow-900 px-4 py-1 rounded-full text-sm font-bold">
                        {{ __('app.pricingProBadge') }}
                    </span>
                </div>

                <h3 class="text-xl font-bold mb-2 mt-2">{{ __('app.pricingProName') }}</h3>
                <div class="mb-6">
                    <span class="text-4xl font-extrabold">{{ __('app.pricingProPrice') }}</span>
                    <span class="text-blue-200 text-sm">{{ __('app.pricingProPeriod') }}</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingProFeature1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingProFeature2') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingProFeature3') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingProFeature4') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingProFeature5') }}
                    </li>
                </ul>
                <a
                    href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool"
                    target="_blank"
                    class="block w-full py-3 text-center rounded-full bg-white text-blue-600 font-semibold hover:bg-blue-50 transition-colors"
                >
                    {{ __('app.pricingProCTA') }}
                </a>
            </div>

            {{-- Enterprise Plan --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-gray-200 hover:border-blue-200 transition-colors">
                <h3 class="text-xl font-bold mb-2">{{ __('app.pricingEnterpriseName') }}</h3>
                <div class="mb-6">
                    <span class="text-2xl font-extrabold">{{ __('app.pricingEnterprisePrice') }}</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingEnterpriseFeature1') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingEnterpriseFeature2') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingEnterpriseFeature3') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingEnterpriseFeature4') }}
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.pricingEnterpriseFeature5') }}
                    </li>
                </ul>
                <a
                    href="mailto:sales@tabsense.net"
                    class="block w-full py-3 text-center rounded-full border-2 border-gray-300 text-gray-700 font-semibold hover:border-blue-500 hover:text-blue-600 transition-colors"
                >
                    {{ __('app.pricingEnterpriseCTA') }}
                </a>
            </div>
        </div>

        {{-- Free Trial Note --}}
        <div class="text-center mt-12">
            <p class="text-gray-600 mb-4">{{ __('app.pricingFreeTrialNote') }}</p>
            <a
                href="{{ route('get-started') }}"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold"
            >
                {{ __('app.pricingFreeTrial') }}
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>
