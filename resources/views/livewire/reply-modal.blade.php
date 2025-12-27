<div>
    @if($showModal && $review)
        {{-- Backdrop - Darker for better contrast --}}
        <div
            class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm transition-opacity"
            wire:click="close"
        ></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" dir="rtl">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl ring-1 ring-black/10 dark:ring-white/10">

                    {{-- Colored Header Bar --}}
                    <div class="bg-gradient-to-l from-primary-600 to-primary-700 dark:from-primary-700 dark:to-primary-800 px-6 py-4 rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white">
                                        الرد على المراجعة
                                    </h3>
                                    <p class="text-sm text-primary-100">
                                        {{ $review->branch?->name }}
                                    </p>
                                </div>
                            </div>
                            <button
                                wire:click="close"
                                class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                            >
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Review Info Card - Distinct background --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b-2 border-slate-200 dark:border-slate-700">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <div class="w-14 h-14 bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-600 dark:to-slate-700 rounded-full flex items-center justify-center flex-shrink-0 ring-2 ring-white dark:ring-slate-800 shadow">
                                @if($review->reviewer_photo_url)
                                    <img src="{{ $review->reviewer_photo_url }}" alt="" class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <span class="text-xl font-bold text-slate-600 dark:text-slate-300">
                                        {{ mb_substr($review->reviewer_name ?? 'ع', 0, 1) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Review Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <span class="font-bold text-slate-900 dark:text-white text-base">
                                        {{ $review->reviewer_name ?? 'عميل' }}
                                    </span>

                                    {{-- Rating Stars --}}
                                    <div class="flex bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-lg">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" />
                                        @endfor
                                    </div>

                                    {{-- Sentiment Badge --}}
                                    @if($review->sentiment)
                                        @php
                                            $sentimentConfig = match($review->sentiment) {
                                                'positive' => ['label' => 'إيجابي', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800'],
                                                'negative' => ['label' => 'سلبي', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-800'],
                                                default => ['label' => 'محايد', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-600'],
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $sentimentConfig['class'] }}">
                                            {{ $sentimentConfig['label'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="bg-white dark:bg-slate-900/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <p class="text-slate-700 dark:text-slate-200 text-sm leading-relaxed">
                                        {{ $review->text ?: 'تقييم بالنجوم فقط - لا يوجد نص' }}
                                    </p>
                                </div>

                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-2">
                                    <x-heroicon-o-calendar class="w-3.5 h-3.5" />
                                    {{ $review->review_date?->format('Y-m-d') }}
                                    @if($review->google_review_id)
                                        <span class="mx-1">•</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                            </svg>
                                            Google
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tone Selector --}}
                    <div class="px-6 py-5 bg-white dark:bg-gray-900 border-b border-slate-200 dark:border-slate-700">
                        <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">
                            اختر نبرة الرد
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($tones as $toneOption)
                                @php
                                    $isSelected = $tone === $toneOption->value;
                                    $colors = match($toneOption->value) {
                                        'professional' => [
                                            'selected' => 'border-blue-500 bg-blue-50 dark:bg-blue-900/40 ring-2 ring-blue-500/30',
                                            'text' => 'text-blue-600 dark:text-blue-400',
                                            'icon_bg' => 'bg-blue-100 dark:bg-blue-800/50'
                                        ],
                                        'friendly' => [
                                            'selected' => 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/40 ring-2 ring-emerald-500/30',
                                            'text' => 'text-emerald-600 dark:text-emerald-400',
                                            'icon_bg' => 'bg-emerald-100 dark:bg-emerald-800/50'
                                        ],
                                        'apologetic' => [
                                            'selected' => 'border-amber-500 bg-amber-50 dark:bg-amber-900/40 ring-2 ring-amber-500/30',
                                            'text' => 'text-amber-600 dark:text-amber-400',
                                            'icon_bg' => 'bg-amber-100 dark:bg-amber-800/50'
                                        ],
                                        default => [
                                            'selected' => 'border-slate-500 bg-slate-50 dark:bg-slate-800',
                                            'text' => 'text-slate-600 dark:text-slate-400',
                                            'icon_bg' => 'bg-slate-100 dark:bg-slate-700'
                                        ],
                                    };
                                @endphp
                                <button
                                    type="button"
                                    wire:click="setTone('{{ $toneOption->value }}')"
                                    class="relative px-4 py-4 rounded-xl border-2 transition-all duration-200 {{ $isSelected ? $colors['selected'] : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500 bg-white dark:bg-slate-800' }}"
                                >
                                    <div class="text-center">
                                        <div class="w-10 h-10 mx-auto mb-2 rounded-lg flex items-center justify-center {{ $isSelected ? $colors['icon_bg'] : 'bg-slate-100 dark:bg-slate-700' }}">
                                            <x-dynamic-component
                                                :component="$toneOption->icon()"
                                                class="w-5 h-5 {{ $isSelected ? $colors['text'] : 'text-slate-500 dark:text-slate-400' }}"
                                            />
                                        </div>
                                        <span class="block text-sm font-semibold {{ $isSelected ? $colors['text'] : 'text-slate-700 dark:text-slate-300' }}">
                                            {{ $toneOption->label() }}
                                        </span>
                                    </div>
                                    @if($isSelected)
                                        <div class="absolute top-2 left-2">
                                            <x-heroicon-s-check-circle class="w-5 h-5 {{ $colors['text'] }}" />
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reply Text Area --}}
                    <div class="px-6 py-5 bg-white dark:bg-gray-900">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200">
                                نص الرد
                            </label>
                            <button
                                type="button"
                                wire:click="generateReply"
                                wire:loading.attr="disabled"
                                wire:target="generateReply"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-l from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 rounded-lg shadow-md shadow-purple-500/20 disabled:opacity-50 transition-all duration-200"
                            >
                                <x-heroicon-o-sparkles class="w-4 h-4" wire:loading.class="animate-spin" wire:target="generateReply" />
                                <span wire:loading.remove wire:target="generateReply">إنشاء بالذكاء الاصطناعي</span>
                                <span wire:loading wire:target="generateReply">جاري الإنشاء...</span>
                            </button>
                        </div>

                        <textarea
                            wire:model.live="replyText"
                            rows="5"
                            class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 resize-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            placeholder="اكتب ردك هنا أو اضغط على 'إنشاء بالذكاء الاصطناعي'..."
                        ></textarea>

                        <div class="flex items-center justify-between mt-3 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded">{{ mb_strlen($replyText) }} حرف</span>
                            <div class="flex items-center gap-3">
                                @if($reply?->is_ai_generated)
                                    <span class="flex items-center gap-1 text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded">
                                        <x-heroicon-o-sparkles class="w-3 h-3" />
                                        {{ $reply->ai_provider ?? 'AI' }}
                                    </span>
                                @endif
                                @if($replyText)
                                    <button
                                        type="button"
                                        wire:click="copyToClipboard"
                                        class="flex items-center gap-1 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 px-2 py-1 rounded transition-colors"
                                    >
                                        <x-heroicon-o-clipboard class="w-3 h-3" />
                                        نسخ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status & Messages --}}
                    <div class="px-6 pb-4 bg-white dark:bg-gray-900">
                        {{-- Error Message --}}
                        @if($error)
                            <div class="p-4 mb-4 bg-rose-50 dark:bg-rose-900/30 rounded-xl border-2 border-rose-200 dark:border-rose-800">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-rose-100 dark:bg-rose-800/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                    </div>
                                    <p class="text-sm font-medium text-rose-700 dark:text-rose-300 pt-1">{{ $error }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Success Message --}}
                        @if($success)
                            <div class="p-4 mb-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl border-2 border-emerald-200 dark:border-emerald-800">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-800/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300 pt-1">{{ $success }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Reply Status Badge --}}
                        @if($reply)
                            <div class="flex items-center gap-3 mb-4">
                                @php
                                    $statusValue = $reply->status?->value ?? 'draft';
                                    $statusConfig = match($statusValue) {
                                        'published' => ['icon' => 'heroicon-o-check-circle', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 ring-1 ring-emerald-300 dark:ring-emerald-700', 'label' => 'تم النشر'],
                                        'failed' => ['icon' => 'heroicon-o-x-circle', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 ring-1 ring-rose-300 dark:ring-rose-700', 'label' => 'فشل النشر'],
                                        'publishing' => ['icon' => 'heroicon-o-clock', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 ring-1 ring-amber-300 dark:ring-amber-700', 'label' => 'جاري النشر'],
                                        default => ['icon' => 'heroicon-o-document', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 ring-1 ring-slate-300 dark:ring-slate-600', 'label' => 'مسودة'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold {{ $statusConfig['class'] }}">
                                    <x-dynamic-component :component="$statusConfig['icon']" class="w-4 h-4" />
                                    {{ $statusConfig['label'] }}
                                </span>

                                @if($reply->published_at)
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        نُشر {{ $reply->published_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Google Connection Status --}}
                        @if(!$canPublishToGoogle)
                            <div class="p-4 mb-4 bg-amber-50 dark:bg-amber-900/30 rounded-xl border-2 border-amber-200 dark:border-amber-800">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-800/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                                            {{ $connectionStatus ?? 'لا يمكن النشر مباشرة على Google' }}
                                        </p>
                                        @if(empty($review->google_review_id))
                                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">هذه المراجعة ليست من Google</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions Footer --}}
                    <div class="px-6 py-4 bg-slate-100 dark:bg-slate-800 rounded-b-2xl border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="close"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors"
                        >
                            إغلاق
                        </button>

                        <div class="flex items-center gap-3">
                            {{-- Save Draft --}}
                            <button
                                type="button"
                                wire:click="saveDraft"
                                wire:loading.attr="disabled"
                                wire:target="saveDraft"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 hover:border-slate-400 dark:hover:border-slate-500 disabled:opacity-50 transition-all shadow-sm"
                            >
                                <x-heroicon-o-document class="w-4 h-4" wire:loading.class="animate-pulse" wire:target="saveDraft" />
                                <span wire:loading.remove wire:target="saveDraft">حفظ كمسودة</span>
                                <span wire:loading wire:target="saveDraft">جاري الحفظ...</span>
                            </button>

                            {{-- Publish to Google --}}
                            <button
                                type="button"
                                wire:click="publishToGoogle"
                                wire:loading.attr="disabled"
                                wire:target="publishToGoogle"
                                @if(!$canPublishToGoogle || empty(trim($replyText))) disabled @endif
                                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-l from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 rounded-lg shadow-lg shadow-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all"
                            >
                                <x-heroicon-o-paper-airplane class="w-4 h-4" wire:loading.class="animate-bounce" wire:target="publishToGoogle" />
                                <span wire:loading.remove wire:target="publishToGoogle">نشر على Google</span>
                                <span wire:loading wire:target="publishToGoogle">جاري النشر...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- Copy to Clipboard Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('copyToClipboard', ({ text }) => {
                navigator.clipboard.writeText(text);
            });
        });
    </script>
</div>
