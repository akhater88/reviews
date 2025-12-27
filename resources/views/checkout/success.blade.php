<x-layouts.checkout>
    <x-slot:title>تم الدفع بنجاح</x-slot:title>

    <div class="max-w-2xl mx-auto py-16 px-4 text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="w-20 h-20 mx-auto bg-success-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold mb-2">تم الدفع بنجاح!</h1>
            <p class="text-gray-600 mb-6">شكراً لك! تم استلام دفعتك وتفعيل اشتراكك.</p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-right">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">رقم الفاتورة</span>
                    <span class="font-medium">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">المبلغ</span>
                    <span class="font-medium">
                        {{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }}
                        {{ number_format($invoice->total_amount, 2) }}
                    </span>
                </div>
                @if($invoice->isPaid())
                    <div class="flex justify-between">
                        <span class="text-gray-500">تاريخ الدفع</span>
                        <span class="font-medium">{{ $invoice->paid_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @endif
            </div>

            @if($subscription)
                <div class="bg-primary-50 rounded-lg p-4 mb-6 text-right">
                    <h3 class="font-semibold text-primary-700 mb-2">معلومات الاشتراك</h3>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">الباقة</span>
                        <span>{{ $subscription->plan->name_ar ?? $subscription->plan->name }}</span>
                    </div>
                    @if($subscription->expires_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">تاريخ الانتهاء</span>
                            <span>{{ $subscription->expires_at->format('Y-m-d') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <a href="{{ route('filament.admin.pages.dashboard') }}" class="inline-block px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                الذهاب للوحة التحكم
            </a>
        </div>
    </div>
</x-layouts.checkout>
