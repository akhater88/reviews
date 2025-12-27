<div>
    @if($showModal && $review)
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 z-40 bg-gray-900/50 dark:bg-gray-900/80 transition-opacity"
            wire:click="close"
        ></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" dir="rtl">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/50 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    الرد على المراجعة
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $review->branch?->name }}
                                </p>
                            </div>
                        </div>
                        <button
                            wire:click="close"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Review Info Card --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                                @if($review->reviewer_photo_url)
                                    <img src="{{ $review->reviewer_photo_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <span class="text-lg font-medium text-gray-600 dark:text-gray-400">
                                        {{ mb_substr($review->reviewer_name ?? 'ع', 0, 1) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Review Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $review->reviewer_name ?? 'عميل' }}
                                    </span>

                                    {{-- Rating Stars --}}
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" />
                                        @endfor
                                    </div>

                                    {{-- Sentiment Badge --}}
                                    @if($review->sentiment)
                                        @php
                                            $sentimentConfig = match($review->sentiment) {
                                                'positive' => ['label' => 'إيجابي', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400'],
                                                'negative' => ['label' => 'سلبي', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400'],
                                                default => ['label' => 'محايد', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'],
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $sentimentConfig['class'] }}">
                                            {{ $sentimentConfig['label'] }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                    {{ $review->text ?: 'تقييم بالنجوم فقط - لا يوجد نص' }}
                                </p>

                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    {{ $review->review_date?->format('Y-m-d') }}
                                    @if($review->google_review_id)
                                        <span class="mx-1">•</span>
                                        <span class="text-blue-500">من Google</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tone Selector --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            نبرة الرد
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($tones as $toneOption)
                                @php
                                    $isSelected = $tone === $toneOption->value;
                                    $colors = match($toneOption->value) {
                                        'professional' => ['border' => 'border-primary-500', 'bg' => 'bg-primary-50 dark:bg-primary-900/30', 'text' => 'text-primary-600 dark:text-primary-400'],
                                        'friendly' => ['border' => 'border-green-500', 'bg' => 'bg-green-50 dark:bg-green-900/30', 'text' => 'text-green-600 dark:text-green-400'],
                                        'apologetic' => ['border' => 'border-orange-500', 'bg' => 'bg-orange-50 dark:bg-orange-900/30', 'text' => 'text-orange-600 dark:text-orange-400'],
                                        default => ['border' => 'border-gray-500', 'bg' => 'bg-gray-50 dark:bg-gray-900/30', 'text' => 'text-gray-600 dark:text-gray-400'],
                                    };
                                @endphp
                                <button
                                    type="button"
                                    wire:click="setTone('{{ $toneOption->value }}')"
                                    class="relative px-4 py-3 rounded-xl border-2 transition-all {{ $isSelected ? $colors['border'] . ' ' . $colors['bg'] : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}"
                                >
                                    <div class="text-center">
                                        <x-dynamic-component
                                            :component="$toneOption->icon()"
                                            class="w-6 h-6 mx-auto mb-1 {{ $isSelected ? $colors['text'] : 'text-gray-400' }}"
                                        />
                                        <span class="block text-sm font-medium {{ $isSelected ? $colors['text'] : 'text-gray-600 dark:text-gray-400' }}">
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
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                نص الرد
                            </label>
                            <button
                                type="button"
                                wire:click="generateReply"
                                wire:loading.attr="disabled"
                                wire:target="generateReply"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 disabled:opacity-50 transition-colors"
                            >
                                <x-heroicon-o-sparkles class="w-4 h-4" wire:loading.class="animate-spin" wire:target="generateReply" />
                                <span wire:loading.remove wire:target="generateReply">إنشاء بالذكاء الاصطناعي</span>
                                <span wire:loading wire:target="generateReply">جاري الإنشاء...</span>
                            </button>
                        </div>

                        <textarea
                            wire:model.live="replyText"
                            rows="5"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 resize-none transition-colors"
                            placeholder="اكتب ردك هنا أو اضغط على 'إنشاء بالذكاء الاصطناعي'..."
                        ></textarea>

                        <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                            <span>{{ mb_strlen($replyText) }} حرف</span>
                            <div class="flex items-center gap-3">
                                @if($reply?->is_ai_generated)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-sparkles class="w-3 h-3" />
                                        {{ $reply->ai_provider ?? 'AI' }}
                                    </span>
                                @endif
                                @if($replyText)
                                    <button
                                        type="button"
                                        wire:click="copyToClipboard"
                                        class="flex items-center gap-1 hover:text-gray-600 dark:hover:text-gray-300"
                                    >
                                        <x-heroicon-o-clipboard class="w-3 h-3" />
                                        نسخ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status & Messages --}}
                    <div class="px-6">
                        {{-- Error Message --}}
                        @if($error)
                            <div class="p-3 mb-4 bg-red-50 dark:bg-red-900/30 rounded-lg border border-red-200 dark:border-red-700">
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Success Message --}}
                        @if($success)
                            <div class="p-3 mb-4 bg-green-50 dark:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-700">
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                    <p class="text-sm text-green-600 dark:text-green-400">{{ $success }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Reply Status Badge --}}
                        @if($reply)
                            <div class="flex items-center gap-3 mb-4">
                                @php
                                    $statusValue = $reply->status?->value ?? 'draft';
                                    $statusConfig = match($statusValue) {
                                        'published' => ['icon' => 'heroicon-o-check-circle', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400', 'label' => 'تم النشر'],
                                        'failed' => ['icon' => 'heroicon-o-x-circle', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400', 'label' => 'فشل النشر'],
                                        'publishing' => ['icon' => 'heroicon-o-clock', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400', 'label' => 'جاري النشر'],
                                        default => ['icon' => 'heroicon-o-document', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400', 'label' => 'مسودة'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $statusConfig['class'] }}">
                                    <x-dynamic-component :component="$statusConfig['icon']" class="w-4 h-4" />
                                    {{ $statusConfig['label'] }}
                                </span>

                                @if($reply->published_at)
                                    <span class="text-xs text-gray-400">
                                        نُشر {{ $reply->published_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Google Connection Status --}}
                        @if(!$canPublishToGoogle)
                            <div class="p-3 mb-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                <div class="flex items-start gap-2">
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                            {{ $connectionStatus ?? 'لا يمكن النشر مباشرة على Google' }}
                                        </p>
                                        @if(empty($review->google_review_id))
                                            <p class="text-xs text-yellow-500 mt-1">هذه المراجعة ليست من Google</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions Footer --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl flex items-center justify-between">
                        <button
                            type="button"
                            wire:click="close"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
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
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 transition-colors"
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
                                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
