<?php

namespace App\Filament\Pages;

use App\Services\PerformanceScoreService;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyRankingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'الترتيب الشهري';
    protected static ?string $title = 'الترتيب الشهري';
    protected static ?string $slug = 'monthly-rankings';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'التقارير';

    protected static string $view = 'filament.pages.monthly-rankings';

    // Filter properties
    public ?string $dateRange = 'this_month';
    public ?string $category = 'overall';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;

    // Data
    public Collection $rankings;
    public array $topThree = [];

    protected PerformanceScoreService $performanceService;

    public function boot(PerformanceScoreService $performanceService): void
    {
        $this->performanceService = $performanceService;
    }

    public function mount(): void
    {
        $this->rankings = collect();
        $this->loadRankings();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('dateRange')
                    ->label('الفترة الزمنية')
                    ->options([
                        'this_month' => 'هذا الشهر',
                        'last_month' => 'الشهر الماضي',
                        'last_3_months' => 'آخر 3 أشهر',
                        'last_6_months' => 'آخر 6 أشهر',
                        'this_year' => 'هذا العام',
                        'custom' => 'فترة مخصصة',
                    ])
                    ->default('this_month')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->loadRankings()),

                DatePicker::make('customStartDate')
                    ->label('من تاريخ')
                    ->visible(fn ($get) => $get('dateRange') === 'custom')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->loadRankings()),

                DatePicker::make('customEndDate')
                    ->label('إلى تاريخ')
                    ->visible(fn ($get) => $get('dateRange') === 'custom')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->loadRankings()),

                Select::make('category')
                    ->label('الفئة')
                    ->options([
                        'overall' => 'شامل',
                        'food' => 'الطعم',
                        'service' => 'الخدمة',
                    ])
                    ->default('overall')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->loadRankings()),
            ])
            ->columns(4);
    }

    public function loadRankings(): void
    {
        $tenantId = auth()->user()->tenant_id;
        [$startDate, $endDate] = $this->getDateRange();

        $this->rankings = $this->performanceService->getRankings(
            $tenantId,
            $startDate,
            $endDate,
            $this->category
        );

        $this->topThree = $this->performanceService->getTopThree(
            $tenantId,
            $startDate,
            $endDate,
            $this->category
        );
    }

    protected function getDateRange(): array
    {
        return match ($this->dateRange) {
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $this->customStartDate ? Carbon::parse($this->customStartDate) : now()->startOfMonth(),
                $this->customEndDate ? Carbon::parse($this->customEndDate) : now()->endOfMonth(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function getDateRangeLabel(): string
    {
        [$startDate, $endDate] = $this->getDateRange();
        return $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
    }

    public function exportReport(): void
    {
        Notification::make()
            ->title('قريباً')
            ->body('سيتم إضافة ميزة التصدير قريباً')
            ->info()
            ->send();
    }

    protected function getViewData(): array
    {
        return [
            'rankings' => $this->rankings,
            'topThree' => $this->topThree,
            'dateRangeLabel' => $this->getDateRangeLabel(),
        ];
    }
}
