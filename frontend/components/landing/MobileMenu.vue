<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isOpen" class="fixed inset-0 z-50">
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        @click="$emit('close')"
      ></div>

      <!-- Menu Panel -->
      <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
      >
        <div
          v-if="isOpen"
          class="absolute top-0 right-0 bottom-0 w-full max-w-sm bg-white shadow-2xl flex flex-col"
        >
          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <NuxtLink to="/" class="flex items-center gap-2" @click="$emit('close')">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <Icon name="lucide:bar-chart-3" class="w-4 h-4 text-white" />
              </div>
              <span class="text-lg font-bold text-gray-900">TABsense</span>
            </NuxtLink>
            <button
              @click="$emit('close')"
              class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              <Icon name="lucide:x" class="w-5 h-5 text-gray-600" />
            </button>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 p-4 overflow-y-auto">
            <ul class="space-y-1">
              <li v-for="item in menuItems" :key="item.id">
                <a
                  :href="item.href"
                  @click="$emit('close')"
                  class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 group"
                >
                  <span class="w-10 h-10 bg-gray-100 group-hover:bg-blue-100 rounded-xl flex items-center justify-center transition-colors">
                    <Icon :name="item.icon" class="w-5 h-5 text-gray-500 group-hover:text-blue-600 transition-colors" />
                  </span>
                  <div>
                    <span class="font-semibold block">{{ item.label }}</span>
                    <span class="text-xs text-gray-500">{{ item.description }}</span>
                  </div>
                  <Icon name="lucide:chevron-left" class="w-4 h-4 text-gray-400 mr-auto opacity-0 group-hover:opacity-100 transition-opacity" />
                </a>
              </li>
            </ul>
          </nav>

          <!-- Divider -->
          <div class="px-4">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- CTA Section -->
          <div class="p-4 space-y-3">
            <!-- Primary CTA -->
            <NuxtLink
              to="/get-started"
              @click="$emit('close')"
              class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-bold rounded-xl shadow-lg shadow-coral-500/20 hover:shadow-xl transition-all duration-300"
            >
              <span>{{ $t('nav.getReport') }}</span>
              <Icon name="lucide:arrow-left" class="w-5 h-5" />
            </NuxtLink>

            <!-- Secondary CTA -->
            <NuxtLink
              to="/login"
              @click="$emit('close')"
              class="flex items-center justify-center gap-2 w-full px-6 py-3 border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
            >
              <Icon name="lucide:log-in" class="w-5 h-5" />
              <span>{{ $t('nav.login') }}</span>
            </NuxtLink>
          </div>

          <!-- Trust Badges -->
          <div class="p-4 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
              <div class="flex items-center gap-1">
                <Icon name="lucide:shield-check" class="w-4 h-4 text-green-500" />
                <span>{{ $t('nav.trustSecure') }}</span>
              </div>
              <div class="flex items-center gap-1">
                <Icon name="lucide:zap" class="w-4 h-4 text-yellow-500" />
                <span>{{ $t('nav.trustFast') }}</span>
              </div>
              <div class="flex items-center gap-1">
                <Icon name="lucide:globe" class="w-4 h-4 text-blue-500" />
                <span>{{ $t('nav.trustArabic') }}</span>
              </div>
            </div>
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

defineEmits(['close'])

const menuItems = computed(() => [
  {
    id: 'features',
    label: t('nav.features'),
    description: t('nav.featuresDesc'),
    href: '#features',
    icon: 'lucide:sparkles'
  },
  {
    id: 'how-it-works',
    label: t('nav.howItWorks'),
    description: t('nav.howItWorksDesc'),
    href: '#how-it-works',
    icon: 'lucide:play-circle'
  },
  {
    id: 'pricing',
    label: t('nav.pricing'),
    description: t('nav.pricingDesc'),
    href: '#pricing',
    icon: 'lucide:tag'
  },
  {
    id: 'faq',
    label: t('nav.faq'),
    description: t('nav.faqDesc'),
    href: '#faq',
    icon: 'lucide:help-circle'
  },
])
</script>
