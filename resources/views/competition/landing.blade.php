<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['hero_subtitle'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings['hero_title'] }} | سُمعة</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/sumaa-favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/sumaa-logo-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/sumaa-apple-touch-icon.png') }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="سُمعة - Sumaa">
    <meta property="og:title" content="{{ $settings['hero_title'] }} | سُمعة">
    <meta property="og:description" content="{{ $settings['hero_subtitle'] }}">
    <meta property="og:image" content="{{ asset('images/sumaa-og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="ar_SA">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $settings['hero_title'] }} | سُمعة">
    <meta name="twitter:description" content="{{ $settings['hero_subtitle'] }}">
    <meta name="twitter:image" content="{{ asset('images/sumaa-og-image.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }

        /* Gradient Background */
        .hero-gradient {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 50%, #FFD700 100%);
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Pulse Animation */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 107, 53, 0.4); }
            50% { box-shadow: 0 0 40px rgba(255, 107, 53, 0.8); }
        }
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        /* Tag Cloud Animation */
        @keyframes tag-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-5px) rotate(1deg); }
            75% { transform: translateY(5px) rotate(-1deg); }
        }
        .tag-float {
            animation: tag-float 4s ease-in-out infinite;
        }

        /* Countdown flip effect */
        .countdown-box {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            min-width: 80px;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased" x-data="competitionApp()">

    <!-- Navigation -->
    @include('competition.partials.navbar')

    <!-- Hero Section -->
    <section class="hero-gradient min-h-screen flex items-center relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-20 left-10 w-48 h-48 bg-yellow-300/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-white/5 rounded-full blur-xl float-animation"></div>
        </div>

        <div class="container mx-auto px-4 py-20 relative z-10">
            <div class="max-w-4xl mx-auto text-center text-white">
                <!-- Trophy Icon -->
                <div class="mb-8 float-animation">
                    <span class="text-8xl">&#127942;</span>
                </div>

                <!-- Main Title -->
                <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight">
                    {{ $settings['hero_title'] }}
                </h1>

                <!-- Subtitle -->
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    {{ $settings['hero_subtitle'] }}
                </p>

                <!-- Key Message -->
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 mb-10 max-w-2xl mx-auto">
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <span class="text-2xl">&#9889;</span>
                        <p class="text-lg font-bold">الفائز يُحدد بناءً على تقييمات جوجل الحقيقية</p>
                    </div>
                    <p class="text-sm opacity-80">
                        &#128202; تحليل ذكي للمراجعات وليس عدد الأصوات
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10 max-w-3xl mx-auto">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-3xl font-bold">&#127873;</div>
                        <div class="text-2xl font-bold">{{ $settings['winner_count'] }}</div>
                        <div class="text-sm opacity-80">فائزين شهريا</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-3xl font-bold">&#128176;</div>
                        <div class="text-2xl font-bold">{{ number_format($prizes[1]) }}</div>
                        <div class="text-sm opacity-80">ريال للأول</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-3xl font-bold">&#127978;</div>
                        <div class="text-2xl font-bold" x-text="stats.total_branches || '0'">{{ $stats['total_branches'] }}</div>
                        <div class="text-sm opacity-80">مطعم مشارك</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <div class="text-3xl font-bold">&#128101;</div>
                        <div class="text-2xl font-bold" x-text="stats.total_participants || '0'">{{ $stats['total_participants'] }}</div>
                        <div class="text-sm opacity-80">مشارك</div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                @if($settings['show_countdown'] && $currentPeriod)
                <div class="mb-10" x-data="countdown('{{ $currentPeriod->ends_at->toIso8601String() }}')">
                    <p class="text-lg mb-4 opacity-90">&#9200; الوقت المتبقي للمشاركة</p>
                    <div class="flex justify-center gap-4">
                        <div class="countdown-box text-center">
                            <div class="text-3xl md:text-4xl font-bold" x-text="days">00</div>
                            <div class="text-xs opacity-70">يوم</div>
                        </div>
                        <div class="countdown-box text-center">
                            <div class="text-3xl md:text-4xl font-bold" x-text="hours">00</div>
                            <div class="text-xs opacity-70">ساعة</div>
                        </div>
                        <div class="countdown-box text-center">
                            <div class="text-3xl md:text-4xl font-bold" x-text="minutes">00</div>
                            <div class="text-xs opacity-70">دقيقة</div>
                        </div>
                        <div class="countdown-box text-center">
                            <div class="text-3xl md:text-4xl font-bold" x-text="seconds">00</div>
                            <div class="text-xs opacity-70">ثانية</div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- CTA Button -->
                <button
                    @click="openNominationModal()"
                    class="bg-white text-orange-600 px-10 py-4 rounded-full text-xl font-bold shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 pulse-glow"
                >
                    &#128640; {{ $settings['cta_button_text'] }}
                </button>

                <!-- Scroll Indicator -->
                <div class="mt-16 animate-bounce">
                    <svg class="w-8 h-8 mx-auto opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-white" id="how-it-works">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">كيف تشارك؟</h2>
                <p class="text-gray-600 text-lg">ثلاث خطوات بسيطة للمشاركة في المسابقة</p>
            </div>

            <div class="max-w-5xl mx-auto">
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-8 text-center hover:shadow-xl transition-shadow duration-300">
                            <div class="w-16 h-16 bg-orange-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                                1
                            </div>
                            <div class="text-5xl mb-4">&#128241;</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">سجّل رقمك</h3>
                            <p class="text-gray-600">أدخل رقم جوالك وتحقق عبر واتساب</p>
                        </div>
                        <!-- Arrow -->
                        <div class="hidden md:block absolute top-1/2 -left-4 transform -translate-y-1/2 text-orange-300 text-4xl">
                            &#8592;
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 text-center hover:shadow-xl transition-shadow duration-300">
                            <div class="w-16 h-16 bg-blue-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                                2
                            </div>
                            <div class="text-5xl mb-4">&#128269;</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">رشّح مطعمك</h3>
                            <p class="text-gray-600">ابحث عن مطعمك المفضل ورشّحه</p>
                        </div>
                        <!-- Arrow -->
                        <div class="hidden md:block absolute top-1/2 -left-4 transform -translate-y-1/2 text-blue-300 text-4xl">
                            &#8592;
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-8 text-center hover:shadow-xl transition-shadow duration-300">
                            <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                                3
                            </div>
                            <div class="text-5xl mb-4">&#127942;</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">اربح جوائز</h3>
                            <p class="text-gray-600">إذا فاز مطعمك تدخل السحب على الجوائز</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scoring Explanation Section -->
    <section class="py-20 bg-gray-50" id="scoring">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">&#128202; كيف نحدد الفائز؟</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    نستخدم نظام تحليل ذكي يعتمد على تقييمات جوجل الحقيقية وليس عدد الأصوات
                </p>
            </div>

            <div class="max-w-4xl mx-auto">
                <!-- Important Notice -->
                <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-6 mb-10 text-center">
                    <div class="flex items-center justify-center gap-3 text-amber-800">
                        <span class="text-3xl">&#9888;&#65039;</span>
                        <p class="text-lg font-bold">التصويت لا يؤثر على ترتيب المطعم!</p>
                    </div>
                    <p class="text-amber-700 mt-2">الأداء الحقيقي للمطعم من تقييمات العملاء هو من يحدد الفائز</p>
                </div>

                <!-- Score Factors -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">معايير تحديد الفائز</h3>

                    <div class="space-y-6">
                        <!-- Rating -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#11088;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">التقييم العام</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['rating'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $scoreWeights['rating'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sentiment -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#128172;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">تحليل المشاعر</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['sentiment'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-400 rounded-full" style="width: {{ $scoreWeights['sentiment'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Response Rate -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#128232;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">معدل الاستجابة</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['response_rate'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-400 rounded-full" style="width: {{ $scoreWeights['response_rate'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Volume -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#128221;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">حجم المراجعات</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['volume'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-400 rounded-full" style="width: {{ $scoreWeights['volume'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Trend -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#128200;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">اتجاه التحسن</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['trend'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-teal-400 rounded-full" style="width: {{ $scoreWeights['trend'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Keywords -->
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">
                                &#128273;
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium text-gray-900">الكلمات المفتاحية</span>
                                    <span class="text-orange-600 font-bold">{{ $scoreWeights['keywords'] }}%</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-pink-400 rounded-full" style="width: {{ $scoreWeights['keywords'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                        <p class="text-gray-600">
                            &#127919; <strong>المطعم صاحب أعلى نتيجة = الفائز</strong>
                        </p>
                        <p class="text-gray-500 text-sm mt-2">
                            &#127920; جميع من رشّحوا المطعم الفائز يدخلون السحب على الجوائز
                        </p>
                    </div>
                </div>

                <!-- Tip -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                    <span class="text-2xl">&#128161;</span>
                    <p class="text-blue-800 font-medium mt-2">
                        نصيحة: رشّح المطعم الذي تعتقد أنه الأفضل أداءً لتزيد فرصتك بالفوز!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Prizes Section -->
    <section class="py-20 bg-gradient-to-br from-gray-900 to-gray-800 text-white" id="prizes">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">&#127873; جوائز هذا الشهر</h2>
                <p class="text-gray-400 text-lg">جوائز نقدية قيّمة للفائزين</p>
            </div>

            <div class="max-w-5xl mx-auto">
                <!-- Top 3 Prizes -->
                <div class="grid md:grid-cols-3 gap-6 mb-10">
                    <!-- 2nd Place -->
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-8 text-center transform hover:scale-105 transition-transform duration-300 order-2 md:order-1">
                        <div class="text-6xl mb-4">&#129352;</div>
                        <h3 class="text-xl font-bold mb-2">المركز الثاني</h3>
                        <div class="text-4xl font-extrabold mb-2">{{ number_format($prizes[2]) }}</div>
                        <div class="text-sm opacity-80">ريال سعودي</div>
                    </div>

                    <!-- 1st Place (Center - Larger) -->
                    <div class="bg-gradient-to-br from-yellow-300 via-yellow-400 to-orange-500 rounded-2xl p-10 text-center transform hover:scale-105 transition-transform duration-300 md:-mt-4 md:mb-4 order-1 md:order-2 shadow-2xl">
                        <div class="text-7xl mb-4">&#129351;</div>
                        <h3 class="text-2xl font-bold mb-2">المركز الأول</h3>
                        <div class="text-5xl font-extrabold mb-2">{{ number_format($prizes[1]) }}</div>
                        <div class="text-sm opacity-80">ريال سعودي</div>
                        <div class="mt-4 bg-white/20 rounded-full px-4 py-2 inline-block text-sm">
                            &#127775; الجائزة الكبرى
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div class="bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl p-8 text-center transform hover:scale-105 transition-transform duration-300 order-3">
                        <div class="text-6xl mb-4">&#129353;</div>
                        <h3 class="text-xl font-bold mb-2">المركز الثالث</h3>
                        <div class="text-4xl font-extrabold mb-2">{{ number_format($prizes[3]) }}</div>
                        <div class="text-sm opacity-80">ريال سعودي</div>
                    </div>
                </div>

                <!-- Other Winners -->
                <div class="bg-gray-700/50 backdrop-blur-sm rounded-2xl p-8 text-center">
                    <div class="text-4xl mb-4">&#127881;</div>
                    <h3 class="text-xl font-bold mb-2">+ {{ $settings['winner_count'] - 3 }} فائزين إضافيين</h3>
                    <p class="text-3xl font-bold text-yellow-400">{{ number_format($prizes['others']) }} ريال</p>
                    <p class="text-gray-400 text-sm mt-2">لكل فائز</p>
                </div>

                <!-- Total Prize Pool -->
                <div class="mt-10 text-center">
                    <p class="text-gray-400">إجمالي الجوائز الشهرية</p>
                    @php
                        $totalPrizes = $prizes[1] + $prizes[2] + $prizes[3] + ($prizes['others'] * ($settings['winner_count'] - 3));
                    @endphp
                    <p class="text-5xl font-extrabold text-yellow-400">{{ number_format($totalPrizes) }} ريال</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Participating Restaurants Section -->
    @if($settings['show_participating_restaurants'] && count($restaurants) > 0)
    <section class="py-20 bg-white" id="restaurants">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">&#127869;&#65039; مطاعم مشاركة في المسابقة</h2>
                <p class="text-gray-600">انضم إليهم ورشّح مطعمك المفضل</p>
            </div>

            <div class="max-w-4xl mx-auto">
                <!-- Restaurant Tags Cloud -->
                <div class="flex flex-wrap justify-center gap-3" id="restaurant-cloud">
                    @foreach($restaurants as $index => $restaurant)
                    <span
                        class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 text-gray-800 px-4 py-2 rounded-full text-sm font-medium hover:shadow-md transition-shadow cursor-default tag-float"
                        style="animation-delay: {{ $index * 0.1 }}s"
                    >
                        {{ $restaurant }}
                    </span>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <p class="text-gray-500">
                        + {{ max(0, $stats['total_branches'] - count($restaurants)) }} مطعم آخر...
                    </p>
                </div>

                <!-- Stats -->
                @if($settings['show_total_stats'])
                <div class="flex justify-center gap-8 mt-10">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_participants']) }}</div>
                        <div class="text-gray-500 text-sm">مشارك</div>
                    </div>
                    <div class="w-px bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_branches']) }}</div>
                        <div class="text-gray-500 text-sm">مطعم</div>
                    </div>
                    <div class="w-px bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_nominations']) }}</div>
                        <div class="text-gray-500 text-sm">ترشيح</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- Previous Winners Section -->
    @if(count($previousWinners) > 0)
    <section class="py-20 bg-gray-50" id="winners">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">&#127942; فائزون سابقون</h2>
                <p class="text-gray-600">انضم إليهم وكن الفائز القادم!</p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach($previousWinners as $winner)
                    <div class="bg-white rounded-2xl p-6 text-center shadow-md hover:shadow-xl transition-shadow">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-yellow-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                            &#128100;
                        </div>
                        <h3 class="font-bold text-gray-900">{{ $winner['name'] }}</h3>
                        <p class="text-gray-500 text-sm mb-2">{{ $winner['city'] }}</p>
                        <div class="inline-block bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                            {{ $winner['prize'] }}
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('competition.winners') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                        عرض جميع الفائزين &#8592;
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- FAQ Section -->
    <section class="py-20 bg-white" id="faq">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">&#10067; أسئلة شائعة</h2>
            </div>

            <div class="max-w-3xl mx-auto" x-data="{ openFaq: null }">
                <!-- FAQ Items -->
                @php
                $faqs = [
                    [
                        'q' => 'كيف يتم اختيار الفائزين؟',
                        'a' => 'نقوم بتحليل تقييمات جوجل للمطاعم المرشحة باستخدام الذكاء الاصطناعي. المطعم صاحب أعلى نتيجة يفوز، وجميع من رشّحوا هذا المطعم يدخلون سحب عشوائي على الجوائز.'
                    ],
                    [
                        'q' => 'هل التصويت يؤثر على ترتيب المطعم؟',
                        'a' => 'لا، التصويت لا يؤثر على ترتيب المطعم إطلاقا. الترتيب يعتمد فقط على أداء المطعم الحقيقي من تقييمات عملائه على جوجل.'
                    ],
                    [
                        'q' => 'هل يمكنني ترشيح أكثر من مطعم؟',
                        'a' => 'لا، يمكنك ترشيح مطعم واحد فقط كل شهر. اختر بحكمة!'
                    ],
                    [
                        'q' => 'متى يتم الإعلان عن الفائزين؟',
                        'a' => 'يتم الإعلان عن الفائزين في اليوم الأول من كل شهر، بعد انتهاء فترة المسابقة وتحليل النتائج.'
                    ],
                    [
                        'q' => 'كيف أستلم جائزتي؟',
                        'a' => 'سنتواصل معك عبر واتساب على الرقم المسجل. يمكنك استلام جائزتك عن طريق تحويل بنكي أو محفظة إلكترونية.'
                    ],
                    [
                        'q' => 'هل المسابقة مجانية؟',
                        'a' => 'نعم! المشاركة في المسابقة مجانية تماما.'
                    ],
                ];
                @endphp

                <div class="space-y-4">
                    @foreach($faqs as $index => $faq)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button
                            @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-6 py-4 text-right bg-white hover:bg-gray-50 flex items-center justify-between transition-colors"
                        >
                            <span class="font-medium text-gray-900">{{ $faq['q'] }}</span>
                            <svg
                                class="w-5 h-5 text-gray-500 transform transition-transform"
                                :class="openFaq === {{ $index }} ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div
                            x-show="openFaq === {{ $index }}"
                            x-collapse
                            class="px-6 py-4 bg-gray-50 border-t border-gray-200"
                        >
                            <p class="text-gray-600">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-20 hero-gradient text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">لا تفوّت الفرصة! &#128640;</h2>

            @if($currentPeriod)
            <p class="text-xl mb-8 opacity-90">
                &#9200; المسابقة تنتهي خلال {{ $stats['days_remaining'] }} يوم
            </p>
            @endif

            <button
                @click="openNominationModal()"
                class="bg-white text-orange-600 px-10 py-4 rounded-full text-xl font-bold shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300"
            >
                &#128640; {{ $settings['cta_button_text'] }}
            </button>

            <p class="mt-6 text-sm opacity-70">
                بالمشاركة، أنت توافق على
                <a href="{{ route('competition.terms') }}" class="underline">الشروط والأحكام</a>
                و
                <a href="{{ route('competition.privacy') }}" class="underline">سياسة الخصوصية</a>
            </p>
        </div>
    </section>

    <!-- Footer -->
    @include('competition.partials.footer')

    <!-- Nomination Modal -->
    @include('competition.partials.nomination-modal')

    <!-- Alpine.js App Script -->
    <script>
        function competitionApp() {
            return {
                stats: @json($stats),
                showNominationModal: false,
                currentStep: 'phone', // phone, otp, register, search, confirm, success

                openNominationModal() {
                    this.showNominationModal = true;
                    this.currentStep = 'phone';
                    document.body.style.overflow = 'hidden';
                },

                closeNominationModal() {
                    this.showNominationModal = false;
                    document.body.style.overflow = '';
                },

                async refreshStats() {
                    try {
                        const response = await fetch('{{ route("competition.stats") }}');
                        const data = await response.json();
                        if (data.success) {
                            this.stats = data.data;
                        }
                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                }
            }
        }

        function countdown(endDate) {
            return {
                days: '00',
                hours: '00',
                minutes: '00',
                seconds: '00',
                interval: null,

                init() {
                    this.updateCountdown();
                    this.interval = setInterval(() => this.updateCountdown(), 1000);
                },

                destroy() {
                    if (this.interval) {
                        clearInterval(this.interval);
                    }
                },

                updateCountdown() {
                    const end = new Date(endDate).getTime();
                    const now = new Date().getTime();
                    const diff = end - now;

                    if (diff <= 0) {
                        this.days = '00';
                        this.hours = '00';
                        this.minutes = '00';
                        this.seconds = '00';
                        this.destroy();
                        return;
                    }

                    this.days = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
                    this.hours = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                    this.minutes = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                    this.seconds = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
                }
            }
        }
    </script>
</body>
</html>
