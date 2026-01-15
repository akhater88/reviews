<x-mail::message>
# {{ $user->name }}

@if($isReset)
تم إعادة تعيين كلمة المرور الخاصة بك على منصة **سُمعة**.

## بيانات الدخول الجديدة

<x-mail::panel>
**البريد الإلكتروني:** {{ $user->email }}

**كلمة المرور الجديدة:** {{ $password }}
</x-mail::panel>

@else
هذه تذكير ببيانات الدخول الخاصة بك على منصة **سُمعة**.

<x-mail::panel>
**البريد الإلكتروني:** {{ $user->email }}

@if($password)
**كلمة المرور:** {{ $password }}
@else
كلمة المرور لم تتغير. استخدم كلمة المرور الحالية.
@endif
</x-mail::panel>
@endif

<x-mail::button :url="$loginUrl">
تسجيل الدخول
</x-mail::button>

---

إذا لم تطلب هذا البريد، يرجى التواصل مع الدعم الفني.

مع تحيات,<br>
فريق {{ config('app.name') }}
</x-mail::message>
