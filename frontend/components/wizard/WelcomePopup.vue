<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

      <!-- Modal -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden"
        >
          <!-- Decorative Header -->
          <div class="relative h-40 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 overflow-hidden">
            <!-- Animated Background Shapes -->
            <div class="absolute inset-0">
              <div class="absolute top-4 right-4 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
              <div class="absolute bottom-4 left-4 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
            </div>

            <!-- Icon -->
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center animate-bounce-subtle">
                <Icon name="lucide:gift" class="w-12 h-12 text-white" />
              </div>
            </div>

            <!-- Close Button -->
            <button
              @click="$emit('skip')"
              class="absolute top-4 left-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors"
            >
              <Icon name="lucide:x" class="w-4 h-4 text-white" />
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 text-center">
            <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
              {{ $t('welcome.title') }}
            </h2>

            <!-- Subtitle -->
            <p class="text-gray-600 mb-6">
              {{ $t('welcome.subtitle') }}
            </p>

            <!-- Features List -->
            <div class="space-y-3 mb-8 text-right">
              <div
                v-for="(feature, index) in features"
                :key="index"
                class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl"
              >
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                  :class="feature.bgColor"
                >
                  <Icon :name="feature.icon" class="w-5 h-5" :class="feature.iconColor" />
                </div>
                <div class="flex-1">
                  <span class="font-medium text-gray-900">{{ feature.title }}</span>
                  <p class="text-xs text-gray-500">{{ feature.description }}</p>
                </div>
              </div>
            </div>

            <!-- CTA Button -->
            <button
              @click="$emit('start')"
              class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-bold text-lg rounded-xl shadow-lg shadow-red-500/30 hover:shadow-xl hover:scale-[1.02] transition-all duration-300"
            >
              <span>{{ $t('welcome.startButton') }}</span>
              <Icon name="lucide:arrow-left" class="w-5 h-5" />
            </button>

            <!-- Time Estimate -->
            <p class="mt-4 text-sm text-gray-500 flex items-center justify-center gap-2">
              <Icon name="lucide:clock" class="w-4 h-4" />
              <span>{{ $t('welcome.timeEstimate') }}</span>
            </p>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup lang="ts">
const { t } = useI18n()

defineProps<{
  isOpen: boolean
}>()

defineEmits(['start', 'skip'])

const features = computed(() => [
  {
    icon: 'lucide:star',
    title: t('welcome.feature1Title'),
    description: t('welcome.feature1Desc'),
    bgColor: 'bg-yellow-100',
    iconColor: 'text-yellow-600',
  },
  {
    icon: 'lucide:brain',
    title: t('welcome.feature2Title'),
    description: t('welcome.feature2Desc'),
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600',
  },
  {
    icon: 'lucide:zap',
    title: t('welcome.feature3Title'),
    description: t('welcome.feature3Desc'),
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600',
  },
])
</script>

<style scoped>
.animate-bounce-subtle {
  animation: bounce-subtle 2s ease-in-out infinite;
}

@keyframes bounce-subtle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
</style>
