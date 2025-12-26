<div>
    @if($showModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm"
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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden" dir="rtl">
            <!-- Header -->
            <div class="bg-primary-600 px-6 py-4 text-white">
                <h2 class="text-xl font-bold">
                    @if($step === 'phone')
                        التحقق من رقم الجوال
                    @else
                        إدخال رمز التحقق
                    @endif
                </h2>
                <p class="text-sm text-primary-100 mt-1">
                    @if($step === 'phone')
                        أدخل رقم جوالك لإرسال رمز التحقق عبر واتساب
                    @else
                        أدخل رمز التحقق المرسل إلى {{ $countryCode }} {{ $phone }}
                    @endif
                </p>
            </div>

            <!-- Body -->
            <div class="p-6">
                @if($error)
                    <div class="mb-4 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg text-danger-700 dark:text-danger-400 text-sm">
                        {{ $error }}
                    </div>
                @endif

                @if($success && $step !== 'otp')
                    <div class="mb-4 p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg text-success-700 dark:text-success-400 text-sm">
                        {{ $success }}
                    </div>
                @endif

                @if($step === 'phone')
                    <!-- Phone Input Step -->
                    <form wire:submit="sendOtp" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                رقم الجوال
                            </label>
                            <div class="flex gap-2" dir="ltr">
                                <select
                                    wire:model="countryCode"
                                    class="w-32 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    @foreach($countryCodes as $code => $label)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                                <input
                                    type="tel"
                                    wire:model="phone"
                                    placeholder="5XXXXXXXX"
                                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    autocomplete="tel"
                                />
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                        >
                            <span wire:loading.remove wire:target="sendOtp">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </span>
                            <span wire:loading wire:target="sendOtp">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                    <form wire:submit="verifyOtp" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                رمز التحقق (6 أرقام)
                            </label>
                            <div class="flex justify-center gap-2" dir="ltr" x-data="{
                                otp: @entangle('otp'),
                                inputs: [],
                                focusNext(index) {
                                    if (index < 5) {
                                        this.$refs['otp' + (index + 1)].focus();
                                    }
                                },
                                focusPrev(index) {
                                    if (index > 0) {
                                        this.$refs['otp' + (index - 1)].focus();
                                    }
                                },
                                handleInput(index, value) {
                                    let chars = this.otp.split('');
                                    chars[index] = value;
                                    this.otp = chars.join('');
                                    if (value && index < 5) {
                                        this.focusNext(index);
                                    }
                                    // Auto submit when all 6 digits are entered
                                    if (this.otp.length === 6 && !this.otp.includes('')) {
                                        $wire.verifyOtp();
                                    }
                                },
                                handleKeydown(index, event) {
                                    if (event.key === 'Backspace' && !this.otp[index]) {
                                        this.focusPrev(index);
                                    }
                                },
                                handlePaste(event) {
                                    event.preventDefault();
                                    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                                    this.otp = paste.padEnd(6, '').slice(0, 6);
                                    if (paste.length === 6) {
                                        $wire.verifyOtp();
                                    }
                                }
                            }">
                                @for($i = 0; $i < 6; $i++)
                                    <input
                                        type="text"
                                        maxlength="1"
                                        x-ref="otp{{ $i }}"
                                        :value="otp[{{ $i }}] || ''"
                                        @input="handleInput({{ $i }}, $event.target.value.replace(/\D/g, ''))"
                                        @keydown="handleKeydown({{ $i }}, $event)"
                                        @paste="handlePaste($event)"
                                        class="w-12 h-14 text-center text-2xl font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        inputmode="numeric"
                                        pattern="[0-9]"
                                    />
                                @endfor
                            </div>
                            @error('otp')
                                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Countdown / Resend -->
                        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
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
                            <p class="text-center text-sm text-warning-600 dark:text-warning-400">
                                المحاولات المتبقية: {{ $maxAttempts - $attempts }}
                            </p>
                        @endif

                        <div class="flex gap-3">
                            <button
                                type="button"
                                wire:click="goBack"
                                class="flex-1 py-3 px-4 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors duration-200"
                            >
                                تغيير الرقم
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <span wire:loading wire:target="verifyOtp">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    سيتم إرسال رمز التحقق عبر واتساب. تأكد من أن رقم الجوال مسجل في واتساب.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
