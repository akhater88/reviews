<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="احصل على تقرير مجاني لتقييمات مطعمك على Google">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ابدأ مجاناً - TABsense</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Animated gradient background */
        .animated-gradient {
            background: linear-gradient(-45deg, #667eea, #764ba2, #6B8DD6, #8E7AB5);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Processing Animations */
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes ping-slow {
            75%, 100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes orbit {
            from { transform: rotate(0deg) translateX(50px) rotate(0deg); }
            to { transform: rotate(360deg) translateX(50px) rotate(-360deg); }
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        @keyframes confetti-fall {
            0% { transform: translateY(-100%) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }

        @keyframes scale-in {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .animate-spin-slow {
            animation: spin-slow 8s linear infinite;
        }

        .animate-ping-slow {
            animation: ping-slow 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        .animate-bounce-subtle {
            animation: bounce-subtle 1s ease-in-out infinite;
        }

        .animate-orbit {
            animation: orbit 4s linear infinite;
        }

        .shimmer-effect {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 2s ease-in-out infinite;
        }

        .animate-pulse-ring {
            animation: pulse-ring 2s ease-out infinite;
        }

        .animate-scale-in {
            animation: scale-in 0.5s ease-out forwards;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            top: -20px;
            border-radius: 2px;
            animation: confetti-fall 3s linear forwards;
        }
    </style>
</head>
<body class="min-h-screen animated-gradient text-gray-900 antialiased rtl" x-data="getStartedWizard()">

    {{-- Back to Home Link --}}
    <a
        href="{{ route('landing') }}"
        class="fixed top-4 right-4 sm:top-6 sm:right-6 text-white/80 hover:text-white flex items-center gap-2 z-50 transition-colors"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="hidden sm:inline">{{ __('app.getStartedBackToHome') }}</span>
    </a>

    {{-- Main Container --}}
    <div class="min-h-screen flex items-center justify-center p-4 py-16">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden">
            {{-- Progress Bar --}}
            <div class="h-1.5 bg-gray-200">
                <div
                    class="h-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-500 relative overflow-hidden"
                    :style="{ width: progressWidth }"
                >
                    <div class="absolute inset-0 shimmer-effect"></div>
                </div>
            </div>

            {{-- Wizard Content --}}
            <div class="p-6 sm:p-8">

                {{-- Step 1: Search for Business --}}
                <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('app.howItWorksStep1Title') }}</h2>
                        <p class="text-gray-500 mt-2">{{ __('app.howItWorksStep1Desc') }}</p>
                    </div>

                    <div class="mb-4">
                        <div class="relative">
                            <input
                                type="text"
                                x-model="searchQuery"
                                @input.debounce.500ms="searchPlaces()"
                                placeholder="مثال: مطعم البيك، كافيه..."
                                class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none text-lg transition-all"
                            >
                            <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                <template x-if="!searchLoading">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </template>
                                <template x-if="searchLoading">
                                    <svg class="animate-spin w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </template>
                            </div>
                        </div>
                        <p x-show="searchError" x-text="searchError" class="text-red-500 text-sm mt-2"></p>
                    </div>

                    {{-- Search Results --}}
                    <div x-show="searchResults.length > 0" class="space-y-3 max-h-72 overflow-y-auto mb-4">
                        <template x-for="place in searchResults" :key="place.place_id">
                            <button
                                @click="selectPlace(place)"
                                class="w-full flex items-start gap-3 p-4 border-2 border-gray-100 rounded-2xl hover:border-blue-500 hover:bg-blue-50 transition-all text-right group"
                            >
                                <div class="w-14 h-14 bg-gray-100 group-hover:bg-blue-100 rounded-xl overflow-hidden flex-shrink-0 transition-colors">
                                    <template x-if="place.photo_url">
                                        <img :src="place.photo_url" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!place.photo_url">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-7 h-7 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-900 truncate" x-text="place.name"></h4>
                                    <p class="text-gray-500 text-sm truncate" x-text="place.address"></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm text-yellow-600 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            <span x-text="place.rating || '-'"></span>
                                        </span>
                                        <span class="text-gray-400 text-sm">(<span x-text="place.reviews_count || '0'"></span> تقييم)</span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- No Results --}}
                    <div x-show="searchQuery.length >= 2 && !searchLoading && searchResults.length === 0 && searchPerformed" class="text-center py-8 bg-gray-50 rounded-2xl">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500">لم يتم العثور على نتائج</p>
                        <p class="text-sm text-gray-400 mt-1">جرب البحث باسم مختلف</p>
                    </div>

                    {{-- Initial State --}}
                    <div x-show="searchQuery.length < 2 && searchResults.length === 0" class="text-center py-8 bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl border-2 border-dashed border-blue-200">
                        <svg class="w-12 h-12 text-blue-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <p class="text-gray-600 font-medium">اكتب اسم مطعمك للبحث</p>
                        <p class="text-sm text-gray-400 mt-1">مثال: مطعم البيك - الرياض</p>
                    </div>
                </div>

                {{-- Step 2: Phone Number --}}
                <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('app.howItWorksStep2Title') }}</h2>
                        <p class="text-gray-500 mt-2">{{ __('app.howItWorksStep2Desc') }}</p>
                    </div>

                    {{-- Selected Place Summary --}}
                    <div x-show="selectedPlace" class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 truncate" x-text="selectedPlace?.name"></h4>
                                <p class="text-gray-500 text-sm truncate" x-text="selectedPlace?.address"></p>
                            </div>
                            <button @click="step = 1; selectedPlace = null" class="text-blue-600 text-sm hover:underline font-medium">
                                تغيير
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="sendOtp()">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">رقم الجوال</label>
                            <div class="flex border-2 border-gray-200 rounded-2xl overflow-hidden focus-within:ring-4 focus-within:ring-blue-100 focus-within:border-blue-500 transition-all">
                                <span class="bg-gray-50 px-4 py-4 text-gray-600 border-l border-gray-200 flex items-center gap-2 text-lg font-medium">
                                    +966
                                </span>
                                <input
                                    type="tel"
                                    x-model="phone"
                                    placeholder="5X XXX XXXX"
                                    class="flex-1 px-4 py-4 focus:outline-none text-left text-lg"
                                    dir="ltr"
                                    maxlength="10"
                                    inputmode="numeric"
                                    @input="phone = phone.replace(/\D/g, '').slice(0, 10)"
                                    required
                                    :disabled="loading"
                                >
                            </div>
                            <p x-show="error" x-text="error" class="text-red-500 text-sm mt-2"></p>
                            <p class="text-xs text-gray-400 mt-2">سنرسل لك رمز التحقق عبر واتساب</p>
                        </div>

                        {{-- WhatsApp Opt-in --}}
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" checked class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">أوافق على استلام التقرير عبر واتساب</span>
                                    <p class="text-xs text-gray-500">سنرسل لك التقرير والتحديثات</p>
                                </div>
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || phone.length < 9"
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-green-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span class="flex items-center gap-2">
                                    <span>إرسال رمز التحقق</span>
                                    <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    جاري الإرسال...
                                </span>
                            </template>
                        </button>
                    </form>

                    <p class="text-center text-gray-400 text-xs mt-4">
                        بياناتك محمية ولن نشاركها مع أي طرف ثالث
                    </p>
                </div>

                {{-- Step 3: OTP Verification --}}
                <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg shadow-purple-500/30 mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">أدخل رمز التحقق</h2>
                        <p class="text-gray-500 mt-2">
                            تم إرسال الرمز إلى واتساب
                        </p>
                        <div class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-full">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-mono font-medium text-gray-700" dir="ltr" x-text="phoneMasked"></span>
                            <button @click="step = 2; resetOtp()" class="text-blue-600 text-sm">تعديل</button>
                        </div>
                    </div>

                    <form @submit.prevent="verifyOtp()">
                        <div class="mb-6">
                            <div class="flex justify-center gap-2" dir="ltr">
                                <template x-for="(digit, index) in otpDigits" :key="index">
                                    <input
                                        type="text"
                                        maxlength="1"
                                        inputmode="numeric"
                                        class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all"
                                        :class="{ 'border-red-400 bg-red-50': error }"
                                        x-model="otpDigits[index]"
                                        @input="handleOtpInput($event, index)"
                                        @keydown.backspace="handleOtpBackspace($event, index)"
                                        @paste="handleOtpPaste($event)"
                                        :disabled="loading"
                                        :x-ref="'otpInput' + index"
                                    >
                                </template>
                            </div>
                            <p x-show="error" x-text="error" class="text-red-500 text-sm text-center mt-3"></p>
                        </div>

                        {{-- WhatsApp Delivery Info --}}
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-green-800">تحقق من رسائل واتساب</p>
                                    <p class="text-sm text-green-600">الرمز صالح لمدة 5 دقائق</p>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || otpCode.length < 6"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-purple-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span>تحقق واحصل على التقرير</span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    جاري التحقق...
                                </span>
                            </template>
                        </button>

                        {{-- Resend OTP --}}
                        <div class="text-center mt-4">
                            <template x-if="resendCountdown > 0">
                                <p class="text-gray-500 text-sm">
                                    إعادة الإرسال بعد <span x-text="formatTime(resendCountdown)" class="font-mono font-bold"></span>
                                </p>
                            </template>
                            <template x-if="resendCountdown <= 0">
                                <button
                                    type="button"
                                    @click="resendOtp()"
                                    :disabled="resendLoading"
                                    class="text-blue-600 hover:text-blue-700 text-sm font-medium disabled:opacity-50"
                                >
                                    لم يصلك الرمز؟ إعادة الإرسال
                                </button>
                            </template>
                        </div>
                    </form>
                </div>

                {{-- Step 4: Processing --}}
                <div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="text-center">
                        {{-- Processing Animation --}}
                        <div class="relative flex justify-center items-center py-8 mb-6">
                            {{-- Background Glow --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="w-40 h-40 rounded-full blur-3xl transition-colors duration-1000"
                                    :class="processingStageColors[processingStage].glow"
                                ></div>
                            </div>

                            {{-- Orbiting Particles --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <template x-for="(particle, index) in 6" :key="index">
                                    <div
                                        class="absolute w-2 h-2 rounded-full animate-orbit"
                                        :class="['bg-blue-400', 'bg-purple-400', 'bg-green-400', 'bg-yellow-400', 'bg-pink-400', 'bg-cyan-400'][index]"
                                        :style="{ animationDelay: (index * 0.5) + 's', transform: 'rotate(' + (index * 60) + 'deg) translateX(50px)' }"
                                    ></div>
                                </template>
                            </div>

                            {{-- Main Icon Container --}}
                            <div class="relative">
                                {{-- Rotating Ring --}}
                                <div class="absolute -inset-4">
                                    <svg class="w-full h-full animate-spin-slow" viewBox="0 0 100 100">
                                        <circle
                                            cx="50" cy="50" r="45"
                                            fill="none"
                                            stroke="url(#gradient)"
                                            stroke-width="2"
                                            stroke-dasharray="70 30"
                                            stroke-linecap="round"
                                        />
                                        <defs>
                                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stop-color="#3b82f6" />
                                                <stop offset="100%" stop-color="#8b5cf6" />
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>

                                {{-- Pulse Ring --}}
                                <div class="absolute -inset-2 rounded-full border-2 border-blue-300/30 animate-ping-slow"></div>

                                {{-- Icon Background --}}
                                <div
                                    class="relative w-20 h-20 rounded-2xl flex items-center justify-center transition-all duration-500"
                                    :class="processingStageColors[processingStage].bg"
                                >
                                    {{-- Stage Icons --}}
                                    <template x-if="processingStage === 0">
                                        <svg class="w-10 h-10 text-white animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                        </svg>
                                    </template>
                                    <template x-if="processingStage === 1">
                                        <svg class="w-10 h-10 text-white animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="processingStage === 2">
                                        <svg class="w-10 h-10 text-white animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="processingStage === 3">
                                        <svg class="w-10 h-10 text-white animate-bounce-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </template>
                                </div>
                            </div>

                            {{-- Stage Label --}}
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2">
                                <div
                                    class="px-4 py-1 rounded-full text-xs font-medium text-white shadow-lg transition-colors duration-500"
                                    :class="processingStageColors[processingStage].bg"
                                    x-text="processingStageLabels[processingStage]"
                                ></div>
                            </div>
                        </div>

                        {{-- Title --}}
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">جاري تحليل تقييماتك</h2>
                        <p class="text-gray-600 mb-6" x-text="processingStageDescriptions[processingStage]"></p>

                        {{-- Stage Indicator Dots --}}
                        <div class="flex items-center justify-center gap-3 mb-6">
                            <template x-for="(stage, index) in 4" :key="index">
                                <div class="flex items-center">
                                    <div
                                        class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all duration-500"
                                        :class="index < processingStage ? 'bg-green-500 shadow-lg shadow-green-500/30' : index === processingStage ? processingStageColors[index].bg + ' shadow-lg' : 'bg-gray-100 border-2 border-gray-200'"
                                    >
                                        <template x-if="index < processingStage">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </template>
                                        <template x-if="index === processingStage">
                                            <span class="text-sm font-bold text-white" x-text="index + 1"></span>
                                        </template>
                                        <template x-if="index > processingStage">
                                            <span class="text-sm font-medium text-gray-400" x-text="index + 1"></span>
                                        </template>
                                        {{-- Pulse Ring for Current --}}
                                        <div
                                            x-show="index === processingStage"
                                            class="absolute inset-0 rounded-full border-2 animate-pulse-ring"
                                            :class="processingStageColors[index].border"
                                        ></div>
                                    </div>
                                    {{-- Connector Line --}}
                                    <div
                                        x-show="index < 3"
                                        class="w-8 h-1 mx-1 rounded-full transition-all duration-500"
                                        :class="index < processingStage ? 'bg-green-400' : 'bg-gray-200'"
                                    ></div>
                                </div>
                            </template>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600" x-text="processingStageLabels[processingStage]"></span>
                                <span class="font-medium text-gray-900" x-text="processingProgress + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-700 relative"
                                    :style="{ width: processingProgress + '%' }"
                                >
                                    <div class="absolute inset-0 shimmer-effect"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Wait Message --}}
                        <p class="text-sm text-gray-500 mb-6">لا تغلق الصفحة، قد يستغرق التحليل بضع دقائق</p>

                        {{-- Tip Card --}}
                        <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-100 rounded-2xl p-5 text-right">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 mb-1">نصيحة</p>
                                    <p class="text-sm text-gray-600" x-text="currentTip"></p>
                                </div>
                            </div>
                        </div>

                        {{-- WhatsApp Notice (when near completion) --}}
                        <div
                            x-show="processingProgress >= 75"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            class="mt-4 bg-green-50 border border-green-200 rounded-2xl p-4"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-800">سيتم إرسال التقرير عبر واتساب</p>
                                    <p class="text-sm text-green-600">تحقق من رسائلك قريباً</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 5: Success --}}
                <div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="text-center relative overflow-hidden">
                        {{-- Confetti --}}
                        <div class="absolute inset-0 overflow-hidden pointer-events-none" x-show="step === 5">
                            <template x-for="i in 20" :key="i">
                                <div
                                    class="confetti"
                                    :style="{
                                        left: Math.random() * 100 + '%',
                                        width: (8 + Math.random() * 8) + 'px',
                                        height: (8 + Math.random() * 8) + 'px',
                                        animationDelay: (Math.random() * 2) + 's',
                                        animationDuration: (2 + Math.random() * 2) + 's',
                                        backgroundColor: ['#facc15', '#4ade80', '#60a5fa', '#f472b6', '#a78bfa', '#f87171'][i % 6]
                                    }"
                                ></div>
                            </template>
                        </div>

                        {{-- Success Icon --}}
                        <div class="relative inline-flex items-center justify-center mb-6">
                            <div class="absolute w-40 h-40 bg-green-400/30 rounded-full blur-3xl animate-pulse"></div>
                            <div class="absolute w-32 h-32 rounded-full border-4 border-green-300/50" style="animation: pulse-ring 1s ease-out forwards;"></div>
                            <div class="relative w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center shadow-xl shadow-green-500/30 animate-scale-in">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.howItWorksStep3Title') }}</h2>
                        <p class="text-gray-600 mb-8">سيتم إرسال تقريرك الكامل عبر واتساب خلال دقائق</p>

                        {{-- Selected Place Summary --}}
                        <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 mb-6 border border-blue-100">
                            <div class="flex items-center justify-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-white rounded-xl overflow-hidden flex-shrink-0 shadow-md flex items-center justify-center">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <h4 class="font-bold text-gray-900" x-text="selectedPlace?.name"></h4>
                                    <p class="text-gray-500 text-sm" x-text="selectedPlace?.city || selectedPlace?.address"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-white rounded-xl p-3 text-center">
                                    <div class="text-lg font-bold text-yellow-500">⭐</div>
                                    <div class="text-xs text-gray-500">تحليل المشاعر</div>
                                </div>
                                <div class="bg-white rounded-xl p-3 text-center">
                                    <div class="text-lg font-bold text-purple-500">🧠</div>
                                    <div class="text-xs text-gray-500">توصيات ذكية</div>
                                </div>
                                <div class="bg-white rounded-xl p-3 text-center">
                                    <div class="text-lg font-bold text-blue-500">📊</div>
                                    <div class="text-xs text-gray-500">نظرة عامة</div>
                                </div>
                            </div>
                        </div>

                        {{-- Report Link Section --}}
                        <div x-show="magicLinkToken" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <button
                                    @click="copyReportLink()"
                                    class="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm"
                                >
                                    <svg x-show="!linkCopied" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <svg x-show="linkCopied" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span x-text="linkCopied ? 'تم النسخ' : 'نسخ الرابط'" :class="linkCopied ? 'text-green-600' : 'text-gray-600'"></span>
                                </button>
                                <span class="text-sm text-gray-500">رابط التقرير</span>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-sm text-gray-700 break-all font-mono text-left" dir="ltr" x-text="reportUrl"></p>
                            </div>
                        </div>

                        {{-- View Report Button --}}
                        <a
                            :href="reportUrl"
                            x-show="magicLinkToken"
                            class="block w-full text-center bg-gradient-to-r from-green-500 to-emerald-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-green-500/30 transition-all mb-4"
                        >
                            <span class="flex items-center justify-center gap-2">
                                <span>عرض التقرير</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </span>
                        </a>

                        {{-- WhatsApp Delivery Notice --}}
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-800">تم الإرسال إلى واتساب</p>
                                    <p class="text-sm text-green-600">تحقق من رسائلك</p>
                                </div>
                            </div>
                        </div>

                        <a
                            href="{{ route('landing') }}"
                            class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-4 rounded-xl font-medium text-lg transition-all text-center"
                        >
                            العودة للرئيسية
                        </a>

                        <p class="text-gray-400 text-sm mt-4">
                            هل تريد المزيد من المميزات؟
                            <a href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool" target="_blank" class="text-blue-600 hover:underline">اشترك الآن</a>
                        </p>
                    </div>
                </div>

                {{-- Step 6: Existing Report Found --}}
                <div x-show="step === 6" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-amber-500/30 mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">لديك تقرير سابق</h2>
                        <p class="text-gray-500 mt-2">تم إنشاء تقرير مجاني لهذا الرقم مسبقاً</p>
                    </div>

                    {{-- Report Info Card --}}
                    <div class="bg-white rounded-2xl border-2 border-amber-200 shadow-lg p-6 mb-6" x-show="existingReportData">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="flex-1 text-right">
                                <h3 class="font-bold text-lg text-gray-900" x-text="existingReportData?.business_name"></h3>
                                <p class="text-sm text-gray-500 mt-1" x-text="existingReportData?.business_address"></p>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 text-sm">تاريخ الإنشاء</span>
                                <span class="font-medium text-gray-900" x-text="existingReportData?.created_at_formatted"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Message --}}
                    <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-amber-800 text-right">
                                التقرير المجاني متاح مرة واحدة فقط لكل رقم جوال. يمكنك الاطلاع على تقريرك السابق.
                            </p>
                        </div>
                    </div>

                    {{-- Report Link Section --}}
                    <div x-show="magicLinkToken" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <button
                                @click="copyReportLink()"
                                class="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm"
                            >
                                <svg x-show="!linkCopied" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <svg x-show="linkCopied" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="linkCopied ? 'تم النسخ' : 'نسخ الرابط'" :class="linkCopied ? 'text-green-600' : 'text-gray-600'"></span>
                            </button>
                            <span class="text-sm text-gray-500">رابط التقرير</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-sm text-gray-700 break-all font-mono text-left" dir="ltr" x-text="reportUrl"></p>
                        </div>
                    </div>

                    {{-- View Report Button --}}
                    <a
                        :href="reportUrl"
                        x-show="magicLinkToken"
                        class="block w-full text-center bg-gradient-to-r from-amber-500 to-orange-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-amber-500/30 transition-all mb-4"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span>عرض التقرير السابق</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </span>
                    </a>

                    {{-- Back Button --}}
                    <button
                        @click="step = 1; selectedPlace = null; existingReportData = null; magicLinkToken = ''; phone = ''; resetOtp();"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span>العودة للبداية</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script>
    function getStartedWizard() {
        return {
            step: 1,

            // Search
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            searchError: '',
            searchPerformed: false,
            selectedPlace: null,

            // Phone
            phone: '',
            phoneMasked: '',
            loading: false,
            error: '',

            // OTP
            otpDigits: ['', '', '', '', '', ''],
            resendCountdown: 0,
            resendTimer: null,
            resendLoading: false,

            // Processing
            processingStage: 0,
            processingProgress: 0,
            processingTimer: null,
            pollTimer: null,
            currentTipIndex: 0,
            tipTimer: null,

            // Report data
            magicLinkToken: '',
            existingReportData: null,
            linkCopied: false,

            tips: [
                'تقييمات العملاء هي أفضل طريقة لفهم نقاط القوة والضعف',
                'التقارير التفصيلية تساعدك على اتخاذ قرارات أفضل',
                'الاستجابة السريعة للتقييمات تزيد من رضا العملاء',
                'تحليل الأنماط يكشف عن فرص التحسين المخفية',
            ],

            processingStageLabels: ['جمع البيانات', 'التحليل', 'إنشاء التقرير', 'الإرسال'],
            processingStageDescriptions: [
                'جاري جمع التقييمات من جوجل...',
                'جاري تحليل المشاعر والأنماط...',
                'جاري إنشاء التقرير...',
                'جاري إرسال التقرير...'
            ],
            processingStageColors: {
                0: { bg: 'bg-blue-500', glow: 'bg-blue-400/30', border: 'border-blue-500' },
                1: { bg: 'bg-purple-500', glow: 'bg-purple-400/30', border: 'border-purple-500' },
                2: { bg: 'bg-green-500', glow: 'bg-green-400/30', border: 'border-green-500' },
                3: { bg: 'bg-orange-500', glow: 'bg-orange-400/30', border: 'border-orange-500' },
            },

            get progressWidth() {
                const totalSteps = this.step === 6 ? 6 : 5;
                return (Math.min(this.step, 5) / 5 * 100) + '%';
            },

            get reportUrl() {
                if (!this.magicLinkToken) return '';
                return window.location.origin + '/free-report/' + this.magicLinkToken;
            },

            get otpCode() {
                return this.otpDigits.join('');
            },

            get currentTip() {
                return this.tips[this.currentTipIndex];
            },

            formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            },

            // Search for places
            async searchPlaces() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    this.searchPerformed = false;
                    return;
                }

                this.searchLoading = true;
                this.searchError = '';

                try {
                    const response = await fetch(`{{ route("free-report.places.search") }}?query=${encodeURIComponent(this.searchQuery)}`, {
                        headers: { 'Accept': 'application/json' },
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.searchResults = data.results || [];
                    } else {
                        this.searchError = data.message || 'فشل في البحث';
                        this.searchResults = [];
                    }

                    this.searchPerformed = true;
                } catch (e) {
                    this.searchError = 'حدث خطأ في البحث. يرجى المحاولة مرة أخرى.';
                    console.error('Search error:', e);
                } finally {
                    this.searchLoading = false;
                }
            },

            selectPlace(place) {
                this.selectedPlace = place;
                this.step = 2;
                this.error = '';
            },

            async sendOtp() {
                if (this.loading) return;

                this.loading = true;
                this.error = '';

                const fullPhone = '+966' + this.phone.replace(/^0+/, '');

                try {
                    const response = await fetch('{{ route("free-report.request-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: fullPhone }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.phoneMasked = '+966 ' + this.phone.slice(0, 2) + '***' + this.phone.slice(-2);
                        this.step = 3;
                        this.startResendCountdown(60);
                    } else {
                        this.error = data.message;
                    }
                } catch (e) {
                    this.error = 'حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.';
                } finally {
                    this.loading = false;
                }
            },

            handleOtpInput(event, index) {
                const value = event.target.value.replace(/\D/g, '');
                this.otpDigits[index] = value.slice(-1);

                if (value && index < 5) {
                    const nextInput = document.querySelector(`[x-ref="otpInput${index + 1}"]`);
                    if (nextInput) nextInput.focus();
                }

                if (this.otpCode.length === 6) {
                    this.verifyOtp();
                }
            },

            handleOtpBackspace(event, index) {
                if (!this.otpDigits[index] && index > 0) {
                    const prevInput = document.querySelector(`[x-ref="otpInput${index - 1}"]`);
                    if (prevInput) prevInput.focus();
                }
            },

            handleOtpPaste(event) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, 6);

                for (let i = 0; i < 6; i++) {
                    this.otpDigits[i] = digits[i] || '';
                }

                if (digits.length === 6) {
                    this.verifyOtp();
                }
            },

            resetOtp() {
                this.otpDigits = ['', '', '', '', '', ''];
                this.error = '';
            },

            startResendCountdown(seconds) {
                this.resendCountdown = seconds;
                if (this.resendTimer) clearInterval(this.resendTimer);

                this.resendTimer = setInterval(() => {
                    this.resendCountdown--;
                    if (this.resendCountdown <= 0) {
                        clearInterval(this.resendTimer);
                    }
                }, 1000);
            },

            async verifyOtp() {
                if (this.loading || this.otpCode.length < 6) return;

                this.loading = true;
                this.error = '';

                const fullPhone = '+966' + this.phone.replace(/^0+/, '');

                try {
                    // First verify the OTP
                    const verifyResponse = await fetch('{{ route("free-report.verify-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: fullPhone,
                            otp: this.otpCode,
                        }),
                    });

                    const verifyData = await verifyResponse.json();

                    if (!verifyData.success) {
                        this.error = verifyData.message;
                        return;
                    }

                    // OTP verified - now create the report
                    const createResponse = await fetch('{{ route("free-report.create") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: fullPhone,
                            place_id: this.selectedPlace.place_id,
                            business_name: this.selectedPlace.name,
                            business_address: this.selectedPlace.address,
                        }),
                    });

                    const createData = await createResponse.json();

                    if (createData.success) {
                        // Move to processing step
                        this.step = 4;
                        this.startProcessing(fullPhone);
                    } else if (createData.error_code === 'DUPLICATE_REPORT' && createData.data) {
                        // Existing report found - show the link
                        this.existingReportData = createData.data;
                        this.magicLinkToken = createData.data.magic_link_token;
                        this.step = 6; // Existing report step
                    } else {
                        this.error = createData.message || 'فشل في إنشاء التقرير';
                    }
                } catch (e) {
                    this.error = 'حدث خطأ في التحقق. يرجى المحاولة مرة أخرى.';
                    console.error('Verify error:', e);
                } finally {
                    this.loading = false;
                }
            },

            startProcessing(fullPhone) {
                // Start tip rotation
                this.tipTimer = setInterval(() => {
                    this.currentTipIndex = (this.currentTipIndex + 1) % this.tips.length;
                }, 8000);

                // Start polling for status
                this.pollStatus(fullPhone);
                this.pollTimer = setInterval(() => this.pollStatus(fullPhone), 3000);
            },

            async pollStatus(fullPhone) {
                try {
                    const response = await fetch(`{{ route("free-report.status") }}?phone=${encodeURIComponent(fullPhone)}&place_id=${encodeURIComponent(this.selectedPlace.place_id)}`, {
                        headers: { 'Accept': 'application/json' },
                    });

                    const data = await response.json();

                    if (data.success && data.data) {
                        const { status, progress } = data.data;

                        // Update progress and stage based on status
                        switch (status) {
                            case 'pending':
                                this.processingProgress = 10;
                                this.processingStage = 0;
                                break;
                            case 'fetching_reviews':
                                this.processingProgress = Math.max(progress || 30, this.processingProgress);
                                this.processingStage = 0;
                                break;
                            case 'analyzing':
                                this.processingProgress = Math.max(progress || 60, this.processingProgress);
                                this.processingStage = 1;
                                break;
                            case 'generating':
                                this.processingProgress = Math.max(progress || 85, this.processingProgress);
                                this.processingStage = 2;
                                break;
                            case 'sending':
                                this.processingProgress = 95;
                                this.processingStage = 3;
                                break;
                            case 'completed':
                                this.processingProgress = 100;
                                this.processingStage = 3;
                                // Store the magic link token
                                if (data.data.token) {
                                    this.magicLinkToken = data.data.token;
                                }
                                this.stopProcessing();
                                setTimeout(() => {
                                    this.step = 5;
                                }, 500);
                                break;
                            case 'failed':
                                this.stopProcessing();
                                this.error = data.data.error_message || 'فشل في إنشاء التقرير';
                                break;
                        }
                    }
                } catch (e) {
                    console.error('Poll error:', e);
                }
            },

            stopProcessing() {
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
                if (this.tipTimer) {
                    clearInterval(this.tipTimer);
                    this.tipTimer = null;
                }
            },

            async resendOtp() {
                if (this.resendLoading || this.resendCountdown > 0) return;

                this.resendLoading = true;
                this.error = '';
                this.resetOtp();

                const fullPhone = '+966' + this.phone.replace(/^0+/, '');

                try {
                    const response = await fetch('{{ route("free-report.request-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: fullPhone }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.startResendCountdown(60);
                    } else {
                        this.error = data.message;
                    }
                } catch (e) {
                    this.error = 'فشل في إعادة الإرسال. يرجى المحاولة مرة أخرى.';
                } finally {
                    this.resendLoading = false;
                }
            },

            async copyReportLink() {
                if (!this.reportUrl) return;
                try {
                    await navigator.clipboard.writeText(this.reportUrl);
                    this.linkCopied = true;
                    setTimeout(() => {
                        this.linkCopied = false;
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy:', err);
                }
            },
        }
    }
    </script>
</body>
</html>
