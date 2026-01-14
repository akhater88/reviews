{{-- Features Section --}}
<section id="features" class="py-16 sm:py-20 lg:py-32 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                {{ __('app.featuresTagline') }}
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold mt-4 mb-6">
                {{ __('app.featuresTitle') }}
            </h2>
            <p class="text-lg sm:text-xl text-gray-600">
                {{ __('app.featuresSubtitle') }}
            </p>
        </div>

        {{-- Features Grid --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Feature 1: Google Sync --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-red-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureGoogleSyncTitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureGoogleSyncDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureGoogleSyncPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureGoogleSyncPoint2') }}
                    </li>
                </ul>
            </div>

            {{-- Feature 2: AI Analysis --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-purple-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureAITitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureAIDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureAIPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureAIPoint2') }}
                    </li>
                </ul>
            </div>

            {{-- Feature 3: Multi-Branch --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-blue-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureMultiBranchTitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureMultiBranchDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureMultiBranchPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureMultiBranchPoint2') }}
                    </li>
                </ul>
            </div>

            {{-- Feature 4: AI Reply --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-green-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureAIReplyTitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureAIReplyDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureAIReplyPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureAIReplyPoint2') }}
                    </li>
                </ul>
            </div>

            {{-- Feature 5: Reports --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-orange-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureReportsTitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureReportsDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureReportsPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureReportsPoint2') }}
                    </li>
                </ul>
            </div>

            {{-- Feature 6: Competition --}}
            <div class="group bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 bg-yellow-100">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold mb-3">
                    {{ __('app.featureCompetitionTitle') }}
                </h3>
                <p class="text-gray-600 mb-4 text-sm sm:text-base">
                    {{ __('app.featureCompetitionDesc') }}
                </p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureCompetitionPoint1') }}
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('app.featureCompetitionPoint2') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
