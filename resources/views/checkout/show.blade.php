<x-layouts.checkout>
    <x-slot:title>إتمام الدفع</x-slot:title>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-primary-600 px-6 py-4">
                <h1 class="text-xl font-bold text-white">إتمام الدفع</h1>
            </div>

            <div class="p-6">
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Invoice Summary -->
                    <div>
                        <h2 class="text-lg font-semibold mb-4">ملخص الفاتورة</h2>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">رقم الفاتورة</span>
                                <span class="font-medium">{{ $invoice->invoice_number }}</span>
                            </div>

                            @if($invoice->items)
                                @foreach($invoice->items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $item['description_ar'] ?? $item['description'] }}</span>
                                        <span>{{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }} {{ number_format($item['total'], 2) }}</span>
                                    </div>
                                @endforeach
                            @endif

                            @if($invoice->tax_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">ضريبة القيمة المضافة (15%)</span>
                                    <span>{{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }} {{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                            @endif

                            <hr>
                            <div class="flex justify-between text-lg font-bold">
                                <span>الإجمالي</span>
                                <span class="text-primary-600">
                                    {{ $invoice->currency === 'SAR' ? 'ر.س' : '$' }}
                                    {{ number_format($invoice->total_amount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div>
                        <h2 class="text-lg font-semibold mb-4">طريقة الدفع</h2>
                        <form action="{{ route('checkout.process', $invoice) }}" method="POST">
                            @csrf
                            <div class="space-y-3">
                                @foreach($gateways as $gateway)
                                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-primary-300 transition-colors {{ $loop->first ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                                        <input type="radio" name="gateway" value="{{ $gateway['identifier'] }}"
                                               {{ $loop->first ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary-600 focus:ring-primary-500"
                                               onchange="this.closest('form').querySelectorAll('label').forEach(l => l.classList.remove('border-primary-500', 'bg-primary-50')); this.closest('label').classList.add('border-primary-500', 'bg-primary-50');">
                                        <div class="mr-3">
                                            <p class="font-medium">{{ $gateway['name_ar'] }}</p>
                                            <p class="text-sm text-gray-500">{{ $gateway['name'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit" class="w-full mt-6 py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
                                متابعة الدفع
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.checkout>
