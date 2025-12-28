<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتهت صلاحية الجائزة - مسابقة أفضل مطعم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="text-6xl mb-4">⏰</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">انتهت صلاحية الجائزة</h1>
            <p class="text-gray-500 mb-6">للأسف، انتهت فترة استلام هذه الجائزة (30 يوماً)</p>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-700 text-sm">
                    الجائزة: {{ number_format($winner->prize_amount ?? 0) }} ر.س
                </p>
            </div>

            <div class="bg-gray-100 rounded-lg p-4 mb-6">
                <p class="text-gray-600 text-sm">
                    إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع الدعم
                </p>
            </div>

            <a href="{{ route('competition.landing') }}" class="inline-block bg-orange-500 text-white px-8 py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                العودة للرئيسية
            </a>
        </div>
    </div>
</body>
</html>
