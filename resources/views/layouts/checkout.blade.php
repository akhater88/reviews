<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'الدفع' }} - {{ config('app.name', 'TABsense') }}</title>

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
