<template>
  <!-- White Logo: For dark backgrounds -->
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 160 80"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="sumaa-logo-white"
  >
    <defs>
      <linearGradient id="white-logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#6366F1"/>
        <stop offset="100%" stop-color="#8B5CF6"/>
      </linearGradient>
    </defs>
    <!-- Rounded rectangle background - white -->
    <rect x="4" y="4" width="152" height="72" rx="20" fill="white"/>
    <!-- Arabic text: سُمعة - gradient -->
    <text
      x="80"
      y="42"
      font-family="IBM Plex Sans Arabic, system-ui, sans-serif"
      font-size="28"
      font-weight="700"
      fill="url(#white-logo-gradient)"
      text-anchor="middle"
      direction="rtl"
    >سُمعة</text>
    <!-- English text: SUMAA -->
    <text
      x="80"
      y="62"
      font-family="Plus Jakarta Sans, system-ui, sans-serif"
      font-size="12"
      font-weight="600"
      fill="#6366F1"
      fill-opacity="0.7"
      text-anchor="middle"
      letter-spacing="4"
    >SUMAA</text>
  </svg>
</template>

<script setup lang="ts">
defineProps({
  width: {
    type: [Number, String],
    default: 120
  },
  height: {
    type: [Number, String],
    default: 60
  }
})
</script>
