<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فائزو {{ $period->name_ar ?? $period->name }} | مسابقة TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-gradient-to-br from-orange-500 to-yellow-500 py-16 text-white text-center">
        <a href="{{ route('competition.winners') }}" class="text-orange-100 hover:text-white mb-4 inline-block">
            &#8594; العودة لقائمة الفائزين
        </a>
        <h1 class="text-4xl font-bold mb-4">&#127942; الفائزون</h1>
        <p class="text-orange-100 text-lg">{{ $period->name_ar ?? $period->name }}</p>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-12">
        @php
            $branchWinners = $period->winners->where('winner_type', 'branch')->sortBy('prize_rank');
            $lotteryWinners = $period->winners->where('winner_type', 'lottery');
            $totalPrizes = $period->winners->sum('prize_amount');
        @endphp

        <!-- Top 3 Branches -->
        @if($branchWinners->count() > 0)
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">&#127869; أفضل المطاعم</h2>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach($branchWinners as $winner)
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden {{ $winner->prize_rank === 1 ? 'ring-2 ring-yellow-400' : '' }}">
                        <!-- Rank Badge -->
                        <div class="bg-gradient-to-r {{ $winner->prize_rank === 1 ? 'from-yellow-400 to-yellow-500' : ($winner->prize_rank === 2 ? 'from-gray-300 to-gray-400' : 'from-amber-600 to-amber-700') }} py-3 text-center text-white">
                            <span class="text-3xl">
                                {{ $winner->prize_rank === 1 ? '🥇' : ($winner->prize_rank === 2 ? '🥈' : '🥉') }}
                            </span>
                            <span class="font-bold mr-2">{{ $winner->rank_label }}</span>
                        </div>

                        <!-- Branch Photo -->
                        <div class="h-40 bg-gray-100">
                            @if($winner->competitionBranch && $winner->competitionBranch->photo_url)
                                <img src="{{ $winner->competitionBranch->photo_url }}" class="w-full h-full object-cover" alt="">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl">🍽️</div>
                            @endif
                        </div>

                        <!-- Branch Info -->
                        <div class="p-4 text-center">
                            <h3 class="font-bold text-lg text-gray-900">{{ $winner->competitionBranch->name ?? 'مطعم' }}</h3>
                            @if($winner->competitionBranch && $winner->competitionBranch->city)
                                <p class="text-gray-500 text-sm">{{ $winner->competitionBranch->city }}</p>
                            @endif

                            <div class="mt-3 flex justify-center gap-4">
                                @if($winner->competition_score)
                                <div>
                                    <span class="text-lg font-bold text-orange-600">{{ number_format($winner->competition_score, 1) }}</span>
                                    <span class="text-gray-400 text-xs block">نقطة</span>
                                </div>
                                @endif
                                <div>
                                    <span class="text-lg font-bold text-green-600">{{ number_format($winner->prize_amount) }}</span>
                                    <span class="text-gray-400 text-xs block">ر.س</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Lottery Winners -->
        @if($lotteryWinners->count() > 0)
            <section>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">&#127922; الفائزون بالسحب</h2>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-purple-500 py-4 px-6 text-white">
                        <p class="text-center">
                            💰 كل فائز يحصل على <span class="font-bold">{{ number_format($lotteryWinners->first()->prize_amount ?? 500) }} ر.س</span>
                        </p>
                    </div>

                    <div class="divide-y">
                        @foreach($lotteryWinners as $winner)
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-xl">
                                        🎫
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $winner->participant->name ?? 'مشارك' }}</p>
                                        <p class="text-gray-500 text-sm">{{ $winner->competitionBranch->name ?? '' }}</p>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <span class="text-green-600 font-bold">{{ number_format($winner->prize_amount) }} ر.س</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Legacy Winners (for old periods without winner_type) -->
        @if($branchWinners->count() === 0 && $lotteryWinners->count() === 0 && $period->winners->count() > 0)
        <div class="max-w-4xl mx-auto">
            <!-- Top 3 Winners -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                @foreach($period->winners->take(3) as $winner)
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center {{ $winner->prize_rank === 1 ? 'md:-mt-4 md:mb-4 ring-2 ring-yellow-400' : '' }}">
                    <div class="text-5xl mb-4">
                        @if($winner->prize_rank === 1)
                            🥇
                        @elseif($winner->prize_rank === 2)
                            🥈
                        @else
                            🥉
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ $winner->rank_label }}</h3>
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-yellow-100 rounded-full flex items-center justify-center text-3xl mx-auto my-4">
                        👤
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
                            👤
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

        <!-- Total Prizes -->
        @if($totalPrizes > 0)
        <div class="mt-12 bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-8 text-white text-center">
            <p class="text-gray-400 mb-2">إجمالي الجوائز الموزعة</p>
            <p class="text-4xl font-bold">{{ number_format($totalPrizes) }} ر.س</p>
        </div>
        @endif

        <!-- CTA -->
        <div class="mt-12 text-center">
            <p class="text-gray-500 mb-4">شارك في المسابقة القادمة!</p>
            <a href="{{ route('competition.landing') }}" class="inline-block bg-orange-500 text-white px-8 py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                رشّح مطعمك المفضل 🎉
            </a>
        </div>
    </div>
</body>
</html>
