import { ref, computed, watch } from 'vue'

export interface Place {
  place_id: string
  name: string
  formatted_address: string
  rating?: number
  user_ratings_total?: number
}

interface WizardState {
  selectedPlace: Place | null
  name: string
  phone: string
  email?: string
  isVerified: boolean
  reportToken: string | null
}

const state = ref<WizardState>({
  selectedPlace: null,
  name: '',
  phone: '',
  email: undefined,
  isVerified: false,
  reportToken: null,
})

export function useWizard() {
  // Computed getters for convenience
  const selectedPlace = computed(() => state.value.selectedPlace)
  const name = computed(() => state.value.name)
  const phone = computed(() => state.value.phone)
  const email = computed(() => state.value.email)
  const isVerified = computed(() => state.value.isVerified)
  const reportToken = computed(() => state.value.reportToken)

  // Methods
  const setPlace = (place: Place) => {
    state.value.selectedPlace = place
    saveState()
  }

  const setContactInfo = (name: string, phone: string, email?: string) => {
    state.value.name = name
    state.value.phone = phone
    state.value.email = email
    saveState()
  }

  const setVerified = (verified: boolean) => {
    state.value.isVerified = verified
    saveState()
  }

  const setReportToken = (token: string) => {
    state.value.reportToken = token
    saveState()
  }

  const reset = () => {
    state.value = {
      selectedPlace: null,
      name: '',
      phone: '',
      email: undefined,
      isVerified: false,
      reportToken: null,
    }
    if (typeof window !== 'undefined') {
      localStorage.removeItem('wizard_state')
    }
  }

  const saveState = () => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('wizard_state', JSON.stringify(state.value))
    }
  }

  // Restore from localStorage on init
  const restore = () => {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('wizard_state')
      if (saved) {
        try {
          const parsed = JSON.parse(saved)
          Object.assign(state.value, parsed)
        } catch (e) {
          console.warn('Failed to restore wizard state')
        }
      }
    }
  }

  // Auto-restore on first use
  if (typeof window !== 'undefined' && !state.value.selectedPlace) {
    restore()
  }

  return {
    // State values (as computed for reactivity)
    selectedPlace,
    name,
    phone,
    email,
    isVerified,
    reportToken,

    // Methods
    setPlace,
    setContactInfo,
    setVerified,
    setReportToken,
    reset,
    restore,
  }
}
