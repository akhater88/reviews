<div>
    @if($showModal && $review)
        {{-- Backdrop - Very dark for contrast --}}
        <div
            class="fixed inset-0 z-40 transition-opacity"
            style="background-color: rgba(0, 0, 0, 0.80);"
            wire:click="close"
        ></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" dir="rtl">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-2xl overflow-hidden" style="background-color: #ffffff; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 3px solid #2563eb;">

                    {{-- Blue Header Bar --}}
                    <div class="px-6 py-5" style="background: linear-gradient(to left, #1d4ed8, #2563eb);">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: rgba(255,255,255,0.2);">
                                    <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">
                                        الرد على المراجعة
                                    </h3>
                                    <p class="text-sm" style="color: rgba(255,255,255,0.8);">
                                        {{ $review->branch?->name }}
                                    </p>
                                </div>
                            </div>
                            <button
                                wire:click="close"
                                class="p-2 rounded-lg transition-colors"
                                style="color: rgba(255,255,255,0.7);"
                                onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)';this.style.color='#ffffff';"
                                onmouseout="this.style.backgroundColor='transparent';this.style.color='rgba(255,255,255,0.7)';"
                            >
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    {{-- Review Info Card --}}
                    <div class="px-6 py-5" style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #cbd5e1, #94a3b8); box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                @if($review->reviewer_photo_url)
                                    <img src="{{ $review->reviewer_photo_url }}" alt="" class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <span class="text-xl font-bold" style="color: #475569;">
                                        {{ mb_substr($review->reviewer_name ?? 'ع', 0, 1) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Review Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-3 flex-wrap">
                                    <span class="font-bold text-lg" style="color: #1e293b;">
                                        {{ $review->reviewer_name ?? 'عميل' }}
                                    </span>

                                    {{-- Rating Stars --}}
                                    <div class="flex px-3 py-1 rounded-lg" style="background-color: #fef3c7;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-5 h-5 {{ $i <= $review->rating ? 'text-amber-500' : 'text-gray-300' }}" />
                                        @endfor
                                    </div>

                                    {{-- Sentiment Badge --}}
                                    @if($review->sentiment)
                                        @php
                                            $sentimentConfig = match($review->sentiment) {
                                                'positive' => ['label' => 'إيجابي', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac'],
                                                'negative' => ['label' => 'سلبي', 'bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fca5a5'],
                                                default => ['label' => 'محايد', 'bg' => '#f3f4f6', 'color' => '#374151', 'border' => '#d1d5db'],
                                            };
                                        @endphp
                                        <span class="px-3 py-1 text-xs font-bold rounded-full" style="background-color: {{ $sentimentConfig['bg'] }}; color: {{ $sentimentConfig['color'] }}; border: 2px solid {{ $sentimentConfig['border'] }};">
                                            {{ $sentimentConfig['label'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="rounded-xl p-4" style="background-color: #ffffff; border: 2px solid #e2e8f0;">
                                    <p class="text-sm leading-relaxed" style="color: #475569;">
                                        {{ $review->text ?: 'تقييم بالنجوم فقط - لا يوجد نص' }}
                                    </p>
                                </div>

                                <p class="text-xs mt-3 flex items-center gap-2" style="color: #64748b;">
                                    <x-heroicon-o-calendar class="w-4 h-4" />
                                    {{ $review->review_date?->format('Y-m-d') }}
                                    @if($review->google_review_id)
                                        <span class="mx-1">•</span>
                                        <span class="font-semibold flex items-center gap-1" style="color: #2563eb;">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
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
                    <div class="px-6 py-5" style="background-color: #ffffff; border-bottom: 2px solid #e2e8f0;">
                        <label class="block text-sm font-bold mb-4" style="color: #1e293b;">
                            اختر نبرة الرد
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($tones as $toneOption)
                                @php
                                    $isSelected = $tone === $toneOption->value;
                                    $colors = match($toneOption->value) {
                                        'professional' => ['bg' => '#eff6ff', 'border' => '#3b82f6', 'text' => '#1d4ed8', 'icon_bg' => '#dbeafe'],
                                        'friendly' => ['bg' => '#ecfdf5', 'border' => '#10b981', 'text' => '#059669', 'icon_bg' => '#d1fae5'],
                                        'apologetic' => ['bg' => '#fffbeb', 'border' => '#f59e0b', 'text' => '#d97706', 'icon_bg' => '#fef3c7'],
                                        default => ['bg' => '#f8fafc', 'border' => '#94a3b8', 'text' => '#64748b', 'icon_bg' => '#f1f5f9'],
                                    };
                                @endphp
                                <button
                                    type="button"
                                    wire:click="setTone('{{ $toneOption->value }}')"
                                    class="relative px-4 py-5 rounded-xl transition-all duration-200"
                                    style="background-color: {{ $isSelected ? $colors['bg'] : '#ffffff' }}; border: 3px solid {{ $isSelected ? $colors['border'] : '#e2e8f0' }}; {{ $isSelected ? 'box-shadow: 0 4px 14px rgba(0,0,0,0.1);' : '' }}"
                                >
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background-color: {{ $isSelected ? $colors['icon_bg'] : '#f1f5f9' }};">
                                            <x-dynamic-component
                                                :component="$toneOption->icon()"
                                                class="w-6 h-6"
                                                style="color: {{ $isSelected ? $colors['text'] : '#64748b' }};"
                                            />
                                        </div>
                                        <span class="block text-sm font-bold" style="color: {{ $isSelected ? $colors['text'] : '#475569' }};">
                                            {{ $toneOption->label() }}
                                        </span>
                                    </div>
                                    @if($isSelected)
                                        <div class="absolute top-2 left-2">
                                            <x-heroicon-s-check-circle class="w-6 h-6" style="color: {{ $colors['border'] }};" />
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reply Text Area --}}
                    <div class="px-6 py-5" style="background-color: #ffffff;">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-bold" style="color: #1e293b;">
                                نص الرد
                            </label>
                            <button
                                type="button"
                                wire:click="generateReply"
                                wire:loading.attr="disabled"
                                wire:target="generateReply"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl disabled:opacity-50 transition-all duration-200"
                                style="background: linear-gradient(to left, #7c3aed, #8b5cf6); box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);"
                            >
                                <x-heroicon-o-sparkles class="w-5 h-5" wire:loading.class="animate-spin" wire:target="generateReply" />
                                <span wire:loading.remove wire:target="generateReply">إنشاء بالذكاء الاصطناعي</span>
                                <span wire:loading wire:target="generateReply">جاري الإنشاء...</span>
                            </button>
                        </div>

                        <textarea
                            wire:model.live="replyText"
                            rows="5"
                            class="w-full rounded-xl resize-none transition-all"
                            style="background-color: #f8fafc; border: 2px solid #e2e8f0; color: #1e293b; padding: 1rem;"
                            placeholder="اكتب ردك هنا أو اضغط على 'إنشاء بالذكاء الاصطناعي'..."
                        ></textarea>

                        <div class="flex items-center justify-between mt-3 text-xs">
                            <span class="px-3 py-1.5 rounded-lg font-medium" style="background-color: #f1f5f9; color: #64748b;">{{ mb_strlen($replyText) }} حرف</span>
                            <div class="flex items-center gap-3">
                                @if($reply?->is_ai_generated)
                                    <span class="flex items-center gap-1 px-3 py-1.5 rounded-lg font-medium" style="background-color: #f3e8ff; color: #7c3aed;">
                                        <x-heroicon-o-sparkles class="w-4 h-4" />
                                        {{ $reply->ai_provider ?? 'AI' }}
                                    </span>
                                @endif
                                @if($replyText)
                                    <button
                                        type="button"
                                        wire:click="copyToClipboard"
                                        class="flex items-center gap-1 px-3 py-1.5 rounded-lg font-medium transition-colors"
                                        style="background-color: #f1f5f9; color: #64748b;"
                                    >
                                        <x-heroicon-o-clipboard class="w-4 h-4" />
                                        نسخ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status & Messages --}}
                    <div class="px-6 pb-4" style="background-color: #ffffff;">
                        {{-- Error Message --}}
                        @if($error)
                            <div class="p-4 mb-4 rounded-xl" style="background-color: #fef2f2; border: 2px solid #fecaca;">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: #fee2e2;">
                                        <x-heroicon-o-exclamation-circle class="w-6 h-6" style="color: #dc2626;" />
                                    </div>
                                    <p class="text-sm font-semibold pt-2" style="color: #991b1b;">{{ $error }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Success Message --}}
                        @if($success)
                            <div class="p-4 mb-4 rounded-xl" style="background-color: #f0fdf4; border: 2px solid #bbf7d0;">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: #dcfce7;">
                                        <x-heroicon-o-check-circle class="w-6 h-6" style="color: #16a34a;" />
                                    </div>
                                    <p class="text-sm font-semibold pt-2" style="color: #166534;">{{ $success }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Reply Status Badge --}}
                        @if($reply)
                            <div class="flex items-center gap-3 mb-4">
                                @php
                                    $statusValue = $reply->status?->value ?? 'draft';
                                    $statusConfig = match($statusValue) {
                                        'published' => ['icon' => 'heroicon-o-check-circle', 'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#86efac', 'label' => 'تم النشر'],
                                        'failed' => ['icon' => 'heroicon-o-x-circle', 'bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fca5a5', 'label' => 'فشل النشر'],
                                        'publishing' => ['icon' => 'heroicon-o-clock', 'bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fcd34d', 'label' => 'جاري النشر'],
                                        default => ['icon' => 'heroicon-o-document', 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'label' => 'مسودة'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold" style="background-color: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['color'] }}; border: 2px solid {{ $statusConfig['border'] }};">
                                    <x-dynamic-component :component="$statusConfig['icon']" class="w-5 h-5" />
                                    {{ $statusConfig['label'] }}
                                </span>

                                @if($reply->published_at)
                                    <span class="text-xs" style="color: #64748b;">
                                        نُشر {{ $reply->published_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Google Connection Status --}}
                        @if(!$canPublishToGoogle)
                            <div class="p-4 mb-4 rounded-xl" style="background-color: #fffbeb; border: 2px solid #fcd34d;">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: #fef3c7;">
                                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" style="color: #d97706;" />
                                    </div>
                                    <div class="pt-1">
                                        <p class="text-sm font-semibold" style="color: #92400e;">
                                            {{ $connectionStatus ?? 'لا يمكن النشر مباشرة على Google' }}
                                        </p>
                                        @if(empty($review->google_review_id))
                                            <p class="text-xs mt-1" style="color: #b45309;">هذه المراجعة ليست من Google</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Actions Footer --}}
                    <div class="px-6 py-5 flex items-center justify-between" style="background-color: #f1f5f9; border-top: 3px solid #e2e8f0;">
                        <button
                            type="button"
                            wire:click="close"
                            class="px-6 py-3 text-sm font-bold rounded-xl transition-colors"
                            style="color: #64748b;"
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
                                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold rounded-xl disabled:opacity-50 transition-all"
                                style="background-color: #ffffff; color: #475569; border: 2px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"
                            >
                                <x-heroicon-o-document class="w-5 h-5" wire:loading.class="animate-pulse" wire:target="saveDraft" />
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
                                class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                style="background: linear-gradient(to left, #1d4ed8, #2563eb); box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);"
                            >
                                <x-heroicon-o-paper-airplane class="w-5 h-5" wire:loading.class="animate-bounce" wire:target="publishToGoogle" />
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
