<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفائزون | مسابقة TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-12">
        <a href="{{ route('competition.landing') }}" class="text-orange-600 hover:text-orange-700 mb-8 inline-block">
            &#8594; العودة للمسابقة
        </a>

        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">&#127942; الفائزون السابقون</h1>

        @if($completedPeriods->isEmpty())
        <div class="text-center py-12">
            <div class="text-6xl mb-4">&#127942;</div>
            <p class="text-gray-500">لم يتم الإعلان عن فائزين بعد</p>
            <a href="{{ route('competition.landing') }}" class="mt-4 inline-block bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                شارك الآن
            </a>
        </div>
        @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            @foreach($completedPeriods as $period)
            <a href="{{ route('competition.winners.period', $period) }}" class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-6">
                <div class="text-center">
                    <div class="text-4xl mb-3">&#127942;</div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $period->name_ar ?? $period->name }}</h3>
                    <p class="text-gray-500 text-sm mt-1">{{ $period->winners->count() }} فائز</p>
                    @if($period->winningBranch)
                    <p class="text-orange-600 text-sm mt-2">
                        المطعم الفائز: {{ $period->winningBranch->display_name }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            {{ $completedPeriods->links() }}
        </div>
        @endif
    </div>
</body>
</html>
