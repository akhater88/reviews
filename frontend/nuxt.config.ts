// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },

  ssr: false,

  app: {
    head: {
      htmlAttrs: {
        lang: 'ar',
        dir: 'rtl',
      },
      title: 'TABsense - تحليل تقييمات المطاعم بالذكاء الاصطناعي',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'احصل على تحليل مجاني لتقييمات مطعمك باستخدام الذكاء الاصطناعي' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap' },
      ],
    },
  },

  css: ['~/assets/css/main.css'],

  modules: [
    '@nuxtjs/tailwindcss',
    '@nuxtjs/i18n',
    'nuxt-icon',
  ],

  i18n: {
    locales: [
      { code: 'ar', file: 'ar.json', dir: 'rtl', name: 'العربية' },
    ],
    defaultLocale: 'ar',
    lazy: true,
    langDir: 'i18n/locales',
    strategy: 'no_prefix',
    vueI18n: './i18n/i18n.config.ts',
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
    },
  },

  compatibilityDate: '2024-11-01',
})
