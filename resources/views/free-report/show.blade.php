<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير {{ $report->business_name }} - سُمعة</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }
        .tab-active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
            font-weight: bold;
        }
        .tab-inactive {
            color: #6b7280;
        }
        .tab-inactive:hover {
            color: #374151;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sticky CTA Banner -->
    @include('free-report.components.cta-banner')

    <div class="pt-16"> <!-- Padding for sticky banner -->
        <!-- Header -->
        @include('free-report.components.header', ['report' => $report, 'result' => $result])

        <!-- Main Content -->
        <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Tabs Navigation -->
            @include('free-report.components.tabs', ['activeTab' => $activeTab, 'token' => $token])

            <!-- Tab Content -->
            <div class="mt-6">
                @switch($activeTab)
                    @case('overview')
                        @include('free-report.partials.tab-overview', ['report' => $report, 'result' => $result])
                        @break
                    @case('keywords')
                        @include('free-report.partials.tab-keywords', ['result' => $result])
                        @break
                    @case('recommendations')
                        @include('free-report.partials.tab-recommendations', ['result' => $result])
                        @break
                    @case('sentiment')
                        @include('free-report.partials.tab-sentiment', ['result' => $result, 'reviews' => $reviews])
                        @break
                    @case('operational')
                        @include('free-report.partials.tab-operational', ['result' => $result])
                        @break
                    @case('categories')
                        @include('free-report.partials.tab-categories', ['result' => $result])
                        @break
                @endswitch
            </div>

            <!-- Bottom CTA Section -->
            @include('free-report.components.cta-section')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 mt-12">
            <div class="container mx-auto px-4 text-center">
                <img src="{{ asset('images/sumaa-logo-white.svg') }}" alt="سُمعة" class="h-8 mx-auto mb-4">
                <p class="text-gray-400 text-sm">
                    {{ date('Y') }} سُمعة. جميع الحقوق محفوظة.
                </p>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
