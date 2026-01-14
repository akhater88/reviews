<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فشل إنشاء التقرير - TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">فشل إنشاء التقرير</h1>
        <p class="text-gray-600 mb-2">{{ $report->business_name }}</p>

        @if($report->error_message)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-right">
                <p class="text-red-700 text-sm">{{ $report->error_message }}</p>
            </div>
        @else
            <p class="text-gray-500 mb-6">
                حدث خطأ أثناء إنشاء التقرير. يرجى المحاولة مرة أخرى.
            </p>
        @endif

        <div class="space-y-3">
            <a href="/get-started" class="block w-full bg-blue-600 text-white py-3 rounded-full font-semibold hover:bg-blue-700 transition-colors">
                حاول مرة أخرى
            </a>
            <a href="mailto:support@tabsense.net" class="block w-full bg-gray-200 text-gray-700 py-3 rounded-full font-semibold hover:bg-gray-300 transition-colors">
                تواصل مع الدعم
            </a>
        </div>
    </div>
</body>
</html>
