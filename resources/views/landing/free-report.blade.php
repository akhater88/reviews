<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>TABsense - تقرير التحليل المجاني</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

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

        [x-cloak] {
            display: none !important;
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .animate-pulse-slow {
            animation: pulse-slow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased rtl min-h-screen">
    <div x-data="freeReport()" x-init="loadReport()" class="container mx-auto px-4 py-8 max-w-4xl">
        {{-- Header --}}
        <header class="text-center mb-8">
            <a href="/" class="inline-block">
                <img src="{{ asset('images/logo.png') }}" alt="TABsense" class="h-12 mx-auto mb-4" />
            </a>
            <h1 class="text-2xl font-bold text-gray-900">تقرير التحليل المجاني</h1>
        </header>

        {{-- Loading State --}}
        <template x-if="loading">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-600 text-lg">جاري تحميل التقرير...</p>
            </div>
        </template>

        {{-- Error State --}}
        <template x-if="error && !loading">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">عذراً</h2>
                <p class="text-gray-600 mb-6" x-text="error"></p>
                <a href="/get-started" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    احصل على تقرير جديد
                </a>
            </div>
        </template>

        {{-- Processing State --}}
        <template x-if="report && report.is_processing && !loading">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse-slow">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2" x-text="report.business_name"></h2>
                <p class="text-gray-600 mb-2">جاري إعداد التقرير...</p>
                <p class="text-sm text-gray-500">قد يستغرق هذا بضع دقائق. سيتم تحديث الصفحة تلقائياً.</p>
                <div class="mt-6 flex justify-center gap-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </template>

        {{-- Failed State --}}
        <template x-if="report && report.has_failed && !loading">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">فشل في إنشاء التقرير</h2>
                <p class="text-gray-600 mb-6" x-text="report.message"></p>
                <a href="/get-started" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    حاول مرة أخرى
                </a>
            </div>
        </template>

        {{-- Completed Report --}}
        <template x-if="report && report.is_completed && report.result && !loading">
            <div class="space-y-6">
                {{-- Business Info --}}
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="report.business_name"></h2>
                    <p class="text-gray-500" x-text="report.business_address"></p>
                </div>

                {{-- Score Card --}}
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-200 text-sm mb-1">النتيجة الإجمالية</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-bold" x-text="report.result.overall_score"></span>
                                <span class="text-xl text-blue-200">/ 10</span>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center">
                                <span class="text-3xl font-bold" x-text="report.result.grade"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-3xl font-bold" x-text="report.result.total_reviews"></p>
                            <p class="text-blue-200 text-sm">تقييم</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold" x-text="report.result.average_rating"></p>
                            <p class="text-blue-200 text-sm">متوسط التقييم</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold" x-text="report.result.sentiment_percentages?.positive + '%'"></p>
                            <p class="text-blue-200 text-sm">إيجابي</p>
                        </div>
                    </div>
                </div>

                {{-- Executive Summary --}}
                <div class="bg-white rounded-2xl shadow-lg p-6" x-show="report.result.executive_summary">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">الملخص التنفيذي</h3>
                    <p class="text-gray-600 leading-relaxed" x-text="report.result.executive_summary"></p>
                </div>

                {{-- Strengths & Weaknesses --}}
                <div class="grid md:grid-cols-2 gap-6">
                    {{-- Strengths --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6" x-show="report.result.top_strengths?.length">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            نقاط القوة
                        </h3>
                        <ul class="space-y-2">
                            <template x-for="strength in report.result.top_strengths">
                                <li class="flex items-start gap-2 text-gray-600">
                                    <span class="text-green-500 mt-1">•</span>
                                    <span x-text="strength"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Weaknesses --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6" x-show="report.result.top_weaknesses?.length">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </span>
                            نقاط التحسين
                        </h3>
                        <ul class="space-y-2">
                            <template x-for="weakness in report.result.top_weaknesses">
                                <li class="flex items-start gap-2 text-gray-600">
                                    <span class="text-red-500 mt-1">•</span>
                                    <span x-text="weakness"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Recommendations --}}
                <div class="bg-white rounded-2xl shadow-lg p-6" x-show="report.result.recommendations?.length">
                    <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </span>
                        التوصيات
                    </h3>
                    <ul class="space-y-3">
                        <template x-for="(rec, index) in report.result.recommendations">
                            <li class="flex items-start gap-3 text-gray-600">
                                <span class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-sm font-bold flex-shrink-0" x-text="index + 1"></span>
                                <span x-text="rec"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- CTA --}}
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white text-center">
                    <h3 class="text-xl font-bold mb-2">هل تريد تحليلاً أعمق؟</h3>
                    <p class="text-green-100 mb-4">اشترك في TABsense للحصول على تقارير مفصلة، تنبيهات فورية، وأدوات الرد الذكي</p>
                    <a href="/get-started" class="inline-block bg-white text-green-600 px-8 py-3 rounded-lg font-bold hover:bg-green-50 transition-colors">
                        ابدأ الآن
                    </a>
                </div>
            </div>
        </template>
    </div>

    <script>
        function freeReport() {
            return {
                loading: true,
                error: null,
                report: null,
                token: '{{ $token }}',
                pollInterval: null,

                async loadReport() {
                    try {
                        const response = await fetch(`/api/free-report/view/${this.token}`);
                        const data = await response.json();

                        if (!data.success) {
                            this.error = data.message || 'الرابط غير صالح أو منتهي الصلاحية';
                            this.loading = false;
                            return;
                        }

                        this.report = data.data;
                        this.loading = false;

                        // If still processing, poll for updates
                        if (this.report.is_processing) {
                            this.startPolling();
                        }
                    } catch (e) {
                        this.error = 'حدث خطأ أثناء تحميل التقرير';
                        this.loading = false;
                    }
                },

                startPolling() {
                    this.pollInterval = setInterval(async () => {
                        try {
                            const response = await fetch(`/api/free-report/view/${this.token}`);
                            const data = await response.json();

                            if (data.success) {
                                this.report = data.data;

                                // Stop polling if completed or failed
                                if (this.report.is_completed || this.report.has_failed) {
                                    this.stopPolling();
                                }
                            }
                        } catch (e) {
                            // Silently ignore polling errors
                        }
                    }, 5000); // Poll every 5 seconds
                },

                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                }
            };
        }
    </script>
</body>
</html>
