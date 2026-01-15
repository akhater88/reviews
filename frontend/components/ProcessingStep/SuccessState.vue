<template>
  <div class="text-center py-8">
    <!-- Confetti Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div
        v-for="i in 20"
        :key="i"
        class="confetti"
        :style="confettiStyle(i)"
      ></div>
    </div>

    <!-- Success Icon -->
    <div class="relative inline-flex items-center justify-center mb-6">
      <!-- Background Glow -->
      <div class="absolute w-40 h-40 bg-green-400/30 rounded-full blur-3xl animate-pulse"></div>

      <!-- Celebration Rings -->
      <div class="absolute w-32 h-32 rounded-full border-4 border-green-300/50 animate-ping-once"></div>
      <div class="absolute w-28 h-28 rounded-full border-4 border-green-400/30 animate-ping-once" style="animation-delay: 0.2s"></div>

      <!-- Icon Container -->
      <div class="relative w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center shadow-xl shadow-green-500/30 animate-scale-in">
        <Icon name="lucide:check" class="w-12 h-12 text-white" />
      </div>
    </div>

    <!-- Success Title -->
    <h3 class="text-2xl font-bold text-gray-900 mb-2 animate-fade-up">
      {{ $t('success.title') }}
    </h3>

    <!-- Success Message -->
    <p class="text-gray-600 mb-8 max-w-md mx-auto animate-fade-up" style="animation-delay: 0.1s">
      {{ $t('success.message') }}
    </p>

    <!-- Report Preview Card -->
    <div
      v-if="reportData"
      class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 mb-8 max-w-md mx-auto border border-blue-100 animate-fade-up"
      style="animation-delay: 0.2s"
    >
      <div class="flex items-center gap-4 mb-4">
        <div class="w-16 h-16 bg-white rounded-xl shadow-sm flex items-center justify-center">
          <Icon name="lucide:store" class="w-8 h-8 text-blue-600" />
        </div>
        <div class="text-right flex-1">
          <h4 class="font-bold text-gray-900">{{ reportData.restaurantName }}</h4>
          <p class="text-sm text-gray-500">{{ reportData.reviewsCount }} {{ $t('success.reviews') }}</p>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl p-3 text-center">
          <div class="text-2xl font-bold text-yellow-500">{{ reportData.rating }}</div>
          <div class="text-xs text-gray-500">{{ $t('success.rating') }}</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center">
          <div class="text-2xl font-bold text-green-500">{{ reportData.positivePercent }}%</div>
          <div class="text-xs text-gray-500">{{ $t('success.positive') }}</div>
        </div>
        <div class="bg-white rounded-xl p-3 text-center">
          <div class="text-2xl font-bold text-blue-500">{{ reportData.insights }}</div>
          <div class="text-xs text-gray-500">{{ $t('success.insights') }}</div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 animate-fade-up" style="animation-delay: 0.3s">
      <!-- View Report Button -->
      <button
        @click="$emit('viewReport')"
        class="flex items-center gap-2 px-8 py-4 bg-gradient-to-l from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 hover:shadow-xl hover:scale-105 transition-all duration-300"
      >
        <span>{{ $t('success.viewReport') }}</span>
        <Icon name="lucide:arrow-left" class="w-5 h-5" />
      </button>

      <!-- Share Button -->
      <button
        @click="shareReport"
        class="flex items-center gap-2 px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
      >
        <Icon name="lucide:share-2" class="w-5 h-5" />
        <span>{{ $t('success.share') }}</span>
      </button>
    </div>

    <!-- WhatsApp Delivery Notice -->
    <div class="mt-8 p-4 bg-green-50 rounded-xl max-w-md mx-auto animate-fade-up" style="animation-delay: 0.4s">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:message-circle" class="w-5 h-5 text-white" />
        </div>
        <div class="text-right">
          <p class="text-sm font-medium text-green-800">{{ $t('success.whatsappSent') }}</p>
          <p class="text-xs text-green-600">{{ $t('success.checkWhatsapp') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface ReportData {
  restaurantName: string
  reviewsCount: number
  rating: string
  positivePercent: number
  insights: number
}

defineProps<{
  reportData?: ReportData
}>()

defineEmits(['viewReport'])

const confettiColors = [
  'bg-yellow-400',
  'bg-green-400',
  'bg-blue-400',
  'bg-pink-400',
  'bg-purple-400',
  'bg-red-400',
]

const confettiStyle = (index: number) => {
  const left = Math.random() * 100
  const delay = Math.random() * 2
  const duration = 2 + Math.random() * 2
  const size = 8 + Math.random() * 8

  return {
    left: `${left}%`,
    width: `${size}px`,
    height: `${size}px`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`,
    backgroundColor: `var(--confetti-color-${index % confettiColors.length})`,
  }
}

const shareReport = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: 'تقرير سُمعة',
        text: 'شاهد تحليل مطعمي على سُمعة',
        url: window.location.href,
      })
    } catch (err) {
      console.log('Share cancelled')
    }
  }
}
</script>

<style scoped>
.confetti {
  position: absolute;
  top: -20px;
  border-radius: 2px;
  animation: confetti-fall linear forwards;
}

@keyframes confetti-fall {
  0% {
    transform: translateY(-100%) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(100vh) rotate(720deg);
    opacity: 0;
  }
}

.animate-scale-in {
  animation: scale-in 0.5s ease-out forwards;
}

@keyframes scale-in {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-ping-once {
  animation: ping-once 1s ease-out forwards;
}

@keyframes ping-once {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-fade-up {
  animation: fade-up 0.5s ease-out forwards;
  opacity: 0;
}

@keyframes fade-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Confetti colors as CSS variables */
:root {
  --confetti-color-0: #facc15;
  --confetti-color-1: #4ade80;
  --confetti-color-2: #60a5fa;
  --confetti-color-3: #f472b6;
  --confetti-color-4: #a78bfa;
  --confetti-color-5: #f87171;
}
</style>
