<template>
  <div>
    <!-- Navbar -->
    <LandingNavbar @menu-open="isMobileMenuOpen = true" />

    <!-- Mobile Menu -->
    <LandingMobileMenu
      :is-open="isMobileMenuOpen"
      @close="isMobileMenuOpen = false"
    />

    <!-- Hero Section -->
    <LandingHeroSection />

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $t('features.title') }}
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            {{ $t('features.subtitle') }}
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(feature, index) in features"
            :key="index"
            class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 card-hover"
          >
            <div
              class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
              :class="feature.bgColor"
            >
              <Icon :name="feature.icon" class="w-7 h-7" :class="feature.iconColor" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $t('howItWorks.title') }}
          </h2>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            {{ $t('howItWorks.subtitle') }}
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="relative text-center"
          >
            <!-- Step Number -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-sumaa-500 to-purple-500 text-white text-2xl font-bold mb-6 shadow-lg">
              {{ index + 1 }}
            </div>
            <!-- Connector Line -->
            <div
              v-if="index < steps.length - 1"
              class="hidden md:block absolute top-8 right-0 w-full h-0.5 bg-gradient-to-l from-sumaa-200 to-purple-200"
              style="right: -50%; width: 100%;"
            ></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ step.title }}</h3>
            <p class="text-gray-600">{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gradient-to-br from-sumaa-50 to-purple-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $t('pricing.title') }}
          </h2>
          <p class="text-xl text-gray-600">
            {{ $t('pricing.subtitle') }}
          </p>
        </div>

        <!-- Free Report Card -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <div class="p-8 text-center border-b border-gray-100">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold mb-4">
              <Icon name="lucide:gift" class="w-4 h-4" />
              <span>{{ $t('pricing.freeLabel') }}</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $t('pricing.freeTitle') }}</h3>
            <p class="text-gray-600">{{ $t('pricing.freeDescription') }}</p>
          </div>
          <div class="p-8">
            <ul class="space-y-4 mb-8">
              <li
                v-for="(item, index) in freeFeatures"
                :key="index"
                class="flex items-center gap-3"
              >
                <Icon name="lucide:check-circle" class="w-5 h-5 text-green-500 flex-shrink-0" />
                <span class="text-gray-700">{{ item }}</span>
              </li>
            </ul>
            <NuxtLink
              to="/get-started"
              class="block w-full text-center px-6 py-4 bg-gradient-to-l from-[#df625b] to-[#e87b75] text-white font-bold text-lg rounded-xl shadow-lg shadow-red-500/20 hover:shadow-xl hover:scale-[1.02] transition-all duration-300"
            >
              {{ $t('pricing.getStarted') }}
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            {{ $t('faq.title') }}
          </h2>
        </div>

        <div class="space-y-4">
          <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <button
              @click="toggleFaq(index)"
              class="w-full flex items-center justify-between p-6 text-right"
            >
              <span class="font-semibold text-gray-900">{{ faq.question }}</span>
              <Icon
                name="lucide:chevron-down"
                class="w-5 h-5 text-gray-400 transition-transform duration-200"
                :class="{ 'rotate-180': openFaq === index }"
              />
            </button>
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-96 opacity-100"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="max-h-96 opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <div v-if="openFaq === index" class="px-6 pb-6 text-gray-600 overflow-hidden">
                {{ faq.answer }}
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <!-- Logo -->
          <div class="flex items-center">
            <SumaaLogoWhite class="h-12" />
          </div>

          <!-- Links -->
          <div class="flex items-center gap-6 text-gray-400">
            <a href="#" class="hover:text-white transition-colors">{{ $t('footer.privacy') }}</a>
            <a href="#" class="hover:text-white transition-colors">{{ $t('footer.terms') }}</a>
            <a href="#" class="hover:text-white transition-colors">{{ $t('footer.contact') }}</a>
          </div>

          <!-- Copyright -->
          <p class="text-gray-500 text-sm">
            {{ $t('footer.copyright', { year: new Date().getFullYear() }) }}
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const { t } = useI18n()

const isMobileMenuOpen = ref(false)
const openFaq = ref<number | null>(null)

const features = computed(() => [
  {
    icon: 'lucide:brain',
    title: t('features.ai.title'),
    description: t('features.ai.description'),
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600',
  },
  {
    icon: 'lucide:message-circle',
    title: t('features.whatsapp.title'),
    description: t('features.whatsapp.description'),
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600',
  },
  {
    icon: 'lucide:globe',
    title: t('features.arabic.title'),
    description: t('features.arabic.description'),
    bgColor: 'bg-sumaa-100',
    iconColor: 'text-sumaa-600',
  },
  {
    icon: 'lucide:zap',
    title: t('features.fast.title'),
    description: t('features.fast.description'),
    bgColor: 'bg-yellow-100',
    iconColor: 'text-yellow-600',
  },
  {
    icon: 'lucide:bar-chart-3',
    title: t('features.insights.title'),
    description: t('features.insights.description'),
    bgColor: 'bg-red-100',
    iconColor: 'text-red-600',
  },
  {
    icon: 'lucide:shield-check',
    title: t('features.secure.title'),
    description: t('features.secure.description'),
    bgColor: 'bg-indigo-100',
    iconColor: 'text-indigo-600',
  },
])

const steps = computed(() => [
  {
    title: t('howItWorks.step1.title'),
    description: t('howItWorks.step1.description'),
  },
  {
    title: t('howItWorks.step2.title'),
    description: t('howItWorks.step2.description'),
  },
  {
    title: t('howItWorks.step3.title'),
    description: t('howItWorks.step3.description'),
  },
])

const freeFeatures = computed(() => [
  t('pricing.feature1'),
  t('pricing.feature2'),
  t('pricing.feature3'),
  t('pricing.feature4'),
  t('pricing.feature5'),
])

const faqs = computed(() => [
  {
    question: t('faq.q1'),
    answer: t('faq.a1'),
  },
  {
    question: t('faq.q2'),
    answer: t('faq.a2'),
  },
  {
    question: t('faq.q3'),
    answer: t('faq.a3'),
  },
  {
    question: t('faq.q4'),
    answer: t('faq.a4'),
  },
])

const toggleFaq = (index: number) => {
  openFaq.value = openFaq.value === index ? null : index
}
</script>
