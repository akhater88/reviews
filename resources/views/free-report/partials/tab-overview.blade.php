<div class="space-y-6">
    <!-- Stats Grid -->
    @include('free-report.components.stats-grid', ['result' => $result])

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Sentiment Chart -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                توزيع المشاعر
            </h3>
            <div class="relative h-64">
                <canvas id="sentimentChart"></canvas>
            </div>
            @php
                $sentimentBreakdown = $result->sentiment_breakdown ?? ['positive' => 0, 'negative' => 0, 'neutral' => 0];
            @endphp
            <div class="flex justify-center gap-6 mt-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <span class="text-sm text-gray-600">إيجابي ({{ $sentimentBreakdown['positive'] ?? 0 }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                    <span class="text-sm text-gray-600">سلبي ({{ $sentimentBreakdown['negative'] ?? 0 }})</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
                    <span class="text-sm text-gray-600">محايد ({{ $sentimentBreakdown['neutral'] ?? 0 }})</span>
                </div>
            </div>
        </div>

        <!-- AI Summary -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                ملخص الذكاء الاصطناعي
            </h3>
            <p class="text-gray-700 leading-relaxed">
                {{ $result->executive_summary ?? 'تحليل شامل لتقييمات عملائك يظهر أن الأداء العام جيد مع بعض المجالات التي تحتاج تحسين.' }}
            </p>

            @if($result->top_strengths || $result->top_weaknesses)
                <div class="mt-6 space-y-4">
                    @if($result->top_strengths && count($result->top_strengths) > 0)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-800 font-medium mb-1">نقاط القوة:</p>
                            <p class="text-green-700 text-sm">{{ is_array($result->top_strengths) ? implode('، ', array_slice($result->top_strengths, 0, 2)) : $result->top_strengths }}</p>
                        </div>
                    @endif

                    @if($result->top_weaknesses && count($result->top_weaknesses) > 0)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-red-800 font-medium mb-1">يحتاج انتباه:</p>
                            <p class="text-red-700 text-sm">{{ is_array($result->top_weaknesses) ? implode('، ', array_slice($result->top_weaknesses, 0, 2)) : $result->top_weaknesses }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['إيجابي', 'سلبي', 'محايد'],
            datasets: [{
                data: [{{ $sentimentBreakdown['positive'] ?? 0 }}, {{ $sentimentBreakdown['negative'] ?? 0 }}, {{ $sentimentBreakdown['neutral'] ?? 0 }}],
                backgroundColor: ['#22c55e', '#ef4444', '#9ca3af'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            cutout: '60%',
        }
    });
});
</script>
@endpush
