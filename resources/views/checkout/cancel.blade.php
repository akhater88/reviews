<x-layouts.checkout>
    <x-slot:title>تم إلغاء الدفع</x-slot:title>

    <div class="max-w-2xl mx-auto py-16 px-4 text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold mb-2">تم إلغاء الدفع</h1>
            <p class="text-gray-600 mb-6">لم يتم إتمام عملية الدفع. يمكنك المحاولة مرة أخرى.</p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-right">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">رقم الفاتورة</span>
                    <span class="font-medium">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">المبلغ</span>
                    <span class="font-medium">
                        {{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }}
                        {{ number_format($invoice->total_amount, 2) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('checkout.show', $invoice) }}" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    المحاولة مرة أخرى
                </a>
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    العودة للوحة التحكم
                </a>
            </div>
        </div>
    </div>
</x-layouts.checkout>
