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
        ];
    }
}
