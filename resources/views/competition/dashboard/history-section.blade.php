<div class="bg-white rounded-2xl shadow-sm p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">سجل المشاركات</h3>

    <div class="space-y-3">
        @foreach($history as $nomination)
            <div class="flex items-center gap-3 p-3 rounded-xl border {{ $nomination->is_winner ? 'bg-yellow-50 border-yellow-200' : 'border-gray-100' }}">
                <!-- Photo -->
                <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                    @if($nomination->competitionBranch->photo_url)
                        <img src="{{ $nomination->competitionBranch->photo_url }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-xl">🍽️</div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-gray-900 truncate">{{ $nomination->competitionBranch->name }}</h4>
                        @if($nomination->is_winner)
                            <span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">فائز</span>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm">{{ $nomination->period->name_ar }}</p>
                </div>

                <!-- Result -->
                <div class="text-left">
                    @if($nomination->period->status->value === 'completed')
                        @if($nomination->is_winner)
                            <div class="text-green-600 font-bold">+{{ number_format($nomination->prize_amount ?? 0) }} ر.س</div>
                        @else
                            <div class="text-gray-400 text-sm">لم يفز</div>
                        @endif
                    @elseif($nomination->period->status->value === 'active')
                        <div class="text-orange-600 font-bold text-sm">جارية</div>
                    @else
                        <div class="text-gray-400 text-sm">{{ $nomination->period->status->value }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
