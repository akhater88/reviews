<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Current Subscription --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                الاشتراك الحالي
            </h2>

            @if($this->getCurrentSubscription())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">الباقة</p>
                        <p class="text-xl font-bold text-primary-600 dark:text-primary-400">
                            {{ $this->getCurrentPlan()?->name_ar ?? 'غير محدد' }}
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">الحالة</p>
                        <p class="text-xl font-bold" style="color: {{ match($this->getCurrentSubscription()->status->value) {
                            'active' => '#10b981',
                            'trial' => '#0ea5e9',
                            'grace_period' => '#f59e0b',
                            'expired' => '#ef4444',
                            default => '#6b7280',
                        } }}">
                            {{ $this->getCurrentSubscription()->status->label() }}
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">تاريخ الانتهاء</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $this->getCurrentSubscription()->expires_at?->format('Y-m-d') ?? 'غير محدد' }}
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">الأيام المتبقية</p>
                        <p class="text-xl font-bold {{ $this->getCurrentSubscription()->daysUntilExpiry() <= 7 ? 'text-warning-500' : 'text-success-500' }}">
                            {{ $this->getCurrentSubscription()->daysUntilExpiry() }} يوم
                        </p>
                    </div>
                </div>

                @if($this->getCurrentSubscription()->isOnTrial())
                    <div class="mt-4 p-4 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-800">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-information-circle class="w-6 h-6 text-info-500" />
                            <div>
                                <p class="font-medium text-info-700 dark:text-info-300">أنت في الفترة التجريبية</p>
                                <p class="text-sm text-info-600 dark:text-info-400">
                                    تنتهي في {{ $this->getCurrentSubscription()->trial_ends_at?->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($this->getCurrentSubscription()->isExpiringSoon())
                    <div class="mt-4 p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning-500" />
                            <div>
                                <p class="font-medium text-warning-700 dark:text-warning-300">اشتراكك ينتهي قريباً</p>
                                <p class="text-sm text-warning-600 dark:text-warning-400">
                                    يرجى التجديد قبل {{ $this->getCurrentSubscription()->expires_at?->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                    <x-heroicon-o-credit-card class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                    <p class="text-gray-500 dark:text-gray-400">لا يوجد اشتراك نشط</p>
                </div>
            @endif
        </div>

        {{-- Usage Summary --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                الاستخدام الشهري
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php $usage = $this->getUsageSummary(); @endphp

                {{-- AI Replies --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">ردود AI</span>
                        <span class="text-sm font-medium">
                            @if($usage['ai_replies']['unlimited'])
                                {{ $usage['ai_replies']['used'] }} / &infin;
                            @else
                                {{ $usage['ai_replies']['used'] }} / {{ $usage['ai_replies']['limit'] }}
                            @endif
                        </span>
                    </div>
                    @unless($usage['ai_replies']['unlimited'])
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $usage['ai_replies']['percentage'] >= 80 ? 'bg-warning-500' : 'bg-primary-500' }}"
                                 style="width: {{ min(100, $usage['ai_replies']['percentage']) }}%"></div>
                        </div>
                    @endunless
                </div>

                {{-- Branches --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">الفروع</span>
                        <span class="text-sm font-medium">
                            @if($usage['branches']['unlimited'])
                                {{ $usage['branches']['used'] }} / &infin;
                            @else
                                {{ $usage['branches']['used'] }} / {{ $usage['branches']['limit'] }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Competitors --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">المنافسين</span>
                        <span class="text-sm font-medium">
                            @if($usage['competitors']['unlimited'])
                                {{ $usage['competitors']['used'] }} / &infin;
                            @else
                                {{ $usage['competitors']['used'] }} / {{ $usage['competitors']['limit'] }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Users --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">المستخدمين</span>
                        <span class="text-sm font-medium">
                            @if($usage['users']['unlimited'])
                                {{ $usage['users']['used'] }} / &infin;
                            @else
                                {{ $usage['users']['used'] }} / {{ $usage['users']['limit'] }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Reviews Synced --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">المراجعات المتزامنة</span>
                        <span class="text-sm font-medium">
                            @if($usage['reviews_synced']['unlimited'])
                                {{ $usage['reviews_synced']['used'] }} / &infin;
                            @else
                                {{ $usage['reviews_synced']['used'] }} / {{ $usage['reviews_synced']['limit'] }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Available Plans --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                الباقات المتاحة
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($this->getAvailablePlans() as $plan)
                    <div class="relative p-6 rounded-xl border-2 transition-all {{ $this->getCurrentPlan()?->id === $plan->id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }}">
                        @if($plan->is_popular)
                            <div class="absolute -top-3 right-4 px-3 py-1 bg-warning-500 text-white text-xs font-bold rounded-full">
                                الأكثر شعبية
                            </div>
                        @endif

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $plan->name_ar }}
                        </h3>

                        <div class="mt-2">
                            @if($plan->is_free)
                                <span class="text-3xl font-bold text-success-500">مجاني</span>
                            @else
                                <span class="text-3xl font-bold text-gray-900 dark:text-white">
                                    ر.س {{ number_format($plan->price_monthly_sar, 0) }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">/شهر</span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $plan->description_ar }}
                        </p>

                        <ul class="mt-4 space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <x-heroicon-o-check class="w-4 h-4 text-success-500" />
                                {{ $plan->limits?->max_branches == -1 ? 'فروع غير محدودة' : $plan->limits?->max_branches . ' فروع' }}
                            </li>
                            <li class="flex items-center gap-2">
                                <x-heroicon-o-check class="w-4 h-4 text-success-500" />
                                {{ $plan->limits?->max_ai_replies == -1 ? 'ردود AI غير محدودة' : $plan->limits?->max_ai_replies . ' رد AI/شهر' }}
                            </li>
                        </ul>

                        @if($this->getCurrentPlan()?->id === $plan->id)
                            <button class="mt-4 w-full py-2 px-4 bg-primary-500 text-white rounded-lg cursor-not-allowed opacity-75" disabled>
                                باقتك الحالية
                            </button>
                        @else
                            <button class="mt-4 w-full py-2 px-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition">
                                {{ $plan->sort_order > ($this->getCurrentPlan()?->sort_order ?? 0) ? 'ترقية' : 'تغيير' }}
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Features --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                الميزات المتاحة لك
            </h2>

            <div class="flex flex-wrap gap-2">
                @foreach($this->getAvailableFeatures() as $featureKey)
                    @php $feature = $this->getFeatureByKey($featureKey); @endphp
                    @if($feature)
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400 rounded-full text-sm">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            {{ $feature->name_ar }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
