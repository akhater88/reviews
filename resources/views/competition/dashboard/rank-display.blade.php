<div class="bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl p-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-orange-100 text-sm">ترتيبك الحالي</p>
            <div class="flex items-baseline gap-2 mt-1">
                @if($rank)
                    <span class="text-5xl font-bold">#{{ $rank }}</span>
                    <span class="text-orange-100">من {{ $totalBranches }}</span>
                @else
                    <span class="text-2xl font-bold">لم يُحدد بعد</span>
                @endif
            </div>
        </div>

        <div class="text-6xl">
            @if($rank === 1)
                🥇
            @elseif($rank === 2)
                🥈
            @elseif($rank === 3)
                🥉
            @elseif($rank && $rank <= 10)
                🏆
            @else
                🎯
            @endif
        </div>
    </div>

    @if($rank && $totalBranches)
        @php
            $percentile = round((1 - ($rank / $totalBranches)) * 100);
        @endphp
        <div class="mt-4 pt-4 border-t border-orange-400/30">
            <div class="flex items-center justify-between text-sm">
                <span>أنت أفضل من</span>
                <span class="font-bold text-lg">{{ $percentile }}%</span>
            </div>
            <div class="mt-2 h-2 bg-orange-400/30 rounded-full overflow-hidden">
                <div
                    class="h-full bg-white rounded-full transition-all duration-500"
                    style="width: {{ $percentile }}%"
                ></div>
            </div>
        </div>
    @endif

    @if($rank && $rank <= 3)
        <div class="mt-4 bg-white/10 rounded-lg p-3 text-center">
            <p class="text-sm">أنت في المراكز الأولى! استمر!</p>
        </div>
    @elseif($rank && $rank <= 10)
        <div class="mt-4 bg-white/10 rounded-lg p-3 text-center">
            <p class="text-sm">أداء رائع! فرصتك للفوز قوية</p>
        </div>
    @endif
</div>
