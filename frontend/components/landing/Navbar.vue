<template>
  <header
    class="fixed top-0 left-0 right-0 z-40 transition-all duration-300"
    :class="isScrolled ? 'bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100' : 'bg-transparent'"
  >
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 lg:h-20">

        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-2">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
            <Icon name="lucide:bar-chart-3" class="w-6 h-6 text-white" />
          </div>
          <span class="text-xl font-bold text-gray-900">TABsense</span>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center gap-1">
          <a
            v-for="item in navItems"
            :key="item.id"
            :href="item.href"
            class="px-4 py-2 text-gray-600 hover:text-blue-600 font-medium rounded-lg hover:bg-blue-50/50 transition-all duration-200"
          >
            {{ item.label }}
          </a>
        </nav>

        <!-- Desktop CTAs -->
        <div class="hidden lg:flex items-center gap-3">
          <NuxtLink
            to="/login"
            class="px-4 py-2 text-gray-600 hover:text-blue-600 font-medium transition-colors"
          >
            {{ $t('nav.login') }}
          </NuxtLink>
          <NuxtLink
            to="/get-started"
            class="px-6 py-2.5 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-semibold rounded-xl shadow-md shadow-coral-500/20 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
          >
            {{ $t('nav.startFree') }}
          </NuxtLink>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="$emit('open-menu')"
          class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 transition-colors"
        >
          <Icon name="lucide:menu" class="w-5 h-5 text-gray-700" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const { t } = useI18n()

defineEmits(['open-menu'])

const isScrolled = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  handleScroll()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

const navItems = computed(() => [
  { id: 'features', label: t('nav.features'), href: '#features' },
  { id: 'how-it-works', label: t('nav.howItWorks'), href: '#how-it-works' },
  { id: 'pricing', label: t('nav.pricing'), href: '#pricing' },
  { id: 'faq', label: t('nav.faq'), href: '#faq' },
])
</script>
