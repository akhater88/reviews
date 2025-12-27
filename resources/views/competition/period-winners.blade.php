<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فائزو {{ $period->name_ar ?? $period->name }} | مسابقة TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-12">
        <a href="{{ route('competition.winners') }}" class="text-orange-600 hover:text-orange-700 mb-8 inline-block">
            &#8594; العودة لقائمة الفائزين
        </a>

        <div class="text-center mb-12">
            <div class="text-6xl mb-4">&#127942;</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">فائزو {{ $period->name_ar ?? $period->name }}</h1>

            @if($period->winningBranch)
            <div class="mt-6 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-2xl p-6 max-w-md mx-auto">
                <p class="text-sm opacity-80 mb-2">المطعم الفائز</p>
                <h2 class="text-2xl font-bold">{{ $period->winningBranch->display_name }}</h2>
                @if($period->winningBranch->city)
                <p class="text-sm opacity-80 mt-1">{{ $period->winningBranch->city }}</p>
                @endif
            </div>
            @endif
        </div>

        @if($period->winners->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500">لم يتم الإعلان عن فائزين لهذه الفترة بعد</p>
        </div>
        @else
        <div class="max-w-4xl mx-auto">
            <!-- Top 3 Winners -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                @foreach($period->winners->take(3) as $winner)
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center {{ $winner->prize_rank === 1 ? 'md:-mt-4 md:mb-4 ring-2 ring-yellow-400' : '' }}">
                    <div class="text-5xl mb-4">
                        @if($winner->prize_rank === 1)
                            &#129351;
                        @elseif($winner->prize_rank === 2)
                            &#129352;
                        @else
                            &#129353;
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ $winner->rank_label }}</h3>
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-yellow-100 rounded-full flex items-center justify-center text-3xl mx-auto my-4">
                        &#128100;
                    </div>
                    <p class="font-medium text-gray-800">{{ $winner->participant->name ?? 'مشارك' }}</p>
                    <p class="text-gray-500 text-sm">{{ $winner->participant->city ?? 'السعودية' }}</p>
                    <div class="mt-4 bg-green-100 text-green-800 px-4 py-2 rounded-full inline-block font-bold">
                        {{ $winner->prize_display }}
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Other Winners -->
            @if($period->winners->count() > 3)
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-gray-900 text-lg mb-4 text-center">فائزون آخرون</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($period->winners->skip(3) as $winner)
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center text-2xl mx-auto mb-2">
                            &#128100;
                        </div>
                        <p class="font-medium text-gray-800 text-sm">{{ $winner->participant->name ?? 'مشارك' }}</p>
                        <p class="text-green-600 text-sm font-bold">{{ $winner->prize_display }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- CTA -->
        <div class="text-center mt-12">
            <p class="text-gray-600 mb-4">هل تريد أن تكون الفائز القادم؟</p>
            <a href="{{ route('competition.landing') }}" class="inline-block bg-orange-500 text-white px-8 py-3 rounded-full font-bold hover:bg-orange-600 transition-colors">
                شارك الآن
            </a>
        </div>
    </div>
</body>
</html>
