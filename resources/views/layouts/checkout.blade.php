<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ">

    <title>{{ $title ?? 'الدفع' }} - {{ config('app.name', 'سُمعة') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/sumaa-favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/sumaa-logo-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/sumaa-apple-touch-icon.png') }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="سُمعة - Sumaa">
    <meta property="og:title" content="{{ $title ?? 'الدفع' }} - سُمعة">
    <meta property="og:description" content="حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ">
    <meta property="og:image" content="{{ asset('images/sumaa-og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="ar_SA">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'الدفع' }} - سُمعة">
    <meta name="twitter:description" content="حوّل تقييمات عملائك إلى رؤى قابلة للتنفيذ">
    <meta name="twitter:image" content="{{ asset('images/sumaa-og-image.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                        },
                        warning: {
                            50: '#fffbeb',
                            200: '#fde68a',
                            700: '#b45309',
                        },
                        info: {
                            50: '#eff6ff',
                            200: '#bfdbfe',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold text-primary-600">{{ config('app.name', 'TABsense') }}</h1>
                    @auth
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t py-4 mt-8">
            <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'TABsense') }}. جميع الحقوق محفوظة.
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
