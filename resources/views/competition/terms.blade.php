<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الشروط والأحكام | مسابقة TABsense</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-12 max-w-3xl">
        <a href="{{ route('competition.landing') }}" class="text-orange-600 hover:text-orange-700 mb-8 inline-block">
            &#8594; العودة للمسابقة
        </a>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">الشروط والأحكام</h1>

        <div class="bg-white rounded-xl shadow-sm p-8 prose prose-lg max-w-none">
            <h2 class="text-xl font-bold text-gray-900 mt-0">1. الأهلية للمشاركة</h2>
            <ul class="text-gray-600 space-y-2">
                <li>يجب أن يكون عمر المشارك 18 عاما أو أكثر</li>
                <li>يجب أن يكون المشارك مقيما في المملكة العربية السعودية</li>
                <li>يحق لكل رقم جوال المشاركة مرة واحدة فقط في كل فترة مسابقة</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">2. آلية المسابقة</h2>
            <ul class="text-gray-600 space-y-2">
                <li>يتم تحديد الفائز بناء على تحليل تقييمات جوجل للمطاعم المرشحة</li>
                <li>التصويت لا يؤثر على ترتيب المطعم</li>
                <li>يتم اختيار الفائزين عشوائيا من بين من رشّحوا المطعم الفائز</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">3. الجوائز</h2>
            <ul class="text-gray-600 space-y-2">
                <li>الجوائز نقدية ويتم تحويلها للحساب البنكي أو المحفظة الإلكترونية</li>
                <li>يجب استلام الجائزة خلال 30 يوما من الإعلان</li>
                <li>لا يمكن استبدال الجائزة النقدية بجوائز أخرى</li>
            </ul>

            <h2 class="text-xl font-bold text-gray-900 mt-8">4. أحكام عامة</h2>
            <ul class="text-gray-600 space-y-2">
                <li>تحتفظ TABsense بالحق في تعديل أو إلغاء المسابقة في أي وقت</li>
                <li>قرارات TABsense نهائية وغير قابلة للطعن</li>
                <li>يحق لنا استبعاد أي مشارك يخالف الشروط</li>
            </ul>
        </div>
    </div>
</body>
</html>
