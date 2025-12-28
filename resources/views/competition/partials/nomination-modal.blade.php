<!-- Nomination Modal -->
<div
    x-show="showNominationModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    x-data="nominationFlow()"
    @keydown.escape.window="closeNominationModal()"
>
    <!-- Backdrop -->
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        @click="closeNominationModal()"
    ></div>

    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div
            x-show="showNominationModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
            @click.stop
        >
            <!-- Progress Bar -->
            <div class="h-1 bg-gray-200">
                <div
                    class="h-full bg-orange-500 transition-all duration-300"
                    :style="{ width: getProgressWidth() }"
                ></div>
            </div>

            <!-- Close Button -->
            <button
                @click="closeNominationModal()"
                class="absolute top-4 left-4 text-gray-400 hover:text-gray-600 z-10"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Modal Body -->
            <div class="p-6">

                <!-- Step 1: Phone Input -->
                <div x-show="currentStep === 'phone'" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">&#128241;</div>
                        <h3 class="text-xl font-bold text-gray-900">أدخل رقم جوالك</h3>
                        <p class="text-gray-500 text-sm mt-2">سنرسل لك رمز التحقق عبر واتساب</p>
                    </div>

                    <form @submit.prevent="sendOtp()">
                        <div class="mb-4">
                            <div class="flex border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500">
                                <span class="bg-gray-100 px-4 py-3 text-gray-600 border-l border-gray-300 flex items-center gap-2">
                                    <span>&#127480;&#127462;</span>
                                    <span>+966</span>
                                </span>
                                <input
                                    type="tel"
                                    x-model="phone"
                                    placeholder="5X XXX XXXX"
                                    class="flex-1 px-4 py-3 focus:outline-none text-left"
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
                            class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span>إرسال رمز التحقق</span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    جاري الإرسال...
                                </span>
                            </template>
                        </button>
                    </form>

                    <p class="text-center text-gray-400 text-xs mt-4">
                        بالمتابعة، أنت توافق على
                        <a href="{{ route('competition.terms') }}" class="underline" target="_blank">الشروط والأحكام</a>
                    </p>
                </div>

                <!-- Step 2: OTP Verification -->
                <div x-show="currentStep === 'otp'" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">&#128274;</div>
                        <h3 class="text-xl font-bold text-gray-900">أدخل رمز التحقق</h3>
                        <p class="text-gray-500 text-sm mt-2">
                            تم إرسال الرمز إلى واتساب
                            <span class="font-medium text-gray-700" dir="ltr" x-text="phoneMasked"></span>
                        </p>
                    </div>

                    <form @submit.prevent="verifyOtp()">
                        <div class="mb-4">
                            <!-- OTP Input Boxes -->
                            <div class="flex justify-center gap-2 mb-2" dir="ltr">
                                <template x-for="(digit, index) in otpDigits" :key="index">
                                    <input
                                        type="text"
                                        maxlength="1"
                                        inputmode="numeric"
                                        class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all"
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
                            <p x-show="error" x-text="error" class="text-red-500 text-sm text-center mt-2"></p>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || otpCode.length < 6"
                            class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span>تحقق</span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    جاري التحقق...
                                </span>
                            </template>
                        </button>

                        <!-- Resend OTP -->
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
                                    class="text-orange-600 hover:text-orange-700 text-sm font-medium disabled:opacity-50"
                                >
                                    <template x-if="!resendLoading">
                                        <span>لم يصلك الرمز؟ إعادة الإرسال</span>
                                    </template>
                                    <template x-if="resendLoading">
                                        <span>جاري الإرسال...</span>
                                    </template>
                                </button>
                            </template>
                        </div>

                        <!-- Back to phone -->
                        <button
                            type="button"
                            @click="goToStep('phone'); resetOtp();"
                            class="w-full mt-4 text-gray-500 hover:text-gray-700 text-sm"
                        >
                            &#8594; تغيير رقم الجوال
                        </button>
                    </form>
                </div>

                <!-- Step 3: Registration -->
                <div x-show="currentStep === 'register'" x-cloak>
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">&#128100;</div>
                        <h3 class="text-xl font-bold text-gray-900">أكمل بياناتك</h3>
                        <p class="text-gray-500 text-sm mt-2">معلومات بسيطة للتواصل معك عند الفوز</p>
                    </div>

                    <form @submit.prevent="register()">
                        <!-- Name -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">الاسم *</label>
                            <input
                                type="text"
                                x-model="registerForm.name"
                                placeholder="أدخل اسمك"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                                :class="{ 'border-red-500': errors.name }"
                                required
                            >
                            <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-sm mt-1"></p>
                        </div>

                        <!-- Email (Optional) -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                البريد الإلكتروني
                                <span class="text-gray-400 font-normal">(اختياري)</span>
                            </label>
                            <input
                                type="email"
                                x-model="registerForm.email"
                                placeholder="example@email.com"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                                dir="ltr"
                            >
                        </div>

                        <!-- City -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">
                                المدينة
                                <span class="text-gray-400 font-normal">(اختياري)</span>
                            </label>
                            <select
                                x-model="registerForm.city"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-white"
                            >
                                <option value="">اختر المدينة</option>
                                <option value="الرياض">الرياض</option>
                                <option value="جدة">جدة</option>
                                <option value="مكة المكرمة">مكة المكرمة</option>
                                <option value="المدينة المنورة">المدينة المنورة</option>
                                <option value="الدمام">الدمام</option>
                                <option value="الخبر">الخبر</option>
                                <option value="الظهران">الظهران</option>
                                <option value="الأحساء">الأحساء</option>
                                <option value="القطيف">القطيف</option>
                                <option value="الطائف">الطائف</option>
                                <option value="تبوك">تبوك</option>
                                <option value="بريدة">بريدة</option>
                                <option value="حائل">حائل</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>

                        <!-- WhatsApp Opt-in -->
                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="registerForm.whatsapp_opted_in"
                                    class="w-5 h-5 text-orange-500 rounded border-gray-300 focus:ring-orange-500"
                                    checked
                                >
                                <span class="text-gray-700 text-sm">أوافق على استقبال إشعارات واتساب</span>
                            </label>
                        </div>

                        <!-- Terms -->
                        <div class="mb-6">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="registerForm.accept_terms"
                                    class="w-5 h-5 text-orange-500 rounded border-gray-300 focus:ring-orange-500 mt-0.5"
                                    required
                                >
                                <span class="text-gray-700 text-sm">
                                    أوافق على
                                    <a href="{{ route('competition.terms') }}" class="text-orange-600 underline" target="_blank">الشروط والأحكام</a>
                                    و
                                    <a href="{{ route('competition.privacy') }}" class="text-orange-600 underline" target="_blank">سياسة الخصوصية</a>
                                </span>
                            </label>
                            <p x-show="errors.accept_terms" x-text="errors.accept_terms" class="text-red-500 text-sm mt-1"></p>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || !registerForm.name || !registerForm.accept_terms"
                            class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <template x-if="!loading">
                                <span>متابعة لترشيح المطعم</span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    جاري الحفظ...
                                </span>
                            </template>
                        </button>
                    </form>
                </div>

                <!-- Step 4: Search (Placeholder - Prompt 11) -->
                <div x-show="currentStep === 'search'" x-cloak>
                    <div class="text-center py-8">
                        <div class="text-5xl mb-4">&#128269;</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">ابحث عن مطعمك</h3>
                        <p class="text-gray-500">سيتم تنفيذ هذه الخطوة في Prompt 11</p>
                        <button
                            @click="goToStep('phone')"
                            class="mt-4 text-orange-600 hover:text-orange-700 text-sm"
                        >
                            &#8594; العودة للبداية
                        </button>
                    </div>
                </div>

                <!-- Already Nominated Step -->
                <div x-show="currentStep === 'already_nominated'" x-cloak>
                    <div class="text-center py-8">
                        <div class="text-5xl mb-4">&#9989;</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">لقد شاركت بالفعل!</h3>
                        <p class="text-gray-500 mb-4">لقد قمت بترشيح مطعم في هذه الفترة.</p>
                        <p class="text-gray-400 text-sm">يمكنك الترشيح مرة واحدة فقط كل شهر.</p>
                        <button
                            @click="closeNominationModal()"
                            class="mt-6 bg-orange-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-orange-600 transition-colors"
                        >
                            حسناً
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function nominationFlow() {
    return {
        // Current step
        currentStep: 'phone',

        // Loading states
        loading: false,
        resendLoading: false,

        // Error handling
        error: '',
        errors: {},

        // Phone step
        phone: '',
        phoneMasked: '',

        // OTP step
        otpDigits: ['', '', '', '', '', ''],
        resendCountdown: 0,
        resendTimer: null,

        // Register step
        registerForm: {
            name: '',
            email: '',
            city: '',
            whatsapp_opted_in: true,
            accept_terms: false,
        },

        // Participant data
        participant: null,

        // Initialize
        init() {
            // Check auth status on load
            this.checkAuthStatus();
        },

        // Progress calculation
        getProgressWidth() {
            const steps = ['phone', 'otp', 'register', 'search', 'confirm', 'success'];
            const index = steps.indexOf(this.currentStep);
            return ((index + 1) / steps.length * 100) + '%';
        },

        // Navigation
        goToStep(step) {
            this.currentStep = step;
            this.error = '';
            this.errors = {};
        },

        // Check if user is already authenticated
        async checkAuthStatus() {
            try {
                const response = await fetch('{{ route("competition.auth-status") }}');
                const data = await response.json();

                if (data.success && data.data.authenticated) {
                    this.participant = data.data.participant;

                    if (data.data.has_nominated) {
                        // Already nominated, show message
                        this.currentStep = 'already_nominated';
                    } else if (!data.data.participant.is_registered) {
                        // Needs registration
                        this.currentStep = 'register';
                    } else {
                        // Ready to nominate
                        this.currentStep = 'search';
                    }
                }
            } catch (e) {
                console.error('Auth check failed:', e);
            }
        },

        // Send OTP
        async sendOtp() {
            if (this.loading) return;

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('{{ route("competition.send-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ phone: this.phone }),
                });

                const data = await response.json();

                if (data.success) {
                    this.phoneMasked = data.data.phone_masked;
                    this.goToStep('otp');
                    this.startResendCountdown(60);

                    // Focus first OTP input after a short delay
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const firstInput = document.querySelector('[x-ref="otpInput0"]');
                            if (firstInput) {
                                firstInput.focus();
                            }
                        }, 100);
                    });
                } else {
                    this.error = data.message;

                    if (data.data?.retry_after) {
                        this.error += ` (${data.data.retry_after} ثانية)`;
                    }
                }
            } catch (e) {
                this.error = 'حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.';
                console.error('Send OTP error:', e);
            } finally {
                this.loading = false;
            }
        },

        // OTP computed value
        get otpCode() {
            return this.otpDigits.join('');
        },

        // Handle OTP input
        handleOtpInput(event, index) {
            const value = event.target.value.replace(/\D/g, '');
            this.otpDigits[index] = value.slice(-1);

            // Auto-focus next input
            if (value && index < 5) {
                const nextInput = document.querySelector(`[x-ref="otpInput${index + 1}"]`);
                if (nextInput) {
                    nextInput.focus();
                }
            }

            // Auto-submit when complete
            if (this.otpCode.length === 6) {
                this.verifyOtp();
            }
        },

        // Handle backspace in OTP
        handleOtpBackspace(event, index) {
            if (!this.otpDigits[index] && index > 0) {
                const prevInput = document.querySelector(`[x-ref="otpInput${index - 1}"]`);
                if (prevInput) {
                    prevInput.focus();
                }
            }
        },

        // Handle paste in OTP
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

        // Reset OTP
        resetOtp() {
            this.otpDigits = ['', '', '', '', '', ''];
            this.error = '';
        },

        // Start resend countdown
        startResendCountdown(seconds) {
            this.resendCountdown = seconds;

            if (this.resendTimer) {
                clearInterval(this.resendTimer);
            }

            this.resendTimer = setInterval(() => {
                this.resendCountdown--;
                if (this.resendCountdown <= 0) {
                    clearInterval(this.resendTimer);
                }
            }, 1000);
        },

        // Verify OTP
        async verifyOtp() {
            if (this.loading || this.otpCode.length < 6) return;

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('{{ route("competition.verify-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        phone: this.phone,
                        code: this.otpCode,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.participant = data.data.participant;

                    if (data.data.needs_registration) {
                        this.goToStep('register');
                    } else {
                        this.goToStep('search');
                    }
                } else {
                    this.error = data.message;

                    if (data.data?.expired) {
                        // OTP expired, show resend option
                        this.resendCountdown = 0;
                    }

                    if (data.data?.max_attempts) {
                        // Max attempts, go back to phone
                        setTimeout(() => {
                            this.goToStep('phone');
                            this.resetOtp();
                        }, 2000);
                    }
                }
            } catch (e) {
                this.error = 'حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.';
                console.error('Verify OTP error:', e);
            } finally {
                this.loading = false;
            }
        },

        // Resend OTP
        async resendOtp() {
            if (this.resendLoading || this.resendCountdown > 0) return;

            this.resendLoading = true;
            this.error = '';
            this.resetOtp();

            try {
                const response = await fetch('{{ route("competition.resend-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ phone: this.phone }),
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

        // Register participant
        async register() {
            if (this.loading) return;

            this.loading = true;
            this.error = '';
            this.errors = {};

            // Validate
            if (!this.registerForm.name || this.registerForm.name.length < 2) {
                this.errors.name = 'الاسم مطلوب (حرفين على الأقل)';
                this.loading = false;
                return;
            }

            if (!this.registerForm.accept_terms) {
                this.errors.accept_terms = 'يجب الموافقة على الشروط والأحكام';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('{{ route("competition.register") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.registerForm),
                });

                const data = await response.json();

                if (data.success) {
                    this.participant = data.data.participant;
                    this.goToStep('search');
                } else {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.error = data.message;
                    }
                }
            } catch (e) {
                this.error = 'حدث خطأ في الحفظ. يرجى المحاولة مرة أخرى.';
                console.error('Register error:', e);
            } finally {
                this.loading = false;
            }
        },

        // Cleanup
        destroy() {
            if (this.resendTimer) {
                clearInterval(this.resendTimer);
            }
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
