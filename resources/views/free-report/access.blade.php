<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الوصول لتقريرك - سُمعة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <img src="{{ asset('images/sumaa-logo-primary.svg') }}" alt="سُمعة" class="h-10 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-gray-900">الوصول لتقريرك</h1>
                <p class="text-gray-600 mt-2">أدخل رقم الجوال للحصول على رابط تقريرك</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700 text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700 text-sm">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('free-report.page.request-access') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">رقم الجوال</label>
                    <div class="flex gap-2">
                        <select name="phone_country_code"
                                class="w-28 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                dir="ltr">
                            <option value="+966">+966</option>
                            <option value="+971">+971</option>
                            <option value="+965">+965</option>
                            <option value="+974">+974</option>
                            <option value="+973">+973</option>
                            <option value="+968">+968</option>
                            <option value="+962">+962</option>
                            <option value="+20">+20</option>
                        </select>
                        <input type="tel"
                               name="phone_number"
                               placeholder="5xxxxxxxx"
                               dir="ltr"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                    </div>
                    @error('phone_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    إرسال رابط عبر واتساب
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-gray-500 text-sm mb-4">ليس لديك تقرير؟</p>
                <a href="/get-started"
                   class="text-blue-600 hover:text-blue-700 font-medium">
                    احصل على تقريرك المجاني الآن
                </a>
            </div>
        </div>
    </div>
</body>
</html>
