<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\AnalysisOverview;
use App\Enums\AnalysisType;
use App\Enums\AnalysisStatus;
use App\Services\Analysis\AnalysisPipelineService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;

class BranchReportPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'تقرير الفرع';
    protected static ?string $title = 'تقرير الفرع';
    protected static ?string $slug = 'branches/{branch}/report';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.branch-report';

    public Branch $branch;
    public ?AnalysisOverview $latestAnalysis = null;
    public string $activeTab = 'overview';
    public array $analysisData = [];

    public function mount(Branch $branch): void
    {
        // Ensure user has access to this branch
        abort_unless(
            auth()->user()->canAccessBranch($branch),
            403,
            'غير مصرح لك بالوصول إلى هذا الفرع'
        );

        $this->branch = $branch;
        $this->loadAnalysisData();
    }

    public function getTitle(): string|Htmlable
    {
        return "تقرير فرع: {$this->branch->name}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.branches.index') => 'الفروع',
            route('filament.admin.resources.branches.edit', $this->branch) => $this->branch->name,
            '#' => 'التقرير',
        ];
    }

    protected function loadAnalysisData(): void
    {
        // Get latest completed analysis for this branch
        $this->latestAnalysis = AnalysisOverview::where('branch_id', $this->branch->id)
            ->where('status', AnalysisStatus::COMPLETED)
            ->with('results')
            ->latest()
            ->first();

        if (!$this->latestAnalysis) {
            return;
        }

        // Load all analysis results into array
        foreach ($this->latestAnalysis->results as $result) {
            $this->analysisData[$result->analysis_type->value] = $result->result;
        }
    }

    public function hasAnalysisData(): bool
    {
        return $this->latestAnalysis !== null && !empty($this->analysisData);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Get restaurant information for the header
     */
    public function getRestaurantInfo(): array
    {
        return [
            'name' => $this->branch->name,
            'location' => $this->branch->full_address ?? "{$this->branch->city}, {$this->branch->country}",
            'photoUrl' => $this->branch->photo_url ?? null,
            'placeId' => $this->branch->google_place_id,
        ];
    }

    /**
     * Get overview cards from analysis data
     */
    public function getOverviewCards(): array
    {
        return $this->analysisData[AnalysisType::OVERVIEW_CARDS->value] ?? [];
    }

    /**
     * Get raw overview data
     */
    public function getOverviewData(): array
    {
        return $this->analysisData[AnalysisType::OVERVIEW_CARDS->value] ?? [];
    }

    public function getSentimentData(): array
    {
        return $this->analysisData[AnalysisType::SENTIMENT->value] ?? [];
    }

    /**
     * Get category data from overview cards
     */
    public function getCategoryData(): array
    {
        $overviewCards = $this->getOverviewCards();
        foreach ($overviewCards as $card) {
            if (($card['type'] ?? '') === 'category_analysis') {
                return $card['data']['categories']
                    ?? $card['data']['dynamicCategories']
                    ?? $card['data']['organicCategories']
                    ?? [];
            }
        }
        // Fallback to category_insights data
        $categoryInsights = $this->analysisData[AnalysisType::CATEGORY_INSIGHTS->value] ?? [];
        return $categoryInsights['categories'] ?? $categoryInsights;
    }

    public function getGenderData(): array
    {
        return $this->analysisData[AnalysisType::GENDER_INSIGHTS->value] ?? [];
    }

    public function getEmployeesData(): array
    {
        return $this->analysisData[AnalysisType::EMPLOYEES_INSIGHTS->value] ?? [];
    }

    public function getKeywordsData(): array
    {
        return $this->analysisData[AnalysisType::KEYWORDS->value] ?? [];
    }

    public function getRecommendationsData(): array
    {
        return $this->analysisData[AnalysisType::RECOMMENDATIONS->value] ?? [];
    }

    /**
     * Get operational data with fallback to recommendations
     */
    public function getOperationalData(): array
    {
        return $this->analysisData[AnalysisType::OPERATIONAL_INTELLIGENCE->value]
            ?? $this->analysisData[AnalysisType::RECOMMENDATIONS->value]
            ?? [];
    }

    /**
     * Get timeline data for ratings trend chart
     */
    public function getTimelineData(): array
    {
        // First try to get from stored analysis data
        $overviewCards = $this->getOverviewCards();
        foreach ($overviewCards as $card) {
            if (($card['type'] ?? '') === 'ratings_reviews') {
                $timeline = $card['data']['timeline'] ?? [];
                if (!empty($timeline)) {
                    return $timeline;
                }
            }
        }

        // Generate on the fly if not available
        return $this->generateTimelineFromReviews();
    }

    /**
     * Generate timeline data from branch reviews
     */
    private function generateTimelineFromReviews(): array
    {
        $periodEnd = now();
        $periodStart = now()->subMonths(3);

        $reviews = $this->branch->reviews()
            ->whereBetween('review_date', [$periodStart, $periodEnd])
            ->whereNotNull('rating')
            ->orderBy('review_date')
            ->get(['rating', 'review_date']);

        if ($reviews->isEmpty()) {
            return [];
        }

        // Group reviews by month
        $groupedByMonth = $reviews->groupBy(function ($review) {
            return \Carbon\Carbon::parse($review->review_date)->format('Y-m');
        });

        $periods = [];
        $previousRating = null;

        foreach ($groupedByMonth as $month => $monthReviews) {
            $averageRating = round($monthReviews->avg('rating'), 2);
            $reviewCount = $monthReviews->count();

            // Determine trend direction
            $trendDirection = 'stable';
            if ($previousRating !== null) {
                $change = $averageRating - $previousRating;
                if ($change > 0.1) {
                    $trendDirection = 'improving';
                } elseif ($change < -0.1) {
                    $trendDirection = 'declining';
                }
            }

            $periods[] = [
                'period' => $month,
                'label' => $month,
                'averageRating' => $averageRating,
                'reviewCount' => $reviewCount,
                'trendDirection' => $trendDirection,
            ];

            $previousRating = $averageRating;
        }

        if (count($periods) < 2) {
            return [
                'periods' => $periods,
                'aiInsights' => ['overallTrend' => 'لا توجد بيانات كافية لتحليل الاتجاه الزمني'],
            ];
        }

        // Generate insights
        $firstRating = $periods[0]['averageRating'];
        $lastRating = end($periods)['averageRating'];
        $change = $lastRating - $firstRating;

        if ($change > 0.2) {
            $description = 'الاتجاه الزمني يظهر تحسناً ملحوظاً في التقييمات خلال الأشهر الثلاثة الماضية';
        } elseif ($change < -0.2) {
            $description = 'الاتجاه الزمني يظهر انخفاضاً في التقييمات يتطلب الانتباه';
        } else {
            $description = 'الاتجاه الزمني يظهر استقراراً في التقييمات مع تذبذبات طفيفة';
        }

        return [
            'periods' => $periods,
            'aiInsights' => [
                'overallTrend' => $description,
                'change' => round($change, 2),
                'direction' => $change > 0.15 ? 'improving' : ($change < -0.15 ? 'declining' : 'stable'),
            ],
        ];
    }

    public function startNewAnalysis(): void
    {
        $service = app(AnalysisPipelineService::class);

        // Check if there's already an active analysis
        if ($service->hasActiveAnalysis($this->branch)) {
            Notification::make()
                ->title('يوجد تحليل قيد التنفيذ')
                ->body('يرجى الانتظار حتى انتهاء التحليل الحالي')
                ->warning()
                ->send();
            return;
        }

        try {
            $overview = $service->startAnalysis($this->branch);

            Notification::make()
                ->title('تم بدء التحليل بنجاح')
                ->body('سيتم تحديث الصفحة عند الانتهاء من التحليل')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('حدث خطأ أثناء بدء التحليل')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        return [
            'branch' => $this->branch,
            'latestAnalysis' => $this->latestAnalysis,
            'hasData' => $this->hasAnalysisData(),
            'activeTab' => $this->activeTab,
        ];
    }
}
