<template>
  <!-- Concept A: Arabic Calligraphy with Star -->
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 180 60"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="sumaa-logo-arabic"
  >
    <defs>
      <linearGradient id="sumaa-text-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="0%" stop-color="#6366F1"/>
        <stop offset="100%" stop-color="#8B5CF6"/>
      </linearGradient>
    </defs>
    <!-- Arabic text: سُمعة -->
    <text
      x="90"
      y="45"
      font-family="IBM Plex Sans Arabic, system-ui, sans-serif"
      font-size="42"
      font-weight="700"
      fill="url(#sumaa-text-gradient)"
      text-anchor="middle"
      direction="rtl"
    >سُمعة</text>
    <!-- Star accent (top right) -->
    <path
      d="M152 8 L154.5 14 L161 14.5 L156 18.5 L157.5 25 L152 21.5 L146.5 25 L148 18.5 L143 14.5 L149.5 14 Z"
      fill="#F59E0B"
    />
    <!-- Small dot accent (top left of س) -->
    <circle cx="48" cy="12" r="3" fill="#6366F1"/>
  </svg>
</template>

<script setup lang="ts">
defineProps({
  width: {
    type: [Number, String],
    default: 180
  },
  height: {
    type: [Number, String],
    default: 60
  }
})
</script>
