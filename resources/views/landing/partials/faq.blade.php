{{-- FAQ Section --}}
<section id="faq" class="py-16 sm:py-20 lg:py-32 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.faqTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.faqTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.faqSubtitle') }}
            </p>
        </div>

        {{-- FAQ Items --}}
        <div class="max-w-3xl mx-auto space-y-4" x-data="{ openFaq: null }">
            @php
                $faqs = [
                    ['q' => __('app.faqQuestion1'), 'a' => __('app.faqAnswer1')],
                    ['q' => __('app.faqQuestion2'), 'a' => __('app.faqAnswer2')],
                    ['q' => __('app.faqQuestion3'), 'a' => __('app.faqAnswer3')],
                    ['q' => __('app.faqQuestion4'), 'a' => __('app.faqAnswer4')],
                    ['q' => __('app.faqQuestion5'), 'a' => __('app.faqAnswer5')],
                    ['q' => __('app.faqQuestion6'), 'a' => __('app.faqAnswer6')],
                    ['q' => __('app.faqQuestion7'), 'a' => __('app.faqAnswer7')],
                    ['q' => __('app.faqQuestion8'), 'a' => __('app.faqAnswer8')],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <button
                        @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-4 sm:py-5 flex items-center justify-between text-right hover:bg-gray-50 transition-colors"
                    >
                        <span class="font-semibold text-gray-900 text-sm sm:text-base">
                            {{ $faq['q'] }}
                        </span>
                        <svg
                            class="w-5 h-5 text-gray-500 flex-shrink-0 mr-4 transform transition-transform"
                            :class="openFaq === {{ $index }} ? 'rotate-180' : ''"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div
                        x-show="openFaq === {{ $index }}"
                        x-collapse
                        x-cloak
                        class="px-6 pb-5 text-gray-600 text-sm sm:text-base leading-relaxed"
                    >
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
