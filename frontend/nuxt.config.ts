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
      title: 'سُمعة - منصة إدارة التقييمات الذكية',
      titleTemplate: '%s | سُمعة',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ. منصة سُمعة تقدم تحليل ذكي لتقييمات Google مع توصيات عملية لتحسين سمعة مطعمك.' },
        { name: 'theme-color', content: '#6366F1' },
        // Open Graph
        { property: 'og:type', content: 'website' },
        { property: 'og:site_name', content: 'سُمعة - Sumaa' },
        { property: 'og:title', content: 'سُمعة - منصة إدارة التقييمات الذكية' },
        { property: 'og:description', content: 'حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ' },
        { property: 'og:image', content: 'https://getsumaa.app/images/sumaa-og-image.png' },
        { property: 'og:url', content: 'https://getsumaa.app' },
        { property: 'og:locale', content: 'ar_SA' },
        // Twitter
        { name: 'twitter:card', content: 'summary_large_image' },
        { name: 'twitter:title', content: 'سُمعة - منصة إدارة التقييمات الذكية' },
        { name: 'twitter:description', content: 'حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ' },
        { name: 'twitter:image', content: 'https://getsumaa.app/images/sumaa-og-image.png' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/images/sumaa-favicon.ico' },
        { rel: 'icon', type: 'image/svg+xml', href: '/images/sumaa-logo-icon.svg' },
        { rel: 'apple-touch-icon', href: '/images/sumaa-apple-touch-icon.png' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap' },
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
