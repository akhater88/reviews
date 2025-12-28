<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة التحكم - مسابقة أفضل مطعم</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Tajawal', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="dashboardApp()">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('competition.landing') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">لوحة التحكم</h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-gray-600 text-sm">{{ $participant->name }}</span>
                    <form action="{{ route('competition.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        @if($nomination)
            <!-- Current Nomination Card -->
            @include('competition.dashboard.nomination-card', ['nomination' => $nomination, 'score' => $score, 'rank' => $rank, 'totalBranches' => $totalBranches])

            <!-- Score Breakdown -->
            @if($score && $score->analysis_status === 'completed')
                @include('competition.dashboard.score-breakdown', ['score' => $score])
            @endif

            <!-- Rank Display -->
            @include('competition.dashboard.rank-display', ['rank' => $rank, 'totalBranches' => $totalBranches, 'score' => $score])

            <!-- Share Card -->
            @include('competition.dashboard.share-card', ['nomination' => $nomination, 'participant' => $participant])

        @else
            <!-- No Nomination CTA -->
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="text-6xl mb-4">🍽️</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">لم تقم بالترشيح بعد</h2>
                <p class="text-gray-500 mb-6">رشّح مطعمك المفضل وادخل السحب على جوائز نقدية!</p>
                <a
                    href="{{ route('competition.landing') }}"
                    class="inline-block bg-orange-500 text-white px-8 py-3 rounded-lg font-bold hover:bg-orange-600 transition-colors"
                >
                    رشّح الآن
                </a>
            </div>
        @endif

        <!-- Leaderboard Preview -->
        @include('competition.dashboard.leaderboard-preview', ['leaderboard' => $leaderboard, 'rank' => $rank])

        <!-- Referral Section -->
        @include('competition.dashboard.referral-section', ['referralStats' => $referralStats])

        <!-- Nomination History -->
        @if($history->count() > 0)
            @include('competition.dashboard.history-section', ['history' => $history])
        @endif

        <!-- Period Info -->
        @if($currentPeriod)
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">الفترة الحالية</p>
                        <h3 class="text-lg font-bold">{{ $currentPeriod->name_ar }}</h3>
                    </div>
                    <div class="text-left">
                        <p class="text-gray-400 text-sm">ينتهي في</p>
                        <p class="font-bold">{{ $currentPeriod->ends_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                <!-- Countdown -->
                <div class="mt-4 pt-4 border-t border-gray-700" x-data="countdown('{{ $currentPeriod->ends_at->toIso8601String() }}')">
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div class="bg-gray-700/50 rounded-lg p-2">
                            <div class="text-2xl font-bold" x-text="days">0</div>
                            <div class="text-xs text-gray-400">يوم</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-lg p-2">
                            <div class="text-2xl font-bold" x-text="hours">0</div>
                            <div class="text-xs text-gray-400">ساعة</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-lg p-2">
                            <div class="text-2xl font-bold" x-text="minutes">0</div>
                            <div class="text-xs text-gray-400">دقيقة</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-lg p-2">
                            <div class="text-2xl font-bold" x-text="seconds">0</div>
                            <div class="text-xs text-gray-400">ثانية</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12 py-6">
        <div class="max-w-4xl mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} TABsense. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
    function dashboardApp() {
        return {
            refreshing: false,

            async refreshScore() {
                this.refreshing = true;
                try {
                    const response = await fetch('{{ route("competition.dashboard.score") }}');
                    const data = await response.json();
                    // Update UI with new data
                    if (data.success) {
                        window.location.reload();
                    }
                } catch (e) {
                    console.error('Refresh failed:', e);
                } finally {
                    this.refreshing = false;
                }
            }
        }
    }

    function countdown(endDate) {
        return {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
            timer: null,

            init() {
                this.updateCountdown();
                this.timer = setInterval(() => this.updateCountdown(), 1000);
            },

            updateCountdown() {
                const end = new Date(endDate).getTime();
                const now = new Date().getTime();
                const diff = end - now;

                if (diff <= 0) {
                    this.days = this.hours = this.minutes = this.seconds = 0;
                    clearInterval(this.timer);
                    return;
                }

                this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
            },

            destroy() {
                if (this.timer) clearInterval(this.timer);
            }
        }
    }
    </script>
</body>
</html>
