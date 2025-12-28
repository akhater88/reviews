<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900">المتصدرون</h3>
        <a href="{{ route('competition.landing') }}#leaderboard" class="text-orange-600 text-sm hover:underline">
            عرض الكل
        </a>
    </div>

    @if(count($leaderboard) > 0)
        <div class="space-y-3">
            @foreach($leaderboard as $index => $entry)
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $rank === $entry['rank'] ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50' }}">
                    <!-- Rank -->
                    <div class="w-8 h-8 flex items-center justify-center font-bold {{ $entry['rank'] <= 3 ? 'text-lg' : 'text-gray-600' }}">
                        @if($entry['rank'] === 1)
                            🥇
                        @elseif($entry['rank'] === 2)
                            🥈
                        @elseif($entry['rank'] === 3)
                            🥉
                        @else
                            {{ $entry['rank'] }}
                        @endif
                    </div>

                    <!-- Photo -->
                    <div class="w-10 h-10 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                        @if($entry['branch']['photo_url'])
                            <img src="{{ $entry['branch']['photo_url'] }}" class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-lg">🍽️</div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 truncate text-sm">{{ $entry['branch']['name'] }}</h4>
                        <p class="text-gray-500 text-xs">{{ $entry['branch']['city'] }}</p>
                    </div>

                    <!-- Score -->
                    <div class="text-left">
                        <div class="font-bold text-orange-600">{{ number_format($entry['score'], 1) }}</div>
                        <div class="text-gray-400 text-xs">نقطة</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($rank && $rank > 5)
            <div class="mt-4 pt-4 border-t text-center">
                <p class="text-gray-500 text-sm">
                    ترتيبك الحالي: <span class="font-bold text-orange-600">#{{ $rank }}</span>
                </p>
            </div>
        @endif
    @else
        <div class="text-center py-8 text-gray-500">
            <div class="text-4xl mb-2">📊</div>
            <p>لا توجد بيانات بعد</p>
        </div>
    @endif
</div>
