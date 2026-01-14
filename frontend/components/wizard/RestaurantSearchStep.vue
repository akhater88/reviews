<template>
  <div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
        <Icon name="lucide:search" class="w-8 h-8 text-white" />
      </div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        {{ $t('wizard.searchTitle') }}
      </h2>
      <p class="text-gray-600">
        {{ $t('wizard.searchSubtitle') }}
      </p>
    </div>

    <!-- Search Input -->
    <div class="relative mb-4">
      <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
        <Icon
          v-if="!isSearching"
          name="lucide:search"
          class="w-5 h-5 text-gray-400"
        />
        <div v-else class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
      </div>
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="$t('wizard.searchPlaceholder')"
        class="w-full pr-12 pl-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-right"
        @input="handleSearch"
      />
    </div>

    <!-- Location Filter (Optional) -->
    <div class="mb-6">
      <button
        @click="toggleLocationFilter"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-blue-600 transition-colors"
      >
        <Icon name="lucide:map-pin" class="w-4 h-4" />
        <span>{{ showLocationFilter ? $t('wizard.hideLocation') : $t('wizard.addLocation') }}</span>
      </button>

      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <input
          v-if="showLocationFilter"
          v-model="locationQuery"
          type="text"
          :placeholder="$t('wizard.locationPlaceholder')"
          class="w-full mt-3 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-300 text-right"
        />
      </Transition>
    </div>

    <!-- Results -->
    <div v-if="results.length > 0" class="space-y-3 mb-6">
      <TransitionGroup
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-4"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <button
          v-for="place in results"
          :key="place.place_id"
          @click="selectPlace(place)"
          class="w-full flex items-center gap-4 p-4 bg-white border-2 rounded-2xl transition-all duration-300 text-right"
          :class="selectedPlace?.place_id === place.place_id
            ? 'border-blue-500 bg-blue-50 shadow-lg shadow-blue-100'
            : 'border-gray-100 hover:border-gray-300 hover:shadow-md'"
        >
          <!-- Place Icon -->
          <div
            class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
            :class="selectedPlace?.place_id === place.place_id ? 'bg-blue-500' : 'bg-gray-100'"
          >
            <Icon
              name="lucide:store"
              class="w-7 h-7"
              :class="selectedPlace?.place_id === place.place_id ? 'text-white' : 'text-gray-500'"
            />
          </div>

          <!-- Place Info -->
          <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-gray-900 truncate">{{ place.name }}</h4>
            <p class="text-sm text-gray-500 truncate">{{ place.formatted_address }}</p>
            <div v-if="place.rating" class="flex items-center gap-1 mt-1">
              <Icon name="lucide:star" class="w-4 h-4 text-yellow-500" />
              <span class="text-sm font-medium text-gray-700">{{ place.rating }}</span>
              <span class="text-xs text-gray-400">({{ place.user_ratings_total }} {{ $t('wizard.reviews') }})</span>
            </div>
          </div>

          <!-- Selection Indicator -->
          <div
            v-if="selectedPlace?.place_id === place.place_id"
            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center"
          >
            <Icon name="lucide:check" class="w-5 h-5 text-white" />
          </div>
        </button>
      </TransitionGroup>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="searchQuery && !isSearching && hasSearched"
      class="text-center py-12 bg-gray-50 rounded-2xl"
    >
      <Icon name="lucide:search-x" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
      <p class="text-gray-500">{{ $t('wizard.noResults') }}</p>
      <p class="text-sm text-gray-400 mt-1">{{ $t('wizard.tryDifferentSearch') }}</p>
    </div>

    <!-- Initial State -->
    <div
      v-else-if="!searchQuery"
      class="text-center py-12 bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl border-2 border-dashed border-blue-200"
    >
      <Icon name="lucide:utensils" class="w-12 h-12 text-blue-400 mx-auto mb-3" />
      <p class="text-gray-600 font-medium">{{ $t('wizard.searchHint') }}</p>
      <p class="text-sm text-gray-400 mt-1">{{ $t('wizard.searchExample') }}</p>
    </div>

    <!-- Continue Button -->
    <div class="mt-8">
      <button
        @click="$emit('next', selectedPlace)"
        :disabled="!selectedPlace"
        class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-l from-blue-600 to-blue-700 text-white font-bold text-lg rounded-xl shadow-lg transition-all duration-300"
        :class="selectedPlace
          ? 'shadow-blue-500/30 hover:shadow-xl hover:scale-[1.02]'
          : 'opacity-50 cursor-not-allowed'"
      >
        <span>{{ $t('wizard.continue') }}</span>
        <Icon name="lucide:arrow-left" class="w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Place } from '~/composables/useFreeReportApi'

const { t } = useI18n()
const { searchPlaces } = useFreeReportApi()

const emit = defineEmits<{
  next: [place: Place]
}>()

const searchQuery = ref('')
const locationQuery = ref('')
const showLocationFilter = ref(false)
const results = ref<Place[]>([])
const selectedPlace = ref<Place | null>(null)
const isSearching = ref(false)
const hasSearched = ref(false)

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const toggleLocationFilter = () => {
  showLocationFilter.value = !showLocationFilter.value
}

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  if (searchQuery.value.length < 2) {
    results.value = []
    hasSearched.value = false
    return
  }

  searchTimeout = setTimeout(async () => {
    isSearching.value = true
    try {
      const response = await searchPlaces(
        searchQuery.value,
        showLocationFilter.value ? locationQuery.value : undefined
      )
      results.value = response.places || []
      hasSearched.value = true
    } catch (error) {
      console.error('Search error:', error)
      results.value = []
    } finally {
      isSearching.value = false
    }
  }, 500)
}

const selectPlace = (place: Place) => {
  selectedPlace.value = place
}

// Re-search when location changes
watch(locationQuery, () => {
  if (searchQuery.value.length >= 2) {
    handleSearch()
  }
})
</script>
