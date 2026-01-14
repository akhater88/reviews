<div id="cta-banner" class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-l from-blue-600 to-purple-600 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3">
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center justify-center w-8 h-8 bg-white/20 rounded-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <span class="text-sm sm:text-base font-medium">
                    {{ __('app.ctaBannerText', ['default' => 'أعجبك التقرير؟ اشترك الآن للحصول على تحديثات مستمرة وميزات إضافية']) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="/get-started"
                   class="bg-white text-blue-600 px-4 sm:px-6 py-2 rounded-full text-sm font-bold hover:bg-blue-50 transition-colors whitespace-nowrap">
                    اشترك الآن
                </a>
                <button onclick="document.getElementById('cta-banner').style.display='none'"
                        class="p-1 hover:bg-white/20 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
