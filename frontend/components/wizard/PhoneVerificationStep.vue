<template>
  <div class="max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg shadow-purple-500/30 mb-4">
        <Icon name="lucide:message-circle" class="w-8 h-8 text-white" />
      </div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        {{ $t('wizard.verifyTitle') }}
      </h2>
      <p class="text-gray-600">
        {{ $t('wizard.verifySubtitle') }}
      </p>
      <!-- Phone Number Display -->
      <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-full">
        <Icon name="lucide:smartphone" class="w-4 h-4 text-gray-500" />
        <span class="font-mono font-medium text-gray-700" dir="ltr">+{{ formattedPhone }}</span>
        <button
          @click="$emit('back')"
          class="text-blue-600 hover:text-blue-700 text-sm"
        >
          {{ $t('wizard.edit') }}
        </button>
      </div>
    </div>

    <!-- OTP Input -->
    <div class="mb-6">
      <div class="flex justify-center gap-3" dir="ltr">
        <input
          v-for="(_, index) in otpDigits"
          :key="index"
          ref="otpInputs"
          v-model="otpDigits[index]"
          type="text"
          inputmode="numeric"
          maxlength="1"
          class="otp-input"
          :class="{ 'error': hasError }"
          @input="handleInput(index)"
          @keydown="handleKeydown($event, index)"
          @paste="handlePaste"
        />
      </div>
      <p v-if="errorMessage" class="mt-3 text-sm text-red-500 text-center">{{ errorMessage }}</p>
    </div>

    <!-- Resend Timer -->
    <div class="text-center mb-8">
      <template v-if="resendTimer > 0">
        <p class="text-sm text-gray-500">
          {{ $t('wizard.resendIn') }}
          <span class="font-mono font-bold text-gray-700">{{ formatTime(resendTimer) }}</span>
        </p>
      </template>
      <template v-else>
        <button
          @click="handleResend"
          :disabled="isResending"
          class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
        >
          <span v-if="!isResending">{{ $t('wizard.resendCode') }}</span>
          <span v-else class="flex items-center gap-2">
            <span class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
            {{ $t('wizard.sending') }}
          </span>
        </button>
      </template>
    </div>

    <!-- WhatsApp Delivery Info -->
    <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:message-circle" class="w-5 h-5 text-white" />
        </div>
        <div class="text-right">
          <p class="font-medium text-green-800">{{ $t('wizard.checkWhatsapp') }}</p>
          <p class="text-sm text-green-600">{{ $t('wizard.codeExpiresIn') }}</p>
        </div>
      </div>
    </div>

    <!-- Verify Button -->
    <button
      @click="handleVerify"
      :disabled="!isOtpComplete || isVerifying"
      class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-l from-purple-600 to-pink-600 text-white font-bold text-lg rounded-xl shadow-lg transition-all duration-300"
      :class="isOtpComplete && !isVerifying
        ? 'shadow-purple-500/30 hover:shadow-xl hover:scale-[1.02]'
        : 'opacity-50 cursor-not-allowed'"
    >
      <span v-if="!isVerifying">{{ $t('wizard.verifyButton') }}</span>
      <span v-else>{{ $t('wizard.verifying') }}</span>
      <div
        v-if="isVerifying"
        class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"
      ></div>
    </button>

    <!-- Help Link -->
    <div class="mt-6 text-center">
      <button
        @click="showHelp = true"
        class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
      >
        {{ $t('wizard.didntReceive') }}
      </button>
    </div>

    <!-- Help Modal -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showHelp" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/50" @click="showHelp = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4 text-center">
            {{ $t('wizard.helpTitle') }}
          </h3>
          <ul class="space-y-3 text-right mb-6">
            <li class="flex items-start gap-3">
              <Icon name="lucide:check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span class="text-gray-600">{{ $t('wizard.helpTip1') }}</span>
            </li>
            <li class="flex items-start gap-3">
              <Icon name="lucide:check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span class="text-gray-600">{{ $t('wizard.helpTip2') }}</span>
            </li>
            <li class="flex items-start gap-3">
              <Icon name="lucide:check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span class="text-gray-600">{{ $t('wizard.helpTip3') }}</span>
            </li>
          </ul>
          <button
            @click="showHelp = false"
            class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors"
          >
            {{ $t('wizard.gotIt') }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const { t } = useI18n()
const { verifyOtp, resendOtp } = useFreeReportApi()

const props = defineProps<{
  phone: string
  name: string
}>()

const emit = defineEmits<{
  back: []
  verified: []
}>()

const otpDigits = ref(['', '', '', '', '', ''])
const otpInputs = ref<HTMLInputElement[]>([])
const resendTimer = ref(60)
const isVerifying = ref(false)
const isResending = ref(false)
const errorMessage = ref('')
const hasError = ref(false)
const showHelp = ref(false)

let timerInterval: ReturnType<typeof setInterval> | null = null

const formattedPhone = computed(() => {
  const phone = props.phone
  if (phone.length === 12) {
    return `${phone.slice(0, 3)} ${phone.slice(3, 5)} ${phone.slice(5, 8)} ${phone.slice(8)}`
  }
  return phone
})

const isOtpComplete = computed(() => {
  return otpDigits.value.every(d => d !== '')
})

const otpValue = computed(() => {
  return otpDigits.value.join('')
})

const formatTime = (seconds: number) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const handleInput = (index: number) => {
  // Clear error on input
  hasError.value = false
  errorMessage.value = ''

  const value = otpDigits.value[index]

  // Only allow digits
  if (!/^\d*$/.test(value)) {
    otpDigits.value[index] = ''
    return
  }

  // Move to next input
  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }

  // Auto-verify when complete
  if (isOtpComplete.value) {
    handleVerify()
  }
}

const handleKeydown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  }
}

const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault()
  const pastedData = event.clipboardData?.getData('text') || ''
  const digits = pastedData.replace(/\D/g, '').slice(0, 6).split('')

  digits.forEach((digit, index) => {
    if (index < 6) {
      otpDigits.value[index] = digit
    }
  })

  // Focus the last filled input or the next empty one
  const focusIndex = Math.min(digits.length, 5)
  otpInputs.value[focusIndex]?.focus()

  if (digits.length === 6) {
    handleVerify()
  }
}

const handleVerify = async () => {
  if (!isOtpComplete.value || isVerifying.value) return

  isVerifying.value = true
  hasError.value = false
  errorMessage.value = ''

  try {
    await verifyOtp(props.phone, otpValue.value)
    emit('verified')
  } catch (error: any) {
    hasError.value = true
    if (error.response?.status === 422) {
      errorMessage.value = t('wizard.errors.invalidCode')
    } else if (error.response?.status === 429) {
      errorMessage.value = t('wizard.errors.tooManyAttempts')
    } else {
      errorMessage.value = t('wizard.errors.verifyFailed')
    }
    // Clear OTP on error
    otpDigits.value = ['', '', '', '', '', '']
    otpInputs.value[0]?.focus()
  } finally {
    isVerifying.value = false
  }
}

const handleResend = async () => {
  if (isResending.value || resendTimer.value > 0) return

  isResending.value = true
  try {
    await resendOtp(props.phone)
    resendTimer.value = 60
    startTimer()
  } catch (error: any) {
    if (error.response?.status === 429) {
      errorMessage.value = t('wizard.errors.tooManyResends')
    } else {
      errorMessage.value = t('wizard.errors.resendFailed')
    }
  } finally {
    isResending.value = false
  }
}

const startTimer = () => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
  timerInterval = setInterval(() => {
    if (resendTimer.value > 0) {
      resendTimer.value--
    } else {
      if (timerInterval) {
        clearInterval(timerInterval)
      }
    }
  }, 1000)
}

onMounted(() => {
  startTimer()
  // Focus first input
  otpInputs.value[0]?.focus()
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
</script>

<style scoped>
.otp-input {
  @apply w-12 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl;
  @apply focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all duration-200;
}

.otp-input.error {
  @apply border-red-400 bg-red-50;
  animation: shake 0.3s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
</style>
