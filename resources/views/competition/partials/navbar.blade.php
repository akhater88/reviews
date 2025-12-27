<nav class="absolute top-0 left-0 right-0 z-50 py-4">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('competition.landing') }}" class="flex items-center gap-2">
                <span class="text-2xl font-bold text-white">TABsense</span>
                <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">مسابقة</span>
            </a>

            <!-- Navigation Links (Desktop) -->
            <div class="hidden md:flex items-center gap-6 text-white/80">
                <a href="#how-it-works" class="hover:text-white transition-colors">كيف تشارك؟</a>
                <a href="#scoring" class="hover:text-white transition-colors">التقييم</a>
                <a href="#prizes" class="hover:text-white transition-colors">الجوائز</a>
                <a href="#faq" class="hover:text-white transition-colors">أسئلة شائعة</a>
            </div>

            <!-- CTA Button -->
            <button
                @click="openNominationModal()"
                class="bg-white text-orange-600 px-6 py-2 rounded-full font-bold hover:shadow-lg transition-shadow"
            >
                شارك الآن
            </button>
        </div>
    </div>
</nav>
