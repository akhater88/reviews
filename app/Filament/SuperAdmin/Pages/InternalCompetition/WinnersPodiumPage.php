<?php

namespace App\Filament\SuperAdmin\Pages\InternalCompetition;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Models\InternalCompetition\InternalCompetition;
use App\Services\InternalCompetition\WinnerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;

class WinnersPodiumPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'المسابقات';
    protected static ?string $navigationLabel = 'منصة الفائزين';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.internal-competition.winners-podium';

    public ?int $selectedCompetitionId = null;
    public ?InternalCompetition $competition = null;
    public array $podiums = [];
    public array $statistics = [];

    public function mount(): void
    {
        $this->selectedCompetitionId = InternalCompetition::whereIn('status', [
            CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED,
        ])->latest()->value('id');
        $this->loadData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('selectCompetition')->label('اختر المسابقة')->icon('heroicon-o-funnel')
                ->form([
                    Select::make('competition_id')->label('المسابقة')
                        ->options(InternalCompetition::whereIn('status', [CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                            ->orderByDesc('ended_at')->get()->mapWithKeys(fn ($c) => [$c->id => $c->display_name]))
                        ->required()->searchable(),
                ])
                ->action(function (array $data) {
                    $this->selectedCompetitionId = $data['competition_id'];
                    $this->loadData();
                }),
        ];
    }

    public function loadData(): void
    {
        if (!$this->selectedCompetitionId) {
            return;
        }
        $this->competition = InternalCompetition::find($this->selectedCompetitionId);
        if (!$this->competition) {
            return;
        }

        $winnerService = app(WinnerService::class);
        $this->podiums = [];
        foreach ($this->competition->enabled_metrics as $metric) {
            $this->podiums[$metric->value] = $winnerService->getPodiumData($this->competition, $metric);
        }
        $this->statistics = $winnerService->getWinnerStatistics($this->competition);
    }
}
