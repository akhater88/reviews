<template>
  <div class="max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg shadow-amber-500/30 mb-4">
        <Icon name="lucide:file-check" class="w-8 h-8 text-white" />
      </div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        {{ $t('existingReport.title') }}
      </h2>
      <p class="text-gray-600">
        {{ $t('existingReport.subtitle') }}
      </p>
    </div>

    <!-- Report Info Card -->
    <div class="bg-white rounded-2xl border-2 border-amber-200 shadow-lg p-6 mb-6">
      <div class="flex items-start gap-4 mb-4">
        <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:store" class="w-6 h-6 text-amber-600" />
        </div>
        <div class="flex-1 text-right">
          <h3 class="font-bold text-lg text-gray-900">{{ businessName }}</h3>
          <p v-if="businessAddress" class="text-sm text-gray-500 mt-1">{{ businessAddress }}</p>
        </div>
      </div>

      <div class="border-t border-gray-100 pt-4">
        <div class="flex items-center justify-between">
          <span class="text-gray-500 text-sm">{{ $t('existingReport.generatedAt') }}</span>
          <span class="font-medium text-gray-900" dir="ltr">{{ formattedDate }}</span>
        </div>
      </div>
    </div>

    <!-- Info Message -->
    <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl mb-6">
      <div class="flex items-start gap-3">
        <Icon name="lucide:info" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
        <p class="text-sm text-amber-800 text-right">
          {{ $t('existingReport.infoMessage') }}
        </p>
      </div>
    </div>

    <!-- Report Link Section -->
    <div v-if="magicLinkToken" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
      <div class="flex items-center justify-between gap-3 mb-3">
        <button
          @click="copyLink"
          class="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm"
        >
          <Icon :name="copied ? 'lucide:check' : 'lucide:copy'" class="w-4 h-4" :class="copied ? 'text-green-600' : 'text-gray-600'" />
          <span :class="copied ? 'text-green-600' : 'text-gray-600'">{{ copied ? $t('existingReport.linkCopied') : $t('existingReport.copyLink') }}</span>
        </button>
        <span class="text-sm text-gray-500">{{ $t('existingReport.reportLink') }}</span>
      </div>
      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-sm text-gray-700 break-all font-mono text-left" dir="ltr">{{ reportUrl }}</p>
      </div>
    </div>

    <!-- View Report Button -->
    <button
      @click="handleViewReport"
      class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-l from-amber-500 to-orange-600 text-white font-bold text-lg rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 mb-4"
    >
      <span>{{ $t('existingReport.viewReport') }}</span>
      <Icon name="lucide:external-link" class="w-5 h-5" />
    </button>

    <!-- Back Button -->
    <button
      @click="$emit('back')"
      class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors"
    >
      <Icon name="lucide:arrow-right" class="w-5 h-5" />
      <span>{{ $t('existingReport.backButton') }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

const router = useRouter()
const runtimeConfig = useRuntimeConfig()

const props = defineProps<{
  businessName: string
  businessAddress?: string
  createdAt: string
  createdAtFormatted: string
  magicLinkToken: string
}>()

const emit = defineEmits<{
  back: []
}>()

const copied = ref(false)

const formattedDate = computed(() => {
  try {
    const date = new Date(props.createdAt)
    return new Intl.DateTimeFormat('ar-SA', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(date)
  } catch {
    return props.createdAtFormatted
  }
})

const reportUrl = computed(() => {
  const baseUrl = typeof window !== 'undefined' ? window.location.origin : ''
  return `${baseUrl}/report/${props.magicLinkToken}`
})

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(reportUrl.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const handleViewReport = () => {
  router.push(`/report/${props.magicLinkToken}`)
}
</script>
