<template>
  <section class="relative min-h-[90vh] overflow-hidden bg-gradient-to-br from-sumaa-50/80 via-white to-purple-50/50">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <!-- Floating Shapes -->
      <div class="absolute top-20 right-10 w-72 h-72 bg-sumaa-200/30 rounded-full blur-3xl animate-float-slow"></div>
      <div class="absolute bottom-20 left-10 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl animate-float-slower"></div>
      <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-coral-200/20 rounded-full blur-3xl animate-float"></div>

      <!-- Grid Pattern -->
      <div class="absolute inset-0 bg-grid-pattern opacity-[0.02]"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 relative z-10">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

        <!-- Left Content -->
        <div class="text-center lg:text-right order-2 lg:order-1">
          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm border border-sumaa-100 rounded-full shadow-sm mb-6">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            <span class="text-sm font-medium text-gray-700">{{ $t('hero.badge') }}</span>
          </div>

          <!-- Main Heading -->
          <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            <span class="block">{{ $t('hero.title1') }}</span>
            <span class="block mt-2">
              {{ $t('hero.title2') }}
              <span class="relative">
                <span class="relative z-10 text-transparent bg-clip-text bg-sumaa-gradient">
                  {{ $t('hero.titleHighlight') }}
                </span>
                <span class="absolute bottom-2 right-0 left-0 h-3 bg-sumaa-200/50 -z-10 transform -skew-x-3"></span>
              </span>
            </span>
          </h1>

          <!-- Subtitle -->
          <p class="text-lg sm:text-xl text-gray-600 leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0 lg:mr-0">
            {{ $t('hero.subtitle') }}
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-end mb-8">
            <!-- Primary CTA -->
            <NuxtLink
              to="/get-started"
              class="group relative inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-bold text-lg rounded-2xl shadow-lg shadow-coral-500/25 hover:shadow-xl hover:shadow-coral-500/30 transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
            >
              <span class="relative z-10">{{ $t('hero.ctaPrimary') }}</span>
              <Icon name="lucide:arrow-left" class="w-5 h-5 relative z-10 group-hover:-translate-x-1 transition-transform" />
              <!-- Shine Effect -->
              <div class="absolute inset-0 bg-gradient-to-l from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
            </NuxtLink>

            <!-- Secondary CTA -->
            <button
              @click="openVideoModal"
              class="group inline-flex items-center justify-center gap-3 px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 font-semibold rounded-2xl hover:border-sumaa-300 hover:bg-sumaa-50/50 transition-all duration-300"
            >
              <span class="w-10 h-10 bg-sumaa-100 rounded-full flex items-center justify-center group-hover:bg-sumaa-200 transition-colors">
                <Icon name="lucide:play" class="w-5 h-5 text-sumaa-600 mr-[-2px]" />
              </span>
              <span>{{ $t('hero.ctaSecondary') }}</span>
            </button>
          </div>

          <!-- Trust Indicators -->
          <div class="flex flex-wrap items-center justify-center lg:justify-end gap-6 text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                <Icon name="lucide:check" class="w-3 h-3 text-green-600" />
              </div>
              <span>{{ $t('hero.trust1') }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                <Icon name="lucide:check" class="w-3 h-3 text-green-600" />
              </div>
              <span>{{ $t('hero.trust2') }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                <Icon name="lucide:check" class="w-3 h-3 text-green-600" />
              </div>
              <span>{{ $t('hero.trust3') }}</span>
            </div>
          </div>
        </div>

        <!-- Right Content - Dashboard Mockup -->
        <div class="order-1 lg:order-2 relative">
          <div class="relative mx-auto max-w-lg lg:max-w-none">
            <!-- Main Dashboard Card -->
            <div class="relative bg-white rounded-3xl shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform hover:scale-[1.02] transition-transform duration-500">
              <!-- Browser Header -->
              <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center gap-2">
                <div class="flex gap-1.5">
                  <div class="w-3 h-3 rounded-full bg-red-400"></div>
                  <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                  <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="flex-1 mx-4">
                  <div class="bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-400 flex items-center gap-2">
                    <Icon name="lucide:lock" class="w-3 h-3" />
                    <span dir="ltr">app.getsumaa.app</span>
                  </div>
                </div>
              </div>

              <!-- Dashboard Content -->
              <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-sumaa-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <Icon name="lucide:bar-chart-3" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <p class="font-bold text-gray-900 text-sm">{{ $t('hero.dashboard.title') }}</p>
                      <p class="text-xs text-gray-500">{{ $t('hero.dashboard.subtitle') }}</p>
                    </div>
                  </div>
                  <div class="text-left">
                    <div class="flex items-center gap-1">
                      <span class="text-lg font-bold text-gray-900">4.8</span>
                      <div class="flex">
                        <Icon v-for="i in 5" :key="i" name="lucide:star"
                              :class="i <= 4 ? 'text-yellow-400 fill-yellow-400' : 'text-yellow-400'"
                              class="w-4 h-4" />
                      </div>
                    </div>
                    <p class="text-xs text-gray-500">{{ $t('hero.dashboard.avgRating') }}</p>
                  </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-3 mb-6">
                  <div class="bg-gradient-to-br from-sumaa-50 to-sumaa-100/50 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-sumaa-600">847</p>
                    <p class="text-xs text-sumaa-700/70">{{ $t('hero.dashboard.reviews') }}</p>
                  </div>
                  <div class="bg-gradient-to-br from-green-50 to-green-100/50 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-green-600">92%</p>
                    <p class="text-xs text-green-700/70">{{ $t('hero.dashboard.positive') }}</p>
                  </div>
                  <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl p-3 text-center">
                    <p class="text-2xl font-bold text-purple-600">+12%</p>
                    <p class="text-xs text-purple-700/70">{{ $t('hero.dashboard.thisMonth') }}</p>
                  </div>
                </div>

                <!-- Mini Chart -->
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                  <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">{{ $t('hero.dashboard.sentimentAnalysis') }}</span>
                    <span class="text-xs text-green-600 bg-green-100 px-2 py-0.5 rounded-full">↑ 5%</span>
                  </div>
                  <div class="h-16 flex items-end gap-1">
                    <div v-for="(height, i) in chartBars" :key="i"
                         class="flex-1 rounded-t transition-all duration-500"
                         :class="i === chartBars.length - 1 ? 'bg-sumaa-500' : 'bg-sumaa-200'"
                         :style="{ height: `${height}%` }">
                    </div>
                  </div>
                </div>

                <!-- AI Recommendation Preview -->
                <div class="bg-gradient-to-l from-purple-50 to-sumaa-50 border border-purple-100 rounded-xl p-3">
                  <div class="flex items-start gap-2">
                    <div class="w-6 h-6 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                      <Icon name="lucide:sparkles" class="w-3 h-3 text-white" />
                    </div>
                    <div>
                      <p class="text-xs font-medium text-purple-900">{{ $t('hero.dashboard.aiRecommendation') }}</p>
                      <p class="text-xs text-purple-700/80">{{ $t('hero.dashboard.aiRecommendationText') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Floating Elements -->
            <!-- Rating Badge -->
            <div class="absolute -left-4 top-1/4 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-4 border border-gray-100 animate-float hidden lg:block">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-xl flex items-center justify-center">
                  <Icon name="lucide:star" class="w-6 h-6 text-white fill-white" />
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-900">4.8</p>
                  <p class="text-xs text-gray-500">{{ $t('hero.floatingBadge1') }}</p>
                </div>
              </div>
            </div>

            <!-- Notification Badge -->
            <div class="absolute -right-4 bottom-1/4 bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-3 border border-gray-100 animate-float-delayed hidden lg:block">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <Icon name="lucide:trending-up" class="w-4 h-4 text-white" />
                </div>
                <div>
                  <p class="text-sm font-bold text-gray-900">+23 {{ $t('hero.floatingBadge2') }}</p>
                  <p class="text-xs text-gray-500">{{ $t('hero.floatingBadge2Time') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Wave -->
    <div class="absolute bottom-0 left-0 right-0">
      <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
        <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
      </svg>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const chartBars = ref([40, 55, 45, 60, 50, 70, 65, 80, 75, 85, 90, 95])

const emit = defineEmits(['open-video'])

const openVideoModal = () => {
  emit('open-video')
}
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

@keyframes float-slow {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-20px) scale(1.05); }
}

@keyframes float-slower {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-30px) scale(1.02); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float 3s ease-in-out infinite;
  animation-delay: 1.5s;
}

.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-slower {
  animation: float-slower 12s ease-in-out infinite;
}
</style>
