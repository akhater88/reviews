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
                <!-- Phone Step -->
                <div x-show="currentStep === 'phone'" x-data="phoneStep()">
                    <div class="text-center mb-6">
                        <div class="text-5xl mb-4">&#128241;</div>
                        <h3 class="text-xl font-bold text-gray-900">أدخل رقم جوالك</h3>
                        <p class="text-gray-500 text-sm mt-2">سنرسل لك رمز التحقق عبر واتساب</p>
                    </div>

                    <form @submit.prevent="sendOtp()">
                        <div class="mb-4">
                            <div class="flex border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500">
                                <span class="bg-gray-100 px-4 py-3 text-gray-600 border-l border-gray-300">
                                    &#127480;&#127462; +966
                                </span>
                                <input
                                    type="tel"
                                    x-model="phone"
                                    placeholder="5X XXX XXXX"
                                    class="flex-1 px-4 py-3 focus:outline-none text-left"
                                    dir="ltr"
                                    maxlength="10"
                                    pattern="[0-9]*"
                                    inputmode="numeric"
                                    required
                                >
                            </div>
                            <p x-show="error" x-text="error" class="text-red-500 text-sm mt-2"></p>
                        </div>

                        <button
                            type="submit"
                            :disabled="loading || phone.length < 9"
                            class="w-full bg-orange-500 text-white py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!loading">إرسال رمز التحقق</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                جاري الإرسال...
                            </span>
                        </button>
                    </form>

                    <p class="text-center text-gray-400 text-xs mt-4">
                        بالمتابعة، أنت توافق على
                        <a href="{{ route('competition.terms') }}" class="underline" target="_blank">الشروط والأحكام</a>
                    </p>
                </div>

                <!-- OTP Step (Placeholder - will be implemented in Prompt 10) -->
                <div x-show="currentStep === 'otp'" class="text-center py-8">
                    <div class="text-5xl mb-4">&#9989;</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">تم إرسال الرمز</h3>
                    <p class="text-gray-500">سيتم تنفيذ هذه الخطوة في Prompt 10</p>
                </div>

                <!-- Other steps will be implemented in subsequent prompts -->
            </div>
        </div>
    </div>
</div>

<script>
    function phoneStep() {
        return {
            phone: '',
            loading: false,
            error: '',

            async sendOtp() {
                this.loading = true;
                this.error = '';

                // Format phone number
                let formattedPhone = this.phone.replace(/\D/g, '');
                if (formattedPhone.startsWith('0')) {
                    formattedPhone = formattedPhone.substring(1);
                }

                if (formattedPhone.length < 9) {
                    this.error = 'يرجى إدخال رقم جوال صحيح';
                    this.loading = false;
                    return;
                }

                try {
                    // API call will be implemented in Prompt 10
                    // For now, just move to next step
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    this.$root.currentStep = 'otp';
                } catch (err) {
                    this.error = 'حدث خطأ، يرجى المحاولة مرة أخرى';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
