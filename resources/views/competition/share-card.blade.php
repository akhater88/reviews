<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شارك مشاركتك - مسابقة أفضل مطعم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Share Card Preview -->
        <div class="bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl p-6 text-white shadow-xl">
            <div class="text-center mb-4">
                <span class="bg-white/20 px-4 py-1 rounded-full text-sm font-medium">مسابقة أفضل مطعم</span>
            </div>

            <h2 class="text-2xl font-bold text-center mb-6">رشّحت مطعمي المفضل!</h2>

            <div class="bg-white rounded-xl p-4 text-gray-900">
                <div class="flex items-center gap-4">
                    @if($nomination->competitionBranch->photo_url)
                        <img
                            src="{{ $nomination->competitionBranch->photo_url }}"
                            alt="{{ $nomination->competitionBranch->name }}"
                            class="w-16 h-16 rounded-lg object-cover"
                        >
                    @else
                        <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center text-2xl">🍽️</div>
                    @endif

                    <div>
                        <h3 class="font-bold text-lg">{{ $nomination->competitionBranch->name }}</h3>
                        <p class="text-gray-500 text-sm">{{ $nomination->competitionBranch->city }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t">
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-900">
                            {{ number_format($nomination->competitionBranch->google_rating, 1) }}
                        </div>
                        <div class="text-gray-500 text-xs">التقييم</div>
                    </div>
                    @if($score && $score->rank_position)
                        <div class="text-center">
                            <div class="text-xl font-bold text-orange-600">#{{ $score->rank_position }}</div>
                            <div class="text-gray-500 text-xs">الترتيب</div>
                        </div>
                    @endif
                    @if($score && $score->competition_score)
                        <div class="text-center">
                            <div class="text-xl font-bold text-gray-900">{{ number_format($score->competition_score, 0) }}</div>
                            <div class="text-gray-500 text-xs">النقاط</div>
                        </div>
                    @endif
                </div>
            </div>

            <p class="text-center text-orange-100 mt-6 text-sm">
                شارك أنت أيضاً واربح جوائز نقدية!
            </p>
        </div>

        <!-- Share Buttons -->
        <div class="mt-6 space-y-3" x-data="shareActions()">
            <button
                @click="shareWhatsApp()"
                class="w-full flex items-center justify-center gap-3 bg-green-500 text-white py-4 rounded-xl font-bold hover:bg-green-600 transition-colors"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                مشاركة عبر واتساب
            </button>

            <button
                @click="shareTwitter()"
                class="w-full flex items-center justify-center gap-3 bg-black text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                مشاركة عبر تويتر
            </button>

            <button
                @click="copyLink()"
                class="w-full flex items-center justify-center gap-3 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold hover:bg-gray-300 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                نسخ الرابط
            </button>

            <a
                href="{{ route('competition.dashboard') }}"
                class="block w-full text-center text-gray-500 py-3 hover:text-gray-700"
            >
                العودة للوحة التحكم
            </a>
        </div>
    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
    function shareActions() {
        return {
            branchName: '{{ $nomination->competitionBranch->name }}',
            shareUrl: '{{ route("competition.landing") }}?ref={{ $participant->referral_code }}',

            shareWhatsApp() {
                const text = `رشّحت "${this.branchName}" في مسابقة أفضل مطعم!\n\nشارك أنت أيضاً واربح جوائز نقدية\n\n`;
                window.open(`https://wa.me/?text=${encodeURIComponent(text + this.shareUrl)}`, '_blank');
            },

            shareTwitter() {
                const text = `رشّحت "${this.branchName}" في مسابقة أفضل مطعم!\n\nشارك أنت أيضاً واربح جوائز نقدية`;
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(this.shareUrl)}`, '_blank');
            },

            async copyLink() {
                try {
                    await navigator.clipboard.writeText(this.shareUrl);
                    alert('تم نسخ الرابط!');
                } catch (e) {
                    console.error('Copy failed:', e);
                }
            }
        }
    }
    </script>
</body>
</html>
