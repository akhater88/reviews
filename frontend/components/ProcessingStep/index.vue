<template>
  <div class="max-w-xl mx-auto py-8">
    <!-- Error State -->
    <ErrorState
      v-if="hasError"
      :title="errorTitle"
      :message="errorMessage"
      @retry="$emit('retry')"
      @go-back="$emit('error')"
    />

    <!-- Success State -->
    <SuccessState
      v-else-if="isComplete"
      :report-data="reportData"
      :report-token="reportToken"
      @view-report="handleViewReport"
    />

    <!-- Processing State -->
    <div v-else class="space-y-8">
      <!-- Main Animation -->
      <ProcessingAnimation :stage="currentStage" />

      <!-- Title & Subtitle -->
      <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          {{ $t('processing.title') }}
        </h2>
        <p class="text-gray-600">
          {{ stageDescriptions[currentStage] }}
        </p>
      </div>

      <!-- Stage Indicator -->
      <StageIndicator :current-stage="currentStage" />

      <!-- Progress Bar -->
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">{{ stageLabels[currentStage] }}</span>
          <span class="font-medium text-gray-900">{{ progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
          <div
            class="h-full bg-gradient-to-l from-blue-500 to-purple-600 rounded-full transition-all duration-700 relative"
            :style="{ width: `${progress}%` }"
          >
            <div class="absolute inset-0 shimmer-effect"></div>
          </div>
        </div>
      </div>

      <!-- Wait Message -->
      <div class="text-center">
        <p class="text-sm text-gray-500">
          {{ $t('processing.waitMessage') }}
        </p>
      </div>

      <!-- Tip Card -->
      <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-100 rounded-2xl p-5">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <Icon name="lucide:lightbulb" class="w-5 h-5 text-white" />
          </div>
          <div class="text-right">
            <p class="font-medium text-gray-900 mb-1">{{ $t('processing.tip') }}</p>
            <p class="text-sm text-gray-600">{{ currentTip }}</p>
          </div>
        </div>
      </div>

      <!-- WhatsApp Notice (when near completion) -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div
          v-if="progress >= 75"
          class="bg-green-50 border border-green-200 rounded-2xl p-4"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
              <Icon name="lucide:message-circle" class="w-5 h-5 text-white" />
            </div>
            <div class="text-right">
              <p class="font-medium text-green-800">{{ $t('success.whatsappSent') }}</p>
              <p class="text-sm text-green-600">{{ $t('success.checkWhatsapp') }}</p>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const { t } = useI18n()
const wizard = useWizard()
const { checkReportStatus } = useFreeReportApi()

const props = defineProps<{
  placeId: string
  placeName: string
}>()

const emit = defineEmits<{
  success: [token: string]
  error: []
  retry: []
}>()

// State
const progress = ref(0)
const currentStage = ref(0)
const hasError = ref(false)
const isComplete = ref(false)
const errorTitle = ref('')
const errorMessage = ref('')
const reportToken = ref('')
const currentTipIndex = ref(0)

let pollInterval: ReturnType<typeof setInterval> | null = null
let tipInterval: ReturnType<typeof setInterval> | null = null

// Computed
const stageLabels = computed(() => [
  t('processing.stage1'),
  t('processing.stage2'),
  t('processing.stage3'),
  t('processing.stage4'),
])

const stageDescriptions = computed(() => [
  t('processing.stage1'),
  t('processing.stage2'),
  t('processing.stage3'),
  t('processing.stage4'),
])

const tips = [
  'تقييمات العملاء هي أفضل طريقة لفهم نقاط القوة والضعف',
  'التقارير التفصيلية تساعدك على اتخاذ قرارات أفضل',
  'الاستجابة السريعة للتقييمات تزيد من رضا العملاء',
  'تحليل الأنماط يكشف عن فرص التحسين المخفية',
]

const currentTip = computed(() => tips[currentTipIndex.value])

const reportData = computed(() => ({
  restaurantName: props.placeName,
  reviewsCount: 50,
  rating: '4.5',
  positivePercent: 85,
  insights: 12,
}))

// Methods
const pollStatus = async () => {
  try {
    const response = await checkReportStatus(wizard.phone.value, props.placeId)

    if (response.success && response.data) {
      const { status, progress: statusProgress, token, error_message } = response.data

      // Update progress based on status
      switch (status) {
        case 'pending':
          progress.value = 10
          currentStage.value = 0
          break
        case 'fetching_reviews':
          progress.value = Math.max(statusProgress || 30, progress.value)
          currentStage.value = 0
          break
        case 'analyzing':
          progress.value = Math.max(statusProgress || 60, progress.value)
          currentStage.value = 1
          break
        case 'generating':
          progress.value = Math.max(statusProgress || 85, progress.value)
          currentStage.value = 2
          break
        case 'sending':
          progress.value = 95
          currentStage.value = 3
          break
        case 'completed':
          progress.value = 100
          currentStage.value = 3
          isComplete.value = true
          reportToken.value = token || ''
          stopPolling()
          break
        case 'failed':
        case 'error':
          hasError.value = true
          errorTitle.value = t('error.defaultTitle')
          errorMessage.value = error_message || t('error.defaultMessage')
          stopPolling()
          break
      }
    }
  } catch (error) {
    console.error('Polling error:', error)
  }
}

const startPolling = () => {
  if (pollInterval) return

  // Initial poll
  pollStatus()

  // Poll every 3 seconds
  pollInterval = setInterval(pollStatus, 3000)
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

const handleViewReport = () => {
  emit('success', reportToken.value)
}

// Lifecycle
onMounted(() => {
  startPolling()

  // Rotate tips every 8 seconds
  tipInterval = setInterval(() => {
    currentTipIndex.value = (currentTipIndex.value + 1) % tips.length
  }, 8000)
})

onUnmounted(() => {
  stopPolling()
  if (tipInterval) {
    clearInterval(tipInterval)
  }
})
</script>

<style scoped>
.shimmer-effect {
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
  background-size: 200% 100%;
  animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
  0% { background-position: -200% center; }
  100% { background-position: 200% center; }
}
</style>
