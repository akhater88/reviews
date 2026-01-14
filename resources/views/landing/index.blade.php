<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('app.heroSubtitle') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TABsense - منصة إدارة تقييمات Google للمطاعم</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Open Graph / Social Media --}}
    <meta property="og:title" content="TABsense - منصة إدارة تقييمات Google للمطاعم">
    <meta property="og:description" content="{{ __('app.heroSubtitle') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
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
