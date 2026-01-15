import type { Config } from 'tailwindcss'

export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './composables/**/*.{js,ts}',
    './plugins/**/*.{js,ts}',
    './app.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['IBM Plex Sans Arabic', 'Tajawal', 'sans-serif'],
      },
      colors: {
        // Sumaa Primary - Indigo
        sumaa: {
          50: '#EEF2FF',
          100: '#E0E7FF',
          200: '#C7D2FE',
          300: '#A5B4FC',
          400: '#818CF8',
          500: '#6366F1',  // Primary
          600: '#4F46E5',
          700: '#4338CA',
          800: '#3730A3',
          900: '#312E81',
        },
        // Keep primary as alias for compatibility
        primary: {
          50: '#EEF2FF',
          100: '#E0E7FF',
          200: '#C7D2FE',
          300: '#A5B4FC',
          400: '#818CF8',
          500: '#6366F1',
          600: '#4F46E5',
          700: '#4338CA',
          800: '#3730A3',
          900: '#312E81',
        },
        // Sumaa Secondary - Purple
        secondary: {
          50: '#FAF5FF',
          100: '#F3E8FF',
          200: '#E9D5FF',
          300: '#D8B4FE',
          400: '#A78BFA',
          500: '#8B5CF6',  // Secondary
          600: '#7C3AED',
          700: '#6D28D9',
          800: '#5B21B6',
          900: '#4C1D95',
        },
        // CTA Color - Coral
        coral: {
          50: '#FEF2F2',
          100: '#FEE2E2',
          200: '#FECACA',
          300: '#FCA5A5',
          400: '#EC8B85',
          500: '#E05D56',  // CTA
          600: '#C55550',
          700: '#B91C1C',
          800: '#991B1B',
          900: '#7F1D1D',
        },
        // Star/Rating Color
        gold: {
          400: '#FBBF24',
          500: '#F59E0B',  // Stars
          600: '#D97706',
        },
      },
      backgroundImage: {
        'sumaa-gradient': 'linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%)',
        'sumaa-gradient-h': 'linear-gradient(90deg, #6366F1 0%, #8B5CF6 100%)',
        'cta-gradient': 'linear-gradient(135deg, #E05D56 0%, #EC8B85 100%)',
      },
      animation: {
        'spin-slow': 'spin 8s linear infinite',
        'ping-slow': 'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
        'bounce-subtle': 'bounce-subtle 1s ease-in-out infinite',
        'scale-in': 'scale-in 0.5s ease-out forwards',
        'ping-once': 'ping-once 1s ease-out forwards',
        'confetti-fall': 'confetti-fall 3s linear forwards',
        'shimmer': 'shimmer 2s ease-in-out infinite',
      },
      keyframes: {
        'bounce-subtle': {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-5px)' },
        },
        'scale-in': {
          '0%': { transform: 'scale(0)', opacity: '0' },
          '50%': { transform: 'scale(1.2)' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
        'ping-once': {
          '0%': { transform: 'scale(1)', opacity: '1' },
          '100%': { transform: 'scale(1.5)', opacity: '0' },
        },
        'confetti-fall': {
          '0%': { transform: 'translateY(-100%) rotate(0deg)', opacity: '1' },
          '100%': { transform: 'translateY(100vh) rotate(720deg)', opacity: '0' },
        },
        'shimmer': {
          '0%': { backgroundPosition: '-200% center' },
          '100%': { backgroundPosition: '200% center' },
        },
      },
    },
  },
  plugins: [],
} satisfies Config
