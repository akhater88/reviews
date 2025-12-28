<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <!-- Branch Header -->
    <div class="relative h-32 bg-gradient-to-br from-orange-400 to-orange-600">
        @if($nomination->competitionBranch->photo_url)
            <img
                src="{{ $nomination->competitionBranch->photo_url }}"
                alt="{{ $nomination->competitionBranch->name }}"
                class="absolute inset-0 w-full h-full object-cover opacity-30"
            >
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>

        <!-- Rank Badge -->
        @if($rank)
            <div class="absolute top-4 left-4 bg-white rounded-full px-3 py-1 shadow-lg">
                <span class="text-sm font-bold text-orange-600">#{{ $rank }}</span>
            </div>
        @endif
    </div>

    <!-- Branch Info -->
    <div class="p-6 -mt-12 relative">
        <div class="flex items-end gap-4">
            <!-- Photo -->
            <div class="w-20 h-20 bg-white rounded-xl shadow-lg overflow-hidden flex-shrink-0 border-4 border-white">
                @if($nomination->competitionBranch->photo_url)
                    <img
                        src="{{ $nomination->competitionBranch->photo_url }}"
                        alt="{{ $nomination->competitionBranch->name }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="w-full h-full flex items-center justify-center bg-orange-100 text-3xl">🍽️</div>
                @endif
            </div>

            <!-- Info -->
            <div class="flex-1 pb-1">
                <h2 class="text-xl font-bold text-gray-900">{{ $nomination->competitionBranch->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $nomination->competitionBranch->city }}</p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">
                    {{ number_format($nomination->competitionBranch->google_rating, 1) }}
                </div>
                <div class="text-gray-500 text-sm">التقييم</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">
                    {{ number_format($nomination->competitionBranch->google_reviews_count) }}
                </div>
                <div class="text-gray-500 text-sm">المراجعات</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">
                    @if($score && $score->competition_score)
                        {{ number_format($score->competition_score, 1) }}
                    @else
                        --
                    @endif
                </div>
                <div class="text-gray-500 text-sm">النقاط</div>
            </div>
        </div>

        <!-- Analysis Status -->
        @if($score)
            <div class="mt-4 flex items-center justify-center gap-2 text-sm">
                @if($score->analysis_status === 'completed')
                    <span class="flex items-center gap-1 text-green-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        تم تحليل البيانات
                    </span>
                @elseif($score->analysis_status === 'analyzing')
                    <span class="flex items-center gap-1 text-blue-600">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        جاري التحليل...
                    </span>
                @else
                    <span class="flex items-center gap-1 text-gray-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        في انتظار التحليل
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>
