{{-- Testimonials Section --}}
<section class="py-16 sm:py-20 lg:py-32 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.testimonialsTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.testimonialsTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.testimonialsSubtitle') }}
            </p>
        </div>

        {{-- Testimonials Grid --}}
        <div class="grid md:grid-cols-3 gap-6 sm:gap-8 max-w-6xl mx-auto">
            {{-- Testimonial 1 --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                {{-- Quote Icon --}}
                <svg class="w-10 h-10 text-blue-100 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>

                {{-- Quote --}}
                <p class="text-gray-700 mb-6 leading-relaxed text-sm sm:text-base">
                    "{{ __('app.testimonial1Quote') }}"
                </p>

                {{-- Rating --}}
                <div class="flex text-yellow-400 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    @endfor
                </div>

                {{-- Author --}}
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                        م
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">{{ __('app.testimonial1Author') }}</div>
                        <div class="text-sm text-gray-500">{{ __('app.testimonial1Role') }} - {{ __('app.testimonial1Location') }}</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                {{-- Quote Icon --}}
                <svg class="w-10 h-10 text-blue-100 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>

                {{-- Quote --}}
                <p class="text-gray-700 mb-6 leading-relaxed text-sm sm:text-base">
                    "{{ __('app.testimonial2Quote') }}"
                </p>

                {{-- Rating --}}
                <div class="flex text-yellow-400 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    @endfor
                </div>

                {{-- Author --}}
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-full flex items-center justify-center text-white font-bold">
                        س
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">{{ __('app.testimonial2Author') }}</div>
                        <div class="text-sm text-gray-500">{{ __('app.testimonial2Role') }} - {{ __('app.testimonial2Location') }}</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial 3 --}}
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                {{-- Quote Icon --}}
                <svg class="w-10 h-10 text-blue-100 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>

                {{-- Quote --}}
                <p class="text-gray-700 mb-6 leading-relaxed text-sm sm:text-base">
                    "{{ __('app.testimonial3Quote') }}"
                </p>

                {{-- Rating --}}
                <div class="flex text-yellow-400 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    @endfor
                </div>

                {{-- Author --}}
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold">
                        أ
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">{{ __('app.testimonial3Author') }}</div>
                        <div class="text-sm text-gray-500">{{ __('app.testimonial3Role') }} - {{ __('app.testimonial3Location') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
