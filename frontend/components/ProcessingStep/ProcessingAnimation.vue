<template>
  <div class="relative flex justify-center items-center py-8">
    <!-- Background Glow -->
    <div class="absolute inset-0 flex items-center justify-center">
      <div
        class="w-40 h-40 rounded-full blur-3xl transition-colors duration-1000"
        :class="glowColor"
      ></div>
    </div>

    <!-- Orbiting Particles -->
    <div class="absolute inset-0 flex items-center justify-center">
      <div
        v-for="(particle, index) in particles"
        :key="index"
        class="absolute w-2 h-2 rounded-full"
        :class="particle.color"
        :style="particleStyle(index)"
      ></div>
    </div>

    <!-- Main Icon Container -->
    <div class="relative">
      <!-- Rotating Ring -->
      <div class="absolute -inset-4">
        <svg class="w-full h-full animate-spin-slow" viewBox="0 0 100 100">
          <circle
            cx="50" cy="50" r="45"
            fill="none"
            stroke="url(#gradient)"
            stroke-width="2"
            stroke-dasharray="70 30"
            stroke-linecap="round"
          />
          <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#3b82f6" />
              <stop offset="100%" stop-color="#8b5cf6" />
            </linearGradient>
          </defs>
        </svg>
      </div>

      <!-- Pulse Ring -->
      <div class="absolute -inset-2 rounded-full border-2 border-blue-300/30 animate-ping-slow"></div>

      <!-- Icon Background -->
      <div
        class="relative w-20 h-20 rounded-2xl flex items-center justify-center transition-all duration-500"
        :class="iconBgColor"
      >
        <!-- Stage Icon -->
        <Icon
          :name="stageIcon"
          class="w-10 h-10 text-white transition-transform duration-300"
          :class="{ 'animate-bounce-subtle': isAnimating }"
        />
      </div>
    </div>

    <!-- Stage Label -->
    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2">
      <div
        class="px-4 py-1 rounded-full text-xs font-medium text-white shadow-lg transition-colors duration-500"
        :class="labelBgColor"
      >
        {{ stageLabel }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'

const { t } = useI18n()

const props = defineProps<{
  stage: number
}>()

const isAnimating = ref(true)

const stageConfigs = computed(() => [
  { icon: 'lucide:download-cloud', bg: 'bg-blue-500', glow: 'bg-blue-400/30', label: t('processing.animLabel1') },
  { icon: 'lucide:brain', bg: 'bg-purple-500', glow: 'bg-purple-400/30', label: t('processing.animLabel2') },
  { icon: 'lucide:file-text', bg: 'bg-green-500', glow: 'bg-green-400/30', label: t('processing.animLabel3') },
  { icon: 'lucide:send', bg: 'bg-orange-500', glow: 'bg-orange-400/30', label: t('processing.animLabel4') },
])

const currentConfig = computed(() => stageConfigs.value[props.stage] || stageConfigs.value[0])
const stageIcon = computed(() => currentConfig.value.icon)
const iconBgColor = computed(() => currentConfig.value.bg)
const glowColor = computed(() => currentConfig.value.glow)
const labelBgColor = computed(() => currentConfig.value.bg)
const stageLabel = computed(() => currentConfig.value.label)

const particles = [
  { color: 'bg-blue-400' },
  { color: 'bg-purple-400' },
  { color: 'bg-green-400' },
  { color: 'bg-yellow-400' },
  { color: 'bg-pink-400' },
  { color: 'bg-cyan-400' },
]

const particleStyle = (index: number) => {
  const angle = (index / particles.length) * 360
  const delay = index * 0.5
  return {
    animation: `orbit 4s linear infinite`,
    animationDelay: `${delay}s`,
    transformOrigin: '50px 50px',
    transform: `rotate(${angle}deg) translateX(50px)`,
  }
}

// Toggle animation periodically
let animationInterval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  animationInterval = setInterval(() => {
    isAnimating.value = !isAnimating.value
    setTimeout(() => {
      isAnimating.value = true
    }, 300)
  }, 3000)
})

onUnmounted(() => {
  if (animationInterval) {
    clearInterval(animationInterval)
  }
})
</script>

<style scoped>
@keyframes orbit {
  from {
    transform: rotate(0deg) translateX(50px) rotate(0deg);
  }
  to {
    transform: rotate(360deg) translateX(50px) rotate(-360deg);
  }
}

.animate-spin-slow {
  animation: spin 8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-ping-slow {
  animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-bounce-subtle {
  animation: bounce-subtle 1s ease-in-out infinite;
}

@keyframes bounce-subtle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
</style>
