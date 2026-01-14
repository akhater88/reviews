
import type { DefineComponent, SlotsType } from 'vue'
type IslandComponent<T> = DefineComponent<{}, {refresh: () => Promise<void>}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, SlotsType<{ fallback: { error: unknown } }>> & T

type HydrationStrategies = {
  hydrateOnVisible?: IntersectionObserverInit | true
  hydrateOnIdle?: number | true
  hydrateOnInteraction?: keyof HTMLElementEventMap | Array<keyof HTMLElementEventMap> | true
  hydrateOnMediaQuery?: string
  hydrateAfter?: number
  hydrateWhen?: boolean
  hydrateNever?: true
}
type LazyComponent<T> = DefineComponent<HydrationStrategies, {}, {}, {}, {}, {}, {}, { hydrated: () => void }> & T

interface _GlobalComponents {
  'ProcessingStepErrorState': typeof import("../../components/ProcessingStep/ErrorState.vue").default
  'ProcessingStepProcessingAnimation': typeof import("../../components/ProcessingStep/ProcessingAnimation.vue").default
  'ProcessingStepStageIndicator': typeof import("../../components/ProcessingStep/StageIndicator.vue").default
  'ProcessingStepSuccessState': typeof import("../../components/ProcessingStep/SuccessState.vue").default
  'ProcessingStep': typeof import("../../components/ProcessingStep/index.vue").default
  'LandingHeroSection': typeof import("../../components/landing/HeroSection.vue").default
  'LandingMobileMenu': typeof import("../../components/landing/MobileMenu.vue").default
  'LandingNavbar': typeof import("../../components/landing/Navbar.vue").default
  'WizardContactInfoStep': typeof import("../../components/wizard/ContactInfoStep.vue").default
  'WizardPhoneVerificationStep': typeof import("../../components/wizard/PhoneVerificationStep.vue").default
  'WizardRestaurantSearchStep': typeof import("../../components/wizard/RestaurantSearchStep.vue").default
  'WizardWelcomePopup': typeof import("../../components/wizard/WelcomePopup.vue").default
  'NuxtWelcome': typeof import("../../node_modules/nuxt/dist/app/components/welcome.vue").default
  'NuxtLayout': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-layout").default
  'NuxtErrorBoundary': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue").default
  'ClientOnly': typeof import("../../node_modules/nuxt/dist/app/components/client-only").default
  'DevOnly': typeof import("../../node_modules/nuxt/dist/app/components/dev-only").default
  'ServerPlaceholder': typeof import("../../node_modules/nuxt/dist/app/components/server-placeholder").default
  'NuxtLink': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-link").default
  'NuxtLoadingIndicator': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-loading-indicator").default
  'NuxtTime': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-time.vue").default
  'NuxtRouteAnnouncer': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-route-announcer").default
  'NuxtImg': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs").NuxtImg
  'NuxtPicture': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs").NuxtPicture
  'NuxtLinkLocale': typeof import("../../node_modules/@nuxtjs/i18n/dist/runtime/components/NuxtLinkLocale").default
  'SwitchLocalePathLink': typeof import("../../node_modules/@nuxtjs/i18n/dist/runtime/components/SwitchLocalePathLink").default
  'Icon': typeof import("../../node_modules/nuxt-icon/dist/runtime/Icon.vue").default
  'IconCSS': typeof import("../../node_modules/nuxt-icon/dist/runtime/IconCSS.vue").default
  'NuxtPage': typeof import("../../node_modules/nuxt/dist/pages/runtime/page").default
  'NoScript': typeof import("../../node_modules/nuxt/dist/head/runtime/components").NoScript
  'Link': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Link
  'Base': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Base
  'Title': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Title
  'Meta': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Meta
  'Style': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Style
  'Head': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Head
  'Html': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Html
  'Body': typeof import("../../node_modules/nuxt/dist/head/runtime/components").Body
  'NuxtIsland': typeof import("../../node_modules/nuxt/dist/app/components/nuxt-island").default
  'LazyProcessingStepErrorState': LazyComponent<typeof import("../../components/ProcessingStep/ErrorState.vue").default>
  'LazyProcessingStepProcessingAnimation': LazyComponent<typeof import("../../components/ProcessingStep/ProcessingAnimation.vue").default>
  'LazyProcessingStepStageIndicator': LazyComponent<typeof import("../../components/ProcessingStep/StageIndicator.vue").default>
  'LazyProcessingStepSuccessState': LazyComponent<typeof import("../../components/ProcessingStep/SuccessState.vue").default>
  'LazyProcessingStep': LazyComponent<typeof import("../../components/ProcessingStep/index.vue").default>
  'LazyLandingHeroSection': LazyComponent<typeof import("../../components/landing/HeroSection.vue").default>
  'LazyLandingMobileMenu': LazyComponent<typeof import("../../components/landing/MobileMenu.vue").default>
  'LazyLandingNavbar': LazyComponent<typeof import("../../components/landing/Navbar.vue").default>
  'LazyWizardContactInfoStep': LazyComponent<typeof import("../../components/wizard/ContactInfoStep.vue").default>
  'LazyWizardPhoneVerificationStep': LazyComponent<typeof import("../../components/wizard/PhoneVerificationStep.vue").default>
  'LazyWizardRestaurantSearchStep': LazyComponent<typeof import("../../components/wizard/RestaurantSearchStep.vue").default>
  'LazyWizardWelcomePopup': LazyComponent<typeof import("../../components/wizard/WelcomePopup.vue").default>
  'LazyNuxtWelcome': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/welcome.vue").default>
  'LazyNuxtLayout': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-layout").default>
  'LazyNuxtErrorBoundary': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue").default>
  'LazyClientOnly': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/client-only").default>
  'LazyDevOnly': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/dev-only").default>
  'LazyServerPlaceholder': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/server-placeholder").default>
  'LazyNuxtLink': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-link").default>
  'LazyNuxtLoadingIndicator': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-loading-indicator").default>
  'LazyNuxtTime': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-time.vue").default>
  'LazyNuxtRouteAnnouncer': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-route-announcer").default>
  'LazyNuxtImg': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs").NuxtImg>
  'LazyNuxtPicture': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-stubs").NuxtPicture>
  'LazyNuxtLinkLocale': LazyComponent<typeof import("../../node_modules/@nuxtjs/i18n/dist/runtime/components/NuxtLinkLocale").default>
  'LazySwitchLocalePathLink': LazyComponent<typeof import("../../node_modules/@nuxtjs/i18n/dist/runtime/components/SwitchLocalePathLink").default>
  'LazyIcon': LazyComponent<typeof import("../../node_modules/nuxt-icon/dist/runtime/Icon.vue").default>
  'LazyIconCSS': LazyComponent<typeof import("../../node_modules/nuxt-icon/dist/runtime/IconCSS.vue").default>
  'LazyNuxtPage': LazyComponent<typeof import("../../node_modules/nuxt/dist/pages/runtime/page").default>
  'LazyNoScript': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").NoScript>
  'LazyLink': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Link>
  'LazyBase': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Base>
  'LazyTitle': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Title>
  'LazyMeta': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Meta>
  'LazyStyle': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Style>
  'LazyHead': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Head>
  'LazyHtml': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Html>
  'LazyBody': LazyComponent<typeof import("../../node_modules/nuxt/dist/head/runtime/components").Body>
  'LazyNuxtIsland': LazyComponent<typeof import("../../node_modules/nuxt/dist/app/components/nuxt-island").default>
}

declare module 'vue' {
  export interface GlobalComponents extends _GlobalComponents { }
}

export {}
