<template>
  <div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg shadow-green-500/30 mb-4">
        <Icon name="lucide:user" class="w-8 h-8 text-white" />
      </div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        {{ $t('wizard.contactTitle') }}
      </h2>
      <p class="text-gray-600">
        {{ $t('wizard.contactSubtitle') }}
      </p>
    </div>

    <!-- Selected Restaurant Summary -->
    <div
      v-if="selectedPlace"
      class="flex items-center gap-4 p-4 bg-blue-50 border border-blue-100 rounded-2xl mb-6"
    >
      <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
        <Icon name="lucide:store" class="w-6 h-6 text-white" />
      </div>
      <div class="flex-1 text-right">
        <h4 class="font-semibold text-gray-900">{{ selectedPlace.name }}</h4>
        <p class="text-sm text-gray-500 truncate">{{ selectedPlace.formatted_address }}</p>
      </div>
      <button
        @click="$emit('back')"
        class="text-blue-600 hover:text-blue-700 text-sm font-medium"
      >
        {{ $t('wizard.change') }}
      </button>
    </div>

    <!-- Contact Form -->
    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Name Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 text-right">
          {{ $t('wizard.nameLabel') }}
        </label>
        <div class="relative">
          <input
            v-model="form.name"
            type="text"
            :placeholder="$t('wizard.namePlaceholder')"
            class="w-full px-4 py-4 border-2 rounded-xl transition-all duration-300 text-right"
            :class="errors.name
              ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
              : 'border-gray-200 focus:border-blue-500 focus:ring-blue-100'"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <Icon name="lucide:user" class="w-5 h-5 text-gray-400" />
          </div>
        </div>
        <p v-if="errors.name" class="mt-1 text-sm text-red-500 text-right">{{ errors.name }}</p>
      </div>

      <!-- Phone Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 text-right">
          {{ $t('wizard.phoneLabel') }}
        </label>
        <div class="relative">
          <input
            v-model="form.phone"
            type="tel"
            dir="ltr"
            :placeholder="$t('wizard.phonePlaceholder')"
            class="w-full px-4 py-4 border-2 rounded-xl transition-all duration-300 text-left phone-input-ltr"
            :class="errors.phone
              ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
              : 'border-gray-200 focus:border-blue-500 focus:ring-blue-100'"
            @input="formatPhone"
          />
          <!-- Country Code -->
          <div class="absolute inset-y-0 right-0 flex items-center pr-4">
            <span class="text-gray-500 font-medium">+966</span>
          </div>
        </div>
        <p v-if="errors.phone" class="mt-1 text-sm text-red-500 text-right">{{ errors.phone }}</p>
        <p class="mt-1 text-xs text-gray-400 text-right">{{ $t('wizard.phoneHint') }}</p>
      </div>

      <!-- Email Input (Optional) -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2 text-right">
          <span>{{ $t('wizard.emailLabel') }}</span>
          <span class="text-gray-400 font-normal mr-1">({{ $t('wizard.optional') }})</span>
        </label>
        <div class="relative">
          <input
            v-model="form.email"
            type="email"
            dir="ltr"
            :placeholder="$t('wizard.emailPlaceholder')"
            class="w-full px-4 py-4 border-2 rounded-xl transition-all duration-300 text-left"
            :class="errors.email
              ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
              : 'border-gray-200 focus:border-blue-500 focus:ring-blue-100'"
          />
          <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
            <Icon name="lucide:mail" class="w-5 h-5 text-gray-400" />
          </div>
        </div>
        <p v-if="errors.email" class="mt-1 text-sm text-red-500 text-right">{{ errors.email }}</p>
      </div>

      <!-- WhatsApp Preference -->
      <div class="p-4 bg-green-50 border border-green-100 rounded-xl">
        <label class="flex items-center gap-3 cursor-pointer">
          <input
            v-model="form.whatsappOptIn"
            type="checkbox"
            class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
          />
          <div class="flex-1 text-right">
            <span class="font-medium text-gray-900">{{ $t('wizard.whatsappOptIn') }}</span>
            <p class="text-xs text-gray-500">{{ $t('wizard.whatsappOptInHint') }}</p>
          </div>
          <Icon name="lucide:message-circle" class="w-6 h-6 text-green-500" />
        </label>
      </div>

      <!-- Privacy Notice -->
      <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl text-right">
        <Icon name="lucide:shield-check" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
        <p class="text-sm text-gray-600">
          {{ $t('wizard.privacyNotice') }}
        </p>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-l from-green-500 to-emerald-600 text-white font-bold text-lg rounded-xl shadow-lg shadow-green-500/30 transition-all duration-300"
        :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-xl hover:scale-[1.02]'"
      >
        <span v-if="!isSubmitting">{{ $t('wizard.sendCode') }}</span>
        <span v-else>{{ $t('wizard.sending') }}</span>
        <Icon
          v-if="!isSubmitting"
          name="lucide:arrow-left"
          class="w-5 h-5"
        />
        <div
          v-else
          class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"
        ></div>
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import type { Place } from '~/composables/useFreeReportApi'

const { t } = useI18n()
const { requestOtp } = useFreeReportApi()

const props = defineProps<{
  selectedPlace: Place | null
}>()

const emit = defineEmits<{
  back: []
  next: [data: { name: string; phone: string; email?: string }]
}>()

const form = reactive({
  name: '',
  phone: '',
  email: '',
  whatsappOptIn: true,
})

const errors = reactive({
  name: '',
  phone: '',
  email: '',
})

const isSubmitting = ref(false)

const formatPhone = () => {
  // Remove non-digits
  let phone = form.phone.replace(/\D/g, '')

  // Remove leading zeros or 966
  if (phone.startsWith('966')) {
    phone = phone.substring(3)
  }
  if (phone.startsWith('0')) {
    phone = phone.substring(1)
  }

  // Limit to 9 digits
  form.phone = phone.substring(0, 9)
}

const validateForm = () => {
  let isValid = true
  errors.name = ''
  errors.phone = ''
  errors.email = ''

  // Name validation
  if (!form.name.trim()) {
    errors.name = t('wizard.errors.nameRequired')
    isValid = false
  } else if (form.name.trim().length < 2) {
    errors.name = t('wizard.errors.nameTooShort')
    isValid = false
  }

  // Phone validation
  if (!form.phone) {
    errors.phone = t('wizard.errors.phoneRequired')
    isValid = false
  } else if (form.phone.length !== 9 || !form.phone.startsWith('5')) {
    errors.phone = t('wizard.errors.phoneInvalid')
    isValid = false
  }

  // Email validation (optional but must be valid if provided)
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = t('wizard.errors.emailInvalid')
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  isSubmitting.value = true
  try {
    const fullPhone = `966${form.phone}`
    await requestOtp(fullPhone, form.name)

    emit('next', {
      name: form.name,
      phone: fullPhone,
      email: form.email || undefined,
    })
  } catch (error: any) {
    if (error.response?.status === 429) {
      errors.phone = t('wizard.errors.tooManyAttempts')
    } else {
      errors.phone = t('wizard.errors.sendFailed')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.phone-input-ltr {
  direction: ltr;
  text-align: left;
  padding-right: 4.5rem;
}
</style>
