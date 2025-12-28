<div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-lg font-bold mb-1">ادعُ أصدقاءك</h3>
            <p class="text-purple-100 text-sm">شارك رابط الدعوة واكسب نقاط إضافية!</p>
        </div>
        <div class="text-4xl">👥</div>
    </div>

    <!-- Referral Stats -->
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div class="bg-white/10 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold">{{ $referralStats['total_referrals'] }}</div>
            <div class="text-purple-100 text-sm">دعوة ناجحة</div>
        </div>
        <div class="bg-white/10 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold">{{ $referralStats['total_referrals'] * 5 }}</div>
            <div class="text-purple-100 text-sm">نقاط مكتسبة</div>
        </div>
    </div>

    <!-- Referral Link -->
    <div class="mt-4" x-data="{ copied: false }">
        <label class="text-purple-100 text-sm mb-2 block">رابط الدعوة الخاص بك:</label>
        <div class="flex gap-2">
            <input
                type="text"
                value="{{ $referralStats['referral_link'] }}"
                readonly
                class="flex-1 bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white text-sm focus:outline-none"
                dir="ltr"
            >
            <button
                @click="navigator.clipboard.writeText('{{ $referralStats['referral_link'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                class="bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition-colors"
            >
                <span x-show="!copied">نسخ</span>
                <span x-show="copied" x-cloak>&#10003;</span>
            </button>
        </div>
    </div>
</div>
