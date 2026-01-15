<x-mail::message>
# {{ $subscription->tenant->name }}

نود تذكيرك بأن اشتراكك في **سُمعة** سينتهي قريباً.

## تفاصيل الاشتراك

<x-mail::panel>
**الباقة:** {{ $subscription->plan->name_ar }}

**تاريخ الانتهاء:** {{ $subscription->expires_at->format('Y-m-d') }}

**الأيام المتبقية:** {{ $subscription->daysUntilExpiry() }} يوم
</x-mail::panel>

لضمان استمرار خدماتك دون انقطاع، يرجى تجديد اشتراكك قبل تاريخ الانتهاء.

<x-mail::button :url="config('app.url') . '/admin/subscription'">
تجديد الاشتراك
</x-mail::button>

## ماذا يحدث بعد انتهاء الاشتراك؟

- ستستمر في الوصول لمدة **{{ config('subscription.grace_period_days', 3) }} أيام** إضافية (فترة السماح)
- بعد فترة السماح، سيتم تعليق الوصول لجميع الميزات
- ستبقى بياناتك محفوظة ويمكنك استعادتها عند التجديد

---

إذا كان لديك أي استفسار، لا تتردد في التواصل معنا.

مع تحيات,<br>
فريق {{ config('app.name') }}
</x-mail::message>
