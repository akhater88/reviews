<div>
    @if($showModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm overflow-y-auto"
        x-data="{
            countdown: @entangle('countdown'),
            interval: null,
            startCountdown(seconds) {
                this.countdown = seconds;
                if (this.interval) clearInterval(this.interval);
                this.interval = setInterval(() => {
                    if (this.countdown > 0) {
                        this.countdown--;
                        $wire.dispatch('update-countdown', { value: this.countdown });
                    } else {
                        clearInterval(this.interval);
                        $wire.dispatch('countdown-finished');
                    }
                }, 1000);
            }
        }"
        x-init="
            $wire.on('start-countdown', (event) => {
                startCountdown(event.seconds);
            });
        "
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm my-auto" dir="rtl">
            <!-- Header -->
            <div class="bg-primary-600 px-4 py-3 text-white rounded-t-xl">
                <h2 class="text-lg font-bold">
                    @if($step === 'phone')
                        التحقق من رقم الجوال
                    @else
                        إدخال رمز التحقق
                    @endif
                </h2>
                <p class="text-xs text-primary-100 mt-1">
                    @if($step === 'phone')
                        أدخل رقم جوالك لإرسال رمز التحقق عبر واتساب
                    @else
                        أدخل رمز التحقق المرسل إلى {{ $countryCode }} {{ $phone }}
                    @endif
                </p>
            </div>

            <!-- Body -->
            <div class="p-4">
                @if($error)
                    <div class="mb-3 p-2 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg text-danger-700 dark:text-danger-400 text-xs">
                        {{ $error }}
                    </div>
                @endif

                @if($success && $step !== 'otp')
                    <div class="mb-3 p-2 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg text-success-700 dark:text-success-400 text-xs">
                        {{ $success }}
                    </div>
                @endif

                @if($step === 'phone')
                    <!-- Phone Input Step -->
                    <form wire:submit="sendOtp" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                رقم الجوال
                            </label>
                            <div class="flex gap-2" dir="ltr">
                                <select
                                    wire:model="countryCode"
                                    class="w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @foreach($countryCodes as $code => $label)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                                <input
                                    type="tel"
                                    wire:model="phone"
                                    placeholder="5XXXXXXXX"
                                    class="flex-1 min-w-0 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    autocomplete="tel"
                                />
                            </div>
                            @error('phone')
                                <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                        >
                            <span wire:loading.remove wire:target="sendOtp">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </span>
                            <span wire:loading wire:target="sendOtp">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="sendOtp">إرسال رمز التحقق</span>
                            <span wire:loading wire:target="sendOtp">جاري الإرسال...</span>
                        </button>
                    </form>

                @else
                    <!-- OTP Input Step -->
                    <form wire:submit="verifyOtp" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">
                                رمز التحقق (6 أرقام)
                            </label>
                            <div
                                class="flex justify-center gap-2"
                                dir="ltr"
                                x-data="{
                                    digits: ['', '', '', '', '', ''],
                                    init() {
                                        this.$nextTick(() => {
                                            if (this.$refs.digit0) {
                                                this.$refs.digit0.focus();
                                            }
                                        });
                                    },
                                    updateOtp() {
                                        $wire.set('otp', this.digits.join(''));
                                    },
                                    handleInput(index, event) {
                                        const value = event.target.value.replace(/\D/g, '');
                                        if (value.length > 0) {
                                            this.digits[index] = value.charAt(0);
                                            event.target.value = this.digits[index];
                                            this.updateOtp();
                                            if (index < 5) {
                                                this.$refs['digit' + (index + 1)].focus();
                                            }
                                            if (this.digits.every(d => d !== '')) {
                                                $wire.verifyOtp();
                                            }
                                        } else {
                                            this.digits[index] = '';
                                            event.target.value = '';
                                            this.updateOtp();
                                        }
                                    },
                                    handleKeydown(index, event) {
                                        if (event.key === 'Backspace') {
                                            if (this.digits[index] === '' && index > 0) {
                                                this.$refs['digit' + (index - 1)].focus();
                                            } else {
                                                this.digits[index] = '';
                                                event.target.value = '';
                                                this.updateOtp();
                                            }
                                        } else if (event.key === 'ArrowLeft' && index > 0) {
                                            this.$refs['digit' + (index - 1)].focus();
                                        } else if (event.key === 'ArrowRight' && index < 5) {
                                            this.$refs['digit' + (index + 1)].focus();
                                        }
                                    },
                                    handlePaste(event) {
                                        event.preventDefault();
                                        const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                                        for (let i = 0; i < 6; i++) {
                                            this.digits[i] = paste[i] || '';
                                            if (this.$refs['digit' + i]) {
                                                this.$refs['digit' + i].value = this.digits[i];
                                            }
                                        }
                                        this.updateOtp();
                                        if (paste.length === 6) {
                                            $wire.verifyOtp();
                                        }
                                    }
                                }"
                            >
                                <input type="text" maxlength="1" x-ref="digit0"
                                    @input="handleInput(0, $event)"
                                    @keydown="handleKeydown(0, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                                <input type="text" maxlength="1" x-ref="digit1"
                                    @input="handleInput(1, $event)"
                                    @keydown="handleKeydown(1, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                                <input type="text" maxlength="1" x-ref="digit2"
                                    @input="handleInput(2, $event)"
                                    @keydown="handleKeydown(2, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                                <input type="text" maxlength="1" x-ref="digit3"
                                    @input="handleInput(3, $event)"
                                    @keydown="handleKeydown(3, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                                <input type="text" maxlength="1" x-ref="digit4"
                                    @input="handleInput(4, $event)"
                                    @keydown="handleKeydown(4, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                                <input type="text" maxlength="1" x-ref="digit5"
                                    @input="handleInput(5, $event)"
                                    @keydown="handleKeydown(5, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-10 h-12 text-center text-xl font-bold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500"
                                    inputmode="numeric" autocomplete="off" />
                            </div>
                            @error('otp')
                                <p class="mt-2 text-xs text-danger-600 dark:text-danger-400 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Countdown / Resend -->
                        <div class="text-center text-xs text-gray-600 dark:text-gray-400">
                            @if(!$canResend)
                                <p>
                                    إعادة الإرسال بعد:
                                    <span class="font-bold text-primary-600" x-text="countdown"></span>
                                    ثانية
                                </p>
                            @else
                                <button
                                    type="button"
                                    wire:click="resendOtp"
                                    class="text-primary-600 hover:text-primary-700 font-medium"
                                >
                                    إعادة إرسال الرمز
                                </button>
                            @endif
                        </div>

                        <!-- Attempts remaining -->
                        @if($attempts > 0)
                            <p class="text-center text-xs text-warning-600 dark:text-warning-400">
                                المحاولات المتبقية: {{ $maxAttempts - $attempts }}
                            </p>
                        @endif

                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="goBack"
                                class="flex-1 py-2.5 px-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200"
                            >
                                تغيير الرقم
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-2.5 px-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-1"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <span wire:loading wire:target="verifyOtp">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="verifyOtp">تحقق</span>
                                <span wire:loading wire:target="verifyOtp">جاري التحقق...</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- Footer info -->
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    سيتم إرسال رمز التحقق عبر واتساب
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
