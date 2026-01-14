<template>
  <div class="text-center py-8">
    <!-- Error Icon -->
    <div class="relative inline-flex items-center justify-center mb-6">
      <!-- Background Glow -->
      <div class="absolute w-32 h-32 bg-red-400/20 rounded-full blur-2xl"></div>

      <!-- Icon Container -->
      <div class="relative w-24 h-24 bg-gradient-to-br from-red-500 to-red-600 rounded-3xl flex items-center justify-center shadow-xl shadow-red-500/30 animate-shake">
        <Icon name="lucide:alert-triangle" class="w-12 h-12 text-white" />
      </div>
    </div>

    <!-- Error Title -->
    <h3 class="text-2xl font-bold text-gray-900 mb-2">
      {{ title || $t('error.defaultTitle') }}
    </h3>

    <!-- Error Message -->
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
      {{ message || $t('error.defaultMessage') }}
    </p>

    <!-- Error Details (if provided) -->
    <div
      v-if="details"
      class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6 max-w-md mx-auto"
    >
      <div class="flex items-start gap-3 text-right">
        <Icon name="lucide:info" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
        <p class="text-sm text-red-700">{{ details }}</p>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
      <!-- Retry Button -->
      <button
        @click="$emit('retry')"
        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-l from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-xl hover:scale-105 transition-all duration-300"
      >
        <Icon name="lucide:refresh-cw" class="w-5 h-5" />
        <span>{{ $t('error.retry') }}</span>
      </button>

      <!-- Go Back Button -->
      <button
        @click="$emit('goBack')"
        class="flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300"
      >
        <Icon name="lucide:arrow-right" class="w-5 h-5" />
        <span>{{ $t('error.goBack') }}</span>
      </button>
    </div>

    <!-- Support Link -->
    <div class="mt-8 pt-6 border-t border-gray-100">
      <p class="text-sm text-gray-500 mb-2">{{ $t('error.needHelp') }}</p>
      <a
        href="https://wa.me/966500000000"
        target="_blank"
        class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 font-medium transition-colors"
      >
        <Icon name="lucide:message-circle" class="w-5 h-5" />
        <span>{{ $t('error.contactSupport') }}</span>
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  message?: string
  details?: string
}>()

defineEmits(['retry', 'goBack'])
</script>

<style scoped>
.animate-shake {
  animation: shake 0.6s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}
</style>
