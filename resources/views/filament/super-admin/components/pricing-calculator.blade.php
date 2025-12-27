<div class="p-4 rounded-lg bg-info-50 dark:bg-info-900/20">
    <h4 class="font-bold text-info-700 dark:text-info-300 mb-3">اقتراحات التسعير</h4>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-gray-600 dark:text-gray-400">السعر السنوي المقترح (10 أشهر):</span>
            <span class="font-bold text-gray-900 dark:text-white">ر.س {{ number_format($suggestedYearly, 0) }}</span>
        </div>
        <div>
            <span class="text-gray-600 dark:text-gray-400">سعر الدولار الشهري المقترح:</span>
            <span class="font-bold text-gray-900 dark:text-white">$ {{ number_format($suggestedUsdMonthly, 2) }}</span>
        </div>
        <div>
            <span class="text-gray-600 dark:text-gray-400">سعر الدولار السنوي المقترح:</span>
            <span class="font-bold text-gray-900 dark:text-white">$ {{ number_format($suggestedUsdYearly, 2) }}</span>
        </div>
        @if($yearlySar > 0 && $monthlySar > 0)
            @php
                $discount = round((($monthlySar * 12 - $yearlySar) / ($monthlySar * 12)) * 100);
            @endphp
            <div>
                <span class="text-gray-600 dark:text-gray-400">نسبة الخصم السنوي:</span>
                <span class="font-bold {{ $discount >= 15 ? 'text-success-600' : 'text-warning-600' }}">{{ $discount }}%</span>
            </div>
        @endif
    </div>

    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        * سعر الصرف المستخدم: 1 USD = 3.75 SAR
    </p>
</div>
