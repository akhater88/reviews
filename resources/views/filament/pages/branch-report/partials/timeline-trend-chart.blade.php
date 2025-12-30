@php
    // Helper function for formatting period labels - must be defined FIRST
    if (!function_exists('formatTimelinePeriod')) {
        function formatTimelinePeriod($label) {
            if (empty($label)) return '';

            $parts = explode('-', $label);
            if (count($parts) < 2) return $label;

            $year = $parts[0];
            $month = (int)$parts[1];

            $monthNames = [
                1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
            ];

            return ($monthNames[$month] ?? $month) . ' ' . $year;
        }
    }

    // Get timeline data - use dedicated method that generates data if not available
    $timeline = $this->getTimelineData();

    // Handle timeline as array or object with periods
    $periods = is_array($timeline) && !isset($timeline['periods']) ? $timeline : ($timeline['periods'] ?? []);

    // Filter periods with valid ratings
    $chartData = collect($periods)
        ->filter(fn($period) => ($period['averageRating'] ?? 0) > 0)
        ->map(fn($period) => [
            'month' => formatTimelinePeriod($period['label'] ?? $period['period'] ?? ''),
            'rating' => $period['averageRating'] ?? 0,
            'reviews' => $period['reviewCount'] ?? $period['totalReviews'] ?? 0,
        ])
        ->values()
        ->toArray();

    // Calculate metrics
    $firstRating = $chartData[0]['rating'] ?? 0;
    $lastRating = !empty($chartData) ? end($chartData)['rating'] : 0;
    $overallChange = $lastRating - $firstRating;
    $averageRating = count($chartData) > 0
        ? collect($chartData)->avg('rating')
        : 0;

    // Determine trend
    $trend = 'stable';
    if ($overallChange > 0.15) $trend = 'improving';
    elseif ($overallChange < -0.15) $trend = 'declining';

    // Get AI insights
    $aiInsights = $timeline['aiInsights'] ?? [];
    $trendDescription = $aiInsights['overallTrend'] ?? $aiInsights['description'] ?? '';

    // Generate default description if none provided
    if (empty($trendDescription) && count($chartData) > 0) {
        if ($overallChange > 0.2) {
            $trendDescription = 'الاتجاه الزمني يظهر تحسناً ملحوظاً في التقييمات خلال الأشهر الثلاثة الماضية';
        } elseif ($overallChange < -0.2) {
            $trendDescription = 'الاتجاه الزمني يظهر انخفاضاً في التقييمات يتطلب الانتباه';
        } else {
            $trendDescription = 'الاتجاه الزمني يظهر استقراراً في التقييمات مع تذبذبات طفيفة';
        }
    }

    // Performance label
    $performanceLabel = match(true) {
        $averageRating >= 4.5 => 'ممتاز',
        $averageRating >= 4.0 => 'جيد جداً',
        $averageRating >= 3.5 => 'جيد',
        $averageRating >= 3.0 => 'متوسط',
        default => 'ضعيف',
    };
@endphp

@if(count($chartData) > 0)
<div class="relative overflow-hidden rounded-2xl border shadow-lg" style="background: linear-gradient(to bottom right, rgb(255 255 255), rgb(248 250 252), rgb(239 246 255 / 0.3)); border-color: rgb(226 232 240 / 0.6);">
    {{-- Subtle background gradient overlay --}}
    <div class="absolute inset-0 rounded-2xl" style="background: linear-gradient(to right, rgb(59 130 246 / 0.05), rgb(168 85 247 / 0.05), rgb(99 102 241 / 0.05));"></div>

    {{-- Header Section --}}
    <div class="relative p-4 sm:p-6 border-b rounded-t-2xl" style="background: linear-gradient(to right, rgb(248 250 252 / 0.8), rgb(239 246 255 / 0.8)); border-color: rgb(226 232 240 / 0.5);">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Title with Icon --}}
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="relative p-2 sm:p-3 rounded-xl shadow-lg h-12 flex items-center justify-center" style="background: linear-gradient(to bottom right, rgb(59 130 246), rgb(99 102 241));">
                    <x-heroicon-s-star class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                        التقييمات (آخر ٣ أشهر)
                    </h3>
                </div>
            </div>

            {{-- Badges --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                {{-- Trend Badge --}}
                @if($trend === 'improving')
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border font-medium text-xs shadow-sm" style="background: rgb(240 253 244); color: rgb(21 128 61); border-color: rgb(187 247 208);">
                        <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" />
                        <span class="text-xs font-semibold">تحسّن</span>
                        @if(abs($overallChange) > 0.1)
                            <span class="text-xs font-bold mr-1">(+{{ number_format($overallChange, 1) }})</span>
                        @endif
                    </div>
                @elseif($trend === 'declining')
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border font-medium text-xs shadow-sm" style="background: rgb(254 242 242); color: rgb(185 28 28); border-color: rgb(254 202 202);">
                        <x-heroicon-o-arrow-trending-down class="h-3.5 w-3.5" />
                        <span class="text-xs font-semibold">تراجع</span>
                        @if(abs($overallChange) > 0.1)
                            <span class="text-xs font-bold mr-1">({{ number_format($overallChange, 1) }})</span>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border font-medium text-xs shadow-sm" style="background: rgb(254 249 195); color: rgb(161 98 7); border-color: rgb(253 224 71);">
                        <x-heroicon-o-minus class="h-3.5 w-3.5" />
                        <span class="text-xs font-semibold">مستقر</span>
                    </div>
                @endif

                {{-- Performance Badge --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border font-medium text-xs shadow-sm" style="background: rgb(239 246 255); color: rgb(29 78 216); border-color: rgb(191 219 254);">
                    <span>⭐</span>
                    <span>{{ $performanceLabel }}</span>
                </div>
            </div>
        </div>

        {{-- AI Insight Description --}}
        @if($trendDescription)
            <div class="mt-3 sm:mt-4 p-3 sm:p-4 rounded-xl border shadow-sm" style="background: rgb(255 255 255 / 0.8); border-color: rgb(226 232 240 / 0.5);">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-bolt class="h-4 w-4 flex-shrink-0 mt-0.5" style="color: rgb(59 130 246);" />
                    <p class="text-sm font-medium leading-relaxed text-right" style="color: rgb(55 65 81);">
                        {{ $trendDescription }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Chart Section --}}
    <div class="relative p-4 sm:p-6" style="background: rgb(255 255 255 / 0.8);">
        <div class="relative h-72 sm:h-80 w-full mb-2 sm:mb-6 bg-white rounded-lg border" style="border-color: rgb(229 231 235);">
            <canvas
                id="timeline-chart-{{ uniqid() }}"
                class="w-full h-full timeline-chart-canvas"
                data-chart-labels="{{ json_encode(collect($chartData)->pluck('month')->toArray()) }}"
                data-chart-data="{{ json_encode(collect($chartData)->pluck('rating')->toArray()) }}"
                data-chart-reviews="{{ json_encode(collect($chartData)->pluck('reviews')->toArray()) }}"
            ></canvas>
        </div>
    </div>
</div>

{{-- Chart.js Initialization Script --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initTimelineCharts, 100);
});

document.addEventListener('livewire:navigated', function() {
    setTimeout(initTimelineCharts, 100);
});

function initTimelineCharts() {
    document.querySelectorAll('.timeline-chart-canvas').forEach(function(canvas) {
        if (canvas.chartInstance) return; // Already initialized

        const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
        const data = JSON.parse(canvas.dataset.chartData || '[]');
        const reviews = JSON.parse(canvas.dataset.chartReviews || '[]');

        if (labels.length === 0) return;

        const ctx = canvas.getContext('2d');
        canvas.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'التقييم',
                    data: data,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 10,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        rtl: true,
                        textDirection: 'rtl',
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937',
                        bodyColor: '#4b5563',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                const rating = context.raw;
                                const reviewCount = reviews[context.dataIndex];
                                let label = 'التقييم: ' + rating.toFixed(1) + '/5';
                                if (reviewCount) {
                                    label += '\nعدد المراجعات: ' + reviewCount;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'system-ui, sans-serif'
                            }
                        }
                    },
                    y: {
                        min: 1,
                        max: 5,
                        ticks: {
                            stepSize: 0.5
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    });
}
</script>
@endpush
@endonce
@endif
