<template>
  <div class="flex items-center justify-center gap-3">
    <div
      v-for="(stage, index) in stages"
      :key="index"
      class="flex items-center"
    >
      <!-- Stage Dot -->
      <div
        class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all duration-500"
        :class="getStageClasses(index)"
      >
        <!-- Completed Check -->
        <Icon
          v-if="index < currentStage"
          name="lucide:check"
          class="w-5 h-5 text-white"
        />
        <!-- Current Stage Number -->
        <span
          v-else-if="index === currentStage"
          class="text-sm font-bold text-white"
        >
          {{ index + 1 }}
        </span>
        <!-- Future Stage Number -->
        <span v-else class="text-sm font-medium text-gray-400">
          {{ index + 1 }}
        </span>

        <!-- Pulse Ring for Current -->
        <div
          v-if="index === currentStage"
          class="absolute inset-0 rounded-full border-2 border-current animate-ping-slow opacity-50"
          :class="stage.color"
        ></div>
      </div>

      <!-- Connector Line -->
      <div
        v-if="index < stages.length - 1"
        class="w-8 h-1 mx-1 rounded-full transition-all duration-500"
        :class="index < currentStage ? 'bg-green-400' : 'bg-gray-200'"
      ></div>
    </div>
  </div>

  <!-- Stage Labels -->
  <div class="flex items-center justify-center gap-3 mt-4">
    <div
      v-for="(stage, index) in stages"
      :key="`label-${index}`"
      class="flex items-center"
    >
      <div class="w-10 text-center">
        <span
          class="text-xs font-medium transition-colors duration-300"
          :class="index <= currentStage ? 'text-gray-700' : 'text-gray-400'"
        >
          {{ stage.label }}
        </span>
      </div>
      <div v-if="index < stages.length - 1" class="w-8 mx-1"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
const { t } = useI18n()

const props = defineProps<{
  currentStage: number
}>()

const stages = computed(() => [
  { label: t('processing.stage1Short'), color: 'text-blue-500' },
  { label: t('processing.stage2Short'), color: 'text-purple-500' },
  { label: t('processing.stage3Short'), color: 'text-green-500' },
  { label: t('processing.stage4Short'), color: 'text-orange-500' },
])

const getStageClasses = (index: number) => {
  if (index < props.currentStage) {
    return 'bg-green-500 shadow-lg shadow-green-500/30'
  } else if (index === props.currentStage) {
    const colors: Record<number, string> = {
      0: 'bg-blue-500 shadow-lg shadow-blue-500/30',
      1: 'bg-purple-500 shadow-lg shadow-purple-500/30',
      2: 'bg-green-500 shadow-lg shadow-green-500/30',
      3: 'bg-orange-500 shadow-lg shadow-orange-500/30',
    }
    return colors[index] || colors[0]
  }
  return 'bg-gray-100 border-2 border-gray-200'
}
</script>

<style scoped>
.animate-ping-slow {
  animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(1.3);
    opacity: 0;
  }
}
</style>
