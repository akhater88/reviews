<x-mail::message>
# {{ $admin->name }}

تم إنشاء حسابك بنجاح على منصة **سُمعة** لإدارة مراجعات Google.

## بيانات الدخول

<x-mail::panel>
**البريد الإلكتروني:** {{ $admin->email }}

**كلمة المرور:** {{ $password }}
</x-mail::panel>

<x-mail::button :url="$loginUrl">
تسجيل الدخول
</x-mail::button>

## معلومات الاشتراك

- **الشركة:** {{ $tenant->name }}
- **الباقة:** {{ $tenant->currentSubscription?->plan?->name_ar ?? 'تجريبية' }}
- **تاريخ الانتهاء:** {{ $tenant->currentSubscription?->expires_at?->format('Y-m-d') ?? '-' }}

---

**هام:** يرجى تغيير كلمة المرور بعد تسجيل الدخول الأول.

مع تحيات,<br>
فريق {{ config('app.name') }}
</x-mail::message>
