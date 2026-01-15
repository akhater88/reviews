<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10">
    <title>جاري إعداد التقرير - سُمعة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="relative mb-8">
            <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center mx-auto shadow-lg animate-pulse-slow">
                <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">جاري إعداد تقريرك</h1>
        <p class="text-gray-600 mb-6">{{ $report->business_name }}</p>

        <!-- Progress Steps -->
        <div class="bg-white rounded-xl p-4 shadow-sm mb-6">
            @php
                $statusSteps = [
                    'pending' => ['label' => 'بدء التحليل', 'progress' => 10],
                    'fetching_reviews' => ['label' => 'جلب التقييمات', 'progress' => 30],
                    'analyzing' => ['label' => 'تحليل التقييمات', 'progress' => 60],
                    'generating_results' => ['label' => 'إنشاء التقرير', 'progress' => 90],
                ];
                $currentStep = $statusSteps[$report->status] ?? ['label' => 'جاري التحليل', 'progress' => 50];
            @endphp

            <div class="h-3 bg-gray-200 rounded-full overflow-hidden mb-3">
                <div class="h-full bg-gradient-to-l from-blue-600 to-purple-600 rounded-full transition-all duration-500"
                     style="width: {{ $currentStep['progress'] }}%"></div>
            </div>

            <p class="text-blue-600 font-medium">{{ $currentStep['label'] }}...</p>
        </div>

        <div class="flex justify-center gap-2 mb-6">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        </div>

        <p class="text-gray-400 text-sm">
            سيتم تحديث الصفحة تلقائياً عند اكتمال التقرير
        </p>
    </div>
</body>
</html>
