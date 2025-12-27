<x-layouts.checkout>
    <x-slot:title>إتمام الدفع</x-slot:title>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.14.0/moyasar.css">
    @endpush

    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-primary-600 px-6 py-4">
                <h1 class="text-xl font-bold text-white">إتمام الدفع</h1>
            </div>

            <div class="p-6">
                <!-- Invoice Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">رقم الفاتورة</span>
                        <span class="font-medium">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>المبلغ</span>
                        <span class="text-primary-600">
                            {{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }}
                            {{ number_format($invoice->total_amount, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Moyasar Payment Form -->
                <div class="moyasar-form"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.moyasar.com/mpf/1.14.0/moyasar.js"></script>
        <script>
            Moyasar.init({
                element: '{{ $form_config['element'] }}',
                amount: {{ $form_config['amount'] }},
                currency: '{{ $form_config['currency'] }}',
                description: '{{ $form_config['description'] }}',
                publishable_api_key: '{{ $form_config['publishable_api_key'] }}',
                callback_url: '{{ $form_config['callback_url'] }}',
                metadata: @json($form_config['metadata']),
                methods: @json($form_config['methods']),
                language: 'ar',
                on_completed: function(payment) {
                    console.log('Payment completed:', payment);
                },
                on_failure: function(error) {
                    console.error('Payment failed:', error);
                }
            });
        </script>
    @endpush
</x-layouts.checkout>
