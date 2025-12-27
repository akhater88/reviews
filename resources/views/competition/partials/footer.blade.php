<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div>
                <h3 class="text-xl font-bold mb-4">TABsense</h3>
                <p class="text-gray-400 text-sm">
                    منصة إدارة تقييمات المطاعم الذكية
                </p>
            </div>

            <!-- Links -->
            <div>
                <h4 class="font-bold mb-4">روابط سريعة</h4>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li><a href="#how-it-works" class="hover:text-white">كيف تشارك؟</a></li>
                    <li><a href="#prizes" class="hover:text-white">الجوائز</a></li>
                    <li><a href="{{ route('competition.winners') }}" class="hover:text-white">الفائزون</a></li>
                    <li><a href="#faq" class="hover:text-white">أسئلة شائعة</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="font-bold mb-4">قانوني</h4>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li><a href="{{ route('competition.terms') }}" class="hover:text-white">الشروط والأحكام</a></li>
                    <li><a href="{{ route('competition.privacy') }}" class="hover:text-white">سياسة الخصوصية</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-bold mb-4">تواصل معنا</h4>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li>&#128231; support@tabsense.com</li>
                    <li>&#128241; +966 XX XXX XXXX</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-10 pt-6 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} TABsense. جميع الحقوق محفوظة.
        </div>
    </div>
</footer>
