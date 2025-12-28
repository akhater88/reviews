<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم استلام الجائزة - مسابقة أفضل مطعم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="text-6xl mb-4 animate-bounce">✅</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">تم استلام طلبك بنجاح!</h1>
            <p class="text-gray-500 mb-6">سيتم تحويل الجائزة إلى حسابك البنكي خلال 3-5 أيام عمل</p>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-center gap-2 text-green-700">
                    <span class="text-2xl">💰</span>
                    <span class="text-xl font-bold">{{ number_format($winner->prize_amount) }} ر.س</span>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-right">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">البنك:</span>
                    <span class="font-medium">{{ $winner->bank_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">الآيبان:</span>
                    <span class="font-mono text-sm" dir="ltr">{{ substr($winner->iban, 0, 4) }}****{{ substr($winner->iban, -4) }}</span>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-blue-800 text-sm">
                    📧 سنرسل لك إشعاراً عند إتمام التحويل
                </p>
            </div>

            <a href="{{ route('competition.landing') }}" class="inline-block bg-orange-500 text-white px-8 py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                العودة للرئيسية
            </a>
        </div>
    </div>
</body>
</html>
