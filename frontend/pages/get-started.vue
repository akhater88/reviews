<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-lg border-b border-gray-100 sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center">
          <SumaaLogo class="h-10" />
        </NuxtLink>

        <!-- Step Progress -->
        <div class="hidden sm:flex items-center gap-2">
          <div
            v-for="step in 4"
            :key="step"
            class="w-8 h-1 rounded-full transition-colors duration-300"
            :class="step <= currentStep ? 'bg-blue-500' : 'bg-gray-200'"
          ></div>
        </div>

        <!-- Close Button -->
        <NuxtLink
          to="/"
          class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 transition-colors"
        >
          <Icon name="lucide:x" class="w-5 h-5 text-gray-600" />
        </NuxtLink>
      </div>
    </header>

    <!-- Welcome Popup -->
    <WizardWelcomePopup
      :is-open="showWelcome"
      @start="startWizard"
      @skip="skipWelcome"
    />

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
      <!-- Step 1: Restaurant Search -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-8"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 -translate-x-8"
        mode="out-in"
      >
        <WizardRestaurantSearchStep
          v-if="currentStep === 1"
          @next="handlePlaceSelected"
        />

        <!-- Step 2: Contact Info -->
        <WizardContactInfoStep
          v-else-if="currentStep === 2"
          :selected-place="wizard.selectedPlace"
          @back="goToStep(1)"
          @next="handleContactSubmitted"
        />

        <!-- Step 3: Phone Verification -->
        <WizardPhoneVerificationStep
          v-else-if="currentStep === 3"
          :phone="wizard.phone"
          :name="wizard.name"
          @back="goToStep(2)"
          @verified="handleVerified"
        />

        <!-- Step 4: Processing -->
        <ProcessingStep
          v-else-if="currentStep === 4"
          :place-id="wizard.selectedPlace?.place_id || ''"
          :place-name="wizard.selectedPlace?.name || ''"
          @success="handleSuccess"
          @error="handleError"
          @retry="retryProcessing"
        />
      </Transition>
    </main>
  </div>
</template>

<script setup lang="ts">
import type { Place } from '~/composables/useFreeReportApi'

const router = useRouter()
const wizard = useWizard()
const { createFreeReport } = useFreeReportApi()

const showWelcome = ref(true)
const currentStep = ref(1)

// Check for saved state on mount
onMounted(() => {
  if (wizard.selectedPlace) {
    // Resume from saved state
    if (wizard.isVerified) {
      currentStep.value = 4
      showWelcome.value = false
    } else if (wizard.phone) {
      currentStep.value = 3
      showWelcome.value = false
    } else {
      currentStep.value = 2
      showWelcome.value = false
    }
  }
})

const startWizard = () => {
  showWelcome.value = false
}

const skipWelcome = () => {
  showWelcome.value = false
}

const goToStep = (step: number) => {
  currentStep.value = step
}

const handlePlaceSelected = (place: Place) => {
  wizard.setPlace(place)
  currentStep.value = 2
}

const handleContactSubmitted = (data: { name: string; phone: string; email?: string }) => {
  wizard.setContactInfo(data.name, data.phone, data.email)
  currentStep.value = 3
}

const handleVerified = async () => {
  wizard.setVerified(true)
  currentStep.value = 4

  // Start report creation
  try {
    await createFreeReport({
      place_id: wizard.selectedPlace!.place_id,
      place_name: wizard.selectedPlace!.name,
      phone: wizard.phone,
      name: wizard.name,
      email: wizard.email,
    })
  } catch (error) {
    console.error('Failed to create report:', error)
  }
}

const handleSuccess = (token: string) => {
  // Clear wizard state
  wizard.reset()
  // Navigate to report
  router.push(`/report/${token}`)
}

const handleError = () => {
  // Stay on processing step, error state is shown
}

const retryProcessing = () => {
  // Retry the report creation
  handleVerified()
}
</script>
