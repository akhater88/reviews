<x-layouts.checkout>
    <x-slot:title>التحويل البنكي</x-slot:title>

    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-info-600 px-6 py-4">
                <h1 class="text-xl font-bold text-white">التحويل البنكي</h1>
            </div>

            <div class="p-6">
                <div class="bg-info-50 border border-info-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-info-600 mt-0.5 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-info-700">{!! nl2br(e($instructions)) !!}</p>
                    </div>
                </div>

                <h2 class="text-lg font-semibold mb-4">معلومات الحساب البنكي</h2>

                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">البنك</span>
                        <span class="font-medium">{{ $bank_details['bank_name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">اسم الحساب</span>
                        <span class="font-medium">{{ $bank_details['account_name'] }}</span>
                    </div>
                    @if($bank_details['account_number'])
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">رقم الحساب</span>
                            <span class="font-medium font-mono">{{ $bank_details['account_number'] }}</span>
                        </div>
                    @endif
                    @if($bank_details['iban'])
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">IBAN</span>
                            <div class="flex items-center">
                                <span class="font-medium font-mono text-sm" id="iban">{{ $bank_details['iban'] }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $bank_details['iban'] }}')" class="mr-2 text-primary-600 hover:text-primary-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    @if($bank_details['swift_code'])
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">SWIFT Code</span>
                            <span class="font-medium font-mono">{{ $bank_details['swift_code'] }}</span>
                        </div>
                    @endif
                    <hr>
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>المبلغ المطلوب</span>
                        <span class="text-primary-600">
                            {{ $bank_details['currency'] === 'SAR' ? 'ر.س' : '$' }}
                            {{ number_format($bank_details['amount'], 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">رقم المرجع</span>
                        <div class="flex items-center">
                            <span class="font-bold text-primary-600 font-mono" id="reference">{{ $bank_details['reference'] }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $bank_details['reference'] }}')" class="mr-2 text-primary-600 hover:text-primary-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-warning-50 border border-warning-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-warning-700 mt-0.5 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-sm text-warning-700">
                            <strong>هام:</strong> يرجى ذكر رقم المرجع (<span class="font-mono font-bold">{{ $bank_details['reference'] }}</span>) في خانة الملاحظات عند إجراء التحويل.
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500 mb-4">سيتم تفعيل اشتراكك فور التحقق من التحويل</p>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="inline-block px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.checkout>
