<header class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Business Info -->
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold">
                    {{ mb_substr($report->business_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                        {{ $report->business_name }}
                    </h1>
                    @if($report->business_address)
                        <p class="text-gray-500 text-sm mt-1">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $report->business_address }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Rating Badge -->
            <div class="flex items-center gap-4">
                @if($result)
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-1 text-2xl font-bold text-yellow-500">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ number_format($result->average_rating ?? 0, 1) }}
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ number_format($result->total_reviews ?? 0) }} تقييم
                        </p>
                    </div>

                    <!-- Grade Badge -->
                    @php
                        $grade = $result->getGrade();
                        $gradeColor = $result->getGradeColor();
                        $colorClasses = [
                            'green' => 'bg-green-100 text-green-700',
                            'blue' => 'bg-blue-100 text-blue-700',
                            'yellow' => 'bg-yellow-100 text-yellow-700',
                            'orange' => 'bg-orange-100 text-orange-700',
                            'red' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <div class="px-4 py-2 {{ $colorClasses[$gradeColor] ?? 'bg-gray-100 text-gray-700' }} rounded-full text-lg font-bold">
                        {{ $grade }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Report Date -->
        <div class="mt-4 text-sm text-gray-500">
            تقرير بتاريخ: {{ $report->created_at->format('d/m/Y') }}
        </div>
    </div>
</header>
