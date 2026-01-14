
// @ts-nocheck


export const localeCodes =  [
  "ar"
]

export const localeLoaders = {
  "ar": [{ key: "../i18n/locales/ar.json", load: () => import("../i18n/locales/ar.json" /* webpackChunkName: "locale__Users_abdullahkayed_WorkTabsen_vibecodinng_reviews_tabsense_reviews_frontend_i18n_locales_ar_json" */), cache: true }]
}

export const vueI18nConfigs = [
  () => import("../i18n/i18n.config.ts?hash=5b32d2e0&config=1" /* webpackChunkName: "__i18n_i18n_config_ts_5b32d2e0" */)
]

export const nuxtI18nOptions = {
  "experimental": {
    "localeDetector": "",
    "switchLocalePathLinkSSR": false,
    "autoImportTranslationFunctions": false
  },
  "bundle": {
    "compositionOnly": true,
    "runtimeOnly": false,
    "fullInstall": true,
    "dropMessageCompiler": false
  },
  "compilation": {
    "jit": true,
    "strictMessage": true,
    "escapeHtml": false
  },
  "customBlocks": {
    "defaultSFCLang": "json",
    "globalSFCScope": false
  },
  "vueI18n": "./i18n/i18n.config.ts",
  "locales": [
    {
      "code": "ar",
      "dir": "rtl",
      "name": "العربية",
      "files": [
        "/Users/abdullahkayed/WorkTabsen/vibecodinng-reviews/tabsense-reviews/frontend/i18n/locales/ar.json"
      ]
    }
  ],
  "defaultLocale": "ar",
  "defaultDirection": "ltr",
  "routesNameSeparator": "___",
  "trailingSlash": false,
  "defaultLocaleRouteNameSuffix": "default",
  "strategy": "no_prefix",
  "lazy": true,
  "langDir": "i18n/locales",
  "detectBrowserLanguage": {
    "alwaysRedirect": false,
    "cookieCrossOrigin": false,
    "cookieDomain": null,
    "cookieKey": "i18n_redirected",
    "cookieSecure": false,
    "fallbackLocale": "",
    "redirectOn": "root",
    "useCookie": true
  },
  "differentDomains": false,
  "baseUrl": "",
  "dynamicRouteParams": false,
  "customRoutes": "page",
  "pages": {},
  "skipSettingLocaleOnNavigate": false,
  "types": "composition",
  "debug": false,
  "parallelPlugin": false,
  "multiDomainLocales": false,
  "i18nModules": []
}

export const normalizedLocales = [
  {
    "code": "ar",
    "dir": "rtl",
    "name": "العربية",
    "files": [
      {
        "path": "/Users/abdullahkayed/WorkTabsen/vibecodinng-reviews/tabsense-reviews/frontend/i18n/locales/ar.json"
      }
    ]
  }
]

export const NUXT_I18N_MODULE_ID = "@nuxtjs/i18n"
export const parallelPlugin = false
export const isSSG = false

export const DEFAULT_DYNAMIC_PARAMS_KEY = "nuxtI18n"
export const DEFAULT_COOKIE_KEY = "i18n_redirected"
export const SWITCH_LOCALE_PATH_LINK_IDENTIFIER = "nuxt-i18n-slp"
