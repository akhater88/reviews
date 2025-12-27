<x-mail::message>
# تأكيد الدفع

مرحباً {{ $payment->tenant->name }}،

تم استلام دفعتك بنجاح!

<x-mail::panel>
**رقم الفاتورة:** {{ $payment->invoice->invoice_number }}

**المبلغ:** {{ $payment->currency === 'SAR' ? 'ر.س' : '$' }} {{ number_format($payment->amount, 2) }}

**تاريخ الدفع:** {{ $payment->paid_at->format('Y-m-d H:i') }}

**طريقة الدفع:** {{ $payment->payment_gateway->label() }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/admin'">
الذهاب للوحة التحكم
</x-mail::button>

مع تحيات,<br>
فريق {{ config('app.name') }}
</x-mail::message>
