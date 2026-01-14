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
                    class="h-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-500"
                    :style="{ width: progressWidth }"
                ></div>
            </div>

            {{-- Wizard Content --}}
            <div class="p-6 sm:p-8">

                {{-- Step 1: Search for Business --}}
                <div x-show="step === 1" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">🔍</div>
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
                                class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-lg"
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
                                class="w-full flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all text-right"
                            >
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <template x-if="place.photo_url">
                                        <img :src="place.photo_url" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!place.photo_url">
                                        <div class="w-full h-full flex items-center justify-center text-2xl bg-gradient-to-br from-blue-100 to-purple-100">🏪</div>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-900 truncate" x-text="place.name"></h4>
                                    <p class="text-gray-500 text-sm truncate" x-text="place.address"></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm text-yellow-600">⭐ <span x-text="place.rating || '-'"></span></span>
                                        <span class="text-gray-400 text-sm">(<span x-text="place.reviews_count || '0'"></span> تقييم)</span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- No Results --}}
                    <div x-show="searchQuery.length >= 2 && !searchLoading && searchResults.length === 0 && searchPerformed" class="text-center py-8 text-gray-500">
                        <div class="text-4xl mb-2">😕</div>
                        <p>لم يتم العثور على نتائج</p>
                        <p class="text-sm mt-1">جرب البحث باسم مختلف</p>
                    </div>

                    {{-- Initial State --}}
                    <div x-show="searchQuery.length < 2 && searchResults.length === 0" class="text-center py-8 text-gray-400">
                        <p class="text-sm">اكتب اسم المحل للبحث</p>
                    </div>
                </div>

                {{-- Step 2: Phone Number --}}
                <div x-show="step === 2" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">📱</div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('app.howItWorksStep2Title') }}</h2>
                        <p class="text-gray-500 mt-2">{{ __('app.howItWorksStep2Desc') }}</p>
                    </div>

                    {{-- Selected Place Summary --}}
                    <div x-show="selectedPlace" class="bg-gray-50 rounded-xl p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                <template x-if="selectedPlace?.photo_url">
                                    <img :src="selectedPlace?.photo_url" class="w-full h-full object-cover" alt="">
                                </template>
                                <template x-if="!selectedPlace?.photo_url">
                                    <div class="w-full h-full flex items-center justify-center text-xl bg-gradient-to-br from-blue-100 to-purple-100">🏪</div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 truncate" x-text="selectedPlace?.name"></h4>
                                <p class="text-gray-500 text-sm truncate" x-text="selectedPlace?.address"></p>
                            </div>
                            <button @click="step = 1; selectedPlace = null" class="text-blue-600 text-sm hover:underline">
                                تغيير
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="sendOtp()">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">رقم الجوال</label>
                            <div class="flex border-2 border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                <span class="bg-gray-100 px-4 py-4 text-gray-600 border-l border-gray-200 flex items-center gap-2 text-lg">
                                    🇸🇦 +966
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
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || phone.length < 9"
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span>إرسال رمز التحقق</span>
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
                        بالمتابعة، أنت توافق على شروط الخدمة وسياسة الخصوصية
                    </p>
                </div>

                {{-- Step 3: OTP Verification --}}
                <div x-show="step === 3" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">🔐</div>
                        <h2 class="text-2xl font-bold text-gray-900">أدخل رمز التحقق</h2>
                        <p class="text-gray-500 mt-2">
                            تم إرسال الرمز إلى واتساب
                            <span class="font-medium text-gray-700" dir="ltr" x-text="phoneMasked"></span>
                        </p>
                    </div>

                    <form @submit.prevent="verifyOtp()">
                        <div class="mb-6">
                            <div class="flex justify-center gap-2" dir="ltr">
                                <template x-for="(digit, index) in otpDigits" :key="index">
                                    <input
                                        type="text"
                                        maxlength="1"
                                        inputmode="numeric"
                                        class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                                        :class="{ 'border-red-500': error }"
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

                        <button
                            type="submit"
                            :disabled="loading || otpCode.length < 6"
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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
                                    إعادة الإرسال بعد <span x-text="resendCountdown" class="font-bold"></span> ثانية
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

                        <button
                            type="button"
                            @click="step = 2; resetOtp()"
                            class="w-full mt-4 text-gray-500 hover:text-gray-700 text-sm"
                        >
                            ← تغيير رقم الجوال
                        </button>
                    </form>
                </div>

                {{-- Step 4: Success --}}
                <div x-show="step === 4" x-cloak>
                    <div class="text-center">
                        <div class="text-6xl mb-4 animate-bounce">🎉</div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.howItWorksStep3Title') }}</h2>
                        <p class="text-gray-500 mb-6">سيتم إرسال تقريرك الكامل عبر واتساب خلال دقائق</p>

                        {{-- Selected Place Summary --}}
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 mb-6">
                            <div class="flex items-center justify-center gap-4 mb-4">
                                <div class="w-16 h-16 bg-white rounded-xl overflow-hidden flex-shrink-0 shadow-md">
                                    <template x-if="selectedPlace?.photo_url">
                                        <img :src="selectedPlace?.photo_url" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!selectedPlace?.photo_url">
                                        <div class="w-full h-full flex items-center justify-center text-2xl bg-gradient-to-br from-blue-100 to-purple-100">🏪</div>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <h4 class="font-bold text-gray-900" x-text="selectedPlace?.name"></h4>
                                    <p class="text-gray-500 text-sm" x-text="selectedPlace?.city || selectedPlace?.address"></p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                                <span>📊 تحليل المشاعر</span>
                                <span>•</span>
                                <span>💡 توصيات ذكية</span>
                                <span>•</span>
                                <span>📈 نظرة عامة</span>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                            <p class="text-green-700 flex items-center justify-center gap-2">
                                <span>✓</span>
                                <span>سنرسل لك التقرير على واتساب قريباً</span>
                            </p>
                        </div>

                        <a
                            href="{{ route('landing') }}"
                            class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all"
                        >
                            العودة للرئيسية
                        </a>

                        <p class="text-gray-400 text-sm mt-4">
                            هل تريد المزيد من المميزات؟
                            <a href="https://www.tabsense.ai/ar/social-landing-pages/google-review-tool" target="_blank" class="text-blue-600 hover:underline">اشترك الآن</a>
                        </p>
                    </div>
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

            get progressWidth() {
                return (this.step / 4 * 100) + '%';
            },

            get otpCode() {
                return this.otpDigits.join('');
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

                // Format phone with country code
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
                        // Create masked phone display
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
                        this.step = 4;
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
        }
    }
    </script>
</body>
</html>
