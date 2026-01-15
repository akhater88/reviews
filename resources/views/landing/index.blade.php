<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('app.heroSubtitle') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>سُمعة - منصة إدارة التقييمات الذكية</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/sumaa-favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/sumaa-logo-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/sumaa-apple-touch-icon.png') }}">

    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="سُمعة - Sumaa">
    <meta property="og:title" content="سُمعة - منصة إدارة التقييمات الذكية">
    <meta property="og:description" content="{{ __('app.heroSubtitle') }}">
    <meta property="og:image" content="{{ asset('images/sumaa-og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="سُمعة - منصة إدارة التقييمات الذكية">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:locale" content="ar_SA">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="سُمعة - منصة إدارة التقييمات الذكية">
    <meta name="twitter:description" content="{{ __('app.heroSubtitle') }}">
    <meta name="twitter:image" content="{{ asset('images/sumaa-og-image.png') }}">
    <meta name="twitter:image:alt" content="سُمعة - منصة إدارة التقييمات الذكية">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --header-height: 67px;
        }

        body {
            font-family: 'Tajawal', sans-serif;
        }

        /* Main content offset for fixed header */
        main {
            padding-top: var(--header-height);
        }

        /* Hide elements with x-cloak until Alpine initializes */
        [x-cloak] {
            display: none !important;
        }

        /* Animations */
        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in-left {
            0% {
                opacity: 0;
                transform: translateX(-30px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes float-slow {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes float-slower {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }
            50% {
                transform: scale(1);
                opacity: 0.4;
            }
            100% {
                transform: scale(0.8);
                opacity: 0.8;
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out;
        }

        .animate-fade-in-left {
            animation: fade-in-left 0.6s ease-out 0.3s backwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 3s ease-in-out infinite 0.5s;
        }

        .animate-float-slow {
            animation: float-slow 6s ease-in-out infinite;
        }

        .animate-float-slower {
            animation: float-slower 8s ease-in-out infinite;
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }

        .animate-pulse-ring {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        /* Grid Pattern Background */
        .bg-grid-pattern {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Alpine collapse transition */
        [x-collapse] {
            overflow: hidden;
        }

        [x-collapse].collapsing {
            transition: height 0.3s ease-out;
        }

        /* Custom scrollbar for mobile menu */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased rtl">
    {{-- Header --}}
    @include('landing.partials.header')

    {{-- Main Content --}}
    <main>
        {{-- Hero Section --}}
        @include('landing.partials.hero')

        {{-- Features Section --}}
        @include('landing.partials.features')

        {{-- How It Works Section --}}
        @include('landing.partials.how-it-works')

        {{-- Sample Report Section --}}
        @include('landing.partials.sample-report')

        {{-- Testimonials Section --}}
        @include('landing.partials.testimonials')

        {{-- Pricing Section --}}
        @include('landing.partials.pricing')

        {{-- FAQ Section --}}
        @include('landing.partials.faq')

        {{-- CTA Section --}}
        @include('landing.partials.cta')
    </main>

    {{-- Footer --}}
    @include('landing.partials.footer')

    {{-- Sticky CTA (Mobile) --}}
    @include('landing.partials.sticky-cta')

    {{-- Alpine Collapse Plugin (for FAQ accordion) --}}
    <script>
        // Simple collapse implementation for Alpine
        document.addEventListener('alpine:init', () => {
            Alpine.directive('collapse', (el, { expression }, { evaluate, effect }) => {
                // Store original height
                let fullHeight;

                // Get the full height
                const getFullHeight = () => {
                    el.style.height = 'auto';
                    fullHeight = el.scrollHeight + 'px';
                    el.style.height = null;
                    return fullHeight;
                };

                // Initialize
                el.style.overflow = 'hidden';

                effect(() => {
                    const show = evaluate(expression !== '' ? expression : el._x_bindings?.['x-show']);

                    if (show === undefined) return;

                    if (show) {
                        el.style.height = '0px';
                        el.offsetHeight; // Force reflow
                        el.style.transition = 'height 0.3s ease-out';
                        el.style.height = getFullHeight();

                        setTimeout(() => {
                            el.style.height = 'auto';
                            el.style.overflow = null;
                        }, 300);
                    } else {
                        el.style.height = el.scrollHeight + 'px';
                        el.offsetHeight; // Force reflow
                        el.style.transition = 'height 0.2s ease-in';
                        el.style.height = '0px';
                        el.style.overflow = 'hidden';
                    }
                });
            });
        });
    </script>
</body>
</html>
