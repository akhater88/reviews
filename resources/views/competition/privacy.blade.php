<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية | مسابقة TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-12 max-w-3xl">
        <a href="{{ route('competition.landing') }}" class="text-orange-600 hover:text-orange-700 mb-8 inline-block">
            &#8594; العودة للمسابقة
        </a>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">سياسة الخصوصية</h1>

        <div class="bg-white rounded-xl shadow-sm p-8 prose prose-lg max-w-none">
            <h2 class="text-xl font-bold text-gray-900 mt-0">1. البيانات التي نجمعها</h2>
            <ul class="text-gray-600 space-y-2">
                <li>رقم الجوال (للتحقق والتواصل)</li>
                <li>الاسم (اختياري)</li>
                <li>البريد الإلكتروني (اختياري)</li>
                <li>المطعم المرشح</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">2. كيف نستخدم بياناتك</h2>
            <ul class="text-gray-600 space-y-2">
                <li>التحقق من هويتك</li>
                <li>إشعارك في حال الفوز</li>
                <li>إرسال تحديثات المسابقة (بموافقتك)</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">3. حماية البيانات</h2>
            <ul class="text-gray-600 space-y-2">
                <li>نستخدم تشفير SSL لحماية بياناتك</li>
                <li>لا نشارك بياناتك مع أطراف ثالثة</li>
                <li>يمكنك طلب حذف بياناتك في أي وقت</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">4. حقوقك</h2>
            <ul class="text-gray-600 space-y-2">
                <li>الوصول إلى بياناتك</li>
                <li>تصحيح بياناتك</li>
                <li>حذف بياناتك</li>
                <li>إلغاء الاشتراك من الرسائل</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">5. التواصل</h2>
            <p class="text-gray-600">لأي استفسارات حول الخصوصية: privacy@tabsense.com</p>
        </div>
    </div>
</body>
</html>
