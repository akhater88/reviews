<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استلام الجائزة - مسابقة أفضل مطعم</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-lg mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-2xl font-bold text-gray-900">استلام الجائزة</h1>
            <p class="text-gray-500 mt-2">مبروك! أدخل بياناتك البنكية لتحويل الجائزة</p>
        </div>

        <!-- Prize Info -->
        <div class="bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl p-6 text-white mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">قيمة الجائزة</p>
                    <p class="text-3xl font-bold">{{ number_format($winner->prize_amount) }} ر.س</p>
                </div>
                <div class="text-5xl">💰</div>
            </div>
            @if($winner->competitionBranch)
                <div class="mt-4 pt-4 border-t border-orange-400/30">
                    <p class="text-orange-100 text-sm">المطعم</p>
                    <p class="font-bold">{{ $winner->competitionBranch->name }}</p>
                </div>
            @endif
        </div>

        <!-- Countdown -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏰</span>
                <div>
                    <p class="text-amber-800 font-medium">متبقي {{ $winner->days_to_claim }} يوم للاستلام</p>
                    <p class="text-amber-600 text-sm">يرجى إكمال البيانات قبل انتهاء المهلة</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('competition.claim.submit', ['code' => $winner->claim_code]) }}" method="POST" class="bg-white rounded-2xl shadow-sm p-6">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">اسم البنك *</label>
                <select name="bank_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <option value="">اختر البنك</option>
                    <option value="الراجحي" {{ old('bank_name') == 'الراجحي' ? 'selected' : '' }}>مصرف الراجحي</option>
                    <option value="الأهلي" {{ old('bank_name') == 'الأهلي' ? 'selected' : '' }}>البنك الأهلي السعودي</option>
                    <option value="الرياض" {{ old('bank_name') == 'الرياض' ? 'selected' : '' }}>بنك الرياض</option>
                    <option value="سامبا" {{ old('bank_name') == 'سامبا' ? 'selected' : '' }}>بنك سامبا</option>
                    <option value="الإنماء" {{ old('bank_name') == 'الإنماء' ? 'selected' : '' }}>مصرف الإنماء</option>
                    <option value="البلاد" {{ old('bank_name') == 'البلاد' ? 'selected' : '' }}>بنك البلاد</option>
                    <option value="الجزيرة" {{ old('bank_name') == 'الجزيرة' ? 'selected' : '' }}>بنك الجزيرة</option>
                    <option value="العربي" {{ old('bank_name') == 'العربي' ? 'selected' : '' }}>البنك العربي الوطني</option>
                    <option value="السعودي الفرنسي" {{ old('bank_name') == 'السعودي الفرنسي' ? 'selected' : '' }}>البنك السعودي الفرنسي</option>
                    <option value="ساب" {{ old('bank_name') == 'ساب' ? 'selected' : '' }}>بنك ساب</option>
                    <option value="other" {{ old('bank_name') == 'other' ? 'selected' : '' }}>بنك آخر</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">رقم الآيبان (IBAN) *</label>
                <input
                    type="text"
                    name="iban"
                    value="{{ old('iban') }}"
                    placeholder="SA0000000000000000000000"
                    maxlength="24"
                    pattern="SA\d{22}"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono"
                    dir="ltr"
                >
                <p class="text-gray-400 text-sm mt-1">مثال: SA0380000000608010167519</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">تأكيد رقم الآيبان *</label>
                <input
                    type="text"
                    name="confirm_iban"
                    placeholder="أعد إدخال رقم الآيبان"
                    maxlength="24"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none font-mono"
                    dir="ltr"
                >
            </div>

            <div class="mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="accept_terms" required class="w-5 h-5 text-orange-500 rounded mt-0.5">
                    <span class="text-gray-700 text-sm">
                        أقر بأن البيانات المدخلة صحيحة وأتحمل مسؤولية أي خطأ في رقم الآيبان
                    </span>
                </label>
            </div>

            <button type="submit" class="w-full bg-orange-500 text-white py-4 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                تأكيد واستلام الجائزة
            </button>
        </form>

        <!-- Security Note -->
        <div class="mt-6 text-center text-gray-400 text-sm">
            <p>🔒 بياناتك محمية ومشفرة</p>
            <p class="mt-1">سيتم التحويل خلال 3-5 أيام عمل</p>
        </div>
    </div>
</body>
</html>
