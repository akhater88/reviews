<?php

namespace App\Filament\SuperAdmin\Resources\InternalCompetitionResource\Pages;

use App\Enums\InternalCompetition\CompetitionStatus;
use App\Filament\SuperAdmin\Resources\InternalCompetitionResource;
use App\Jobs\InternalCompetition\CalculateBenchmarksJob;
use App\Jobs\InternalCompetition\CalculateDailyScoresJob;
use App\Jobs\InternalCompetition\ProcessCompetitionEndJob;
use App\Services\InternalCompetition\CompetitionService;
use App\Services\InternalCompetition\ScoreCalculationService;
use App\Services\InternalCompetition\WinnerService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInternalCompetition extends ViewRecord
{
    protected static string $resource = InternalCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status->canEdit()),

            Actions\Action::make('activate')
                ->label('تفعيل المسابقة')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status->canActivate())
                ->action(function () {
                    try {
                        app(CompetitionService::class)->activate($this->record);
                        Notification::make()
                            ->title('تم تفعيل المسابقة بنجاح')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('calculate_scores')
                ->label('حساب النتائج')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->visible(fn () => $this->record->status === CompetitionStatus::ACTIVE)
                ->action(function () {
                    CalculateDailyScoresJob::dispatch($this->record->id);
                    Notification::make()
                        ->title('جاري حساب النتائج')
                        ->body('تم إرسال المهمة للمعالجة')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('calculate_benchmarks')
                ->label('حساب المقارنات')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->visible(fn () => in_array($this->record->status, [
                    CompetitionStatus::ACTIVE,
                    CompetitionStatus::ENDED,
                    CompetitionStatus::PUBLISHED,
                ]))
                ->action(function () {
                    CalculateBenchmarksJob::dispatch($this->record->id);
                    Notification::make()
                        ->title('جاري حساب المقارنات')
                        ->body('تم إرسال المهمة للمعالجة')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('end')
                ->label('إنهاء المسابقة')
                ->icon('heroicon-o-stop')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === CompetitionStatus::ACTIVE)
                ->action(function () {
                    try {
                        // Dispatch the job to handle the full end process including:
                        // - Finalizing scores
                        // - Calculating benchmarks
                        // - Determining winners
                        ProcessCompetitionEndJob::dispatch($this->record->id);
                        Notification::make()
                            ->title('جاري إنهاء المسابقة')
                            ->body('تم إرسال المهمة للمعالجة. سيتم حساب النتائج وتحديد الفائزين.')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('publish')
                ->label('نشر النتائج')
                ->icon('heroicon-o-megaphone')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === CompetitionStatus::ENDED)
                ->action(function () {
                    try {
                        app(CompetitionService::class)->publish($this->record);
                        Notification::make()
                            ->title('تم نشر النتائج')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('determine_winners')
                ->label('تحديد الفائزين')
                ->icon('heroicon-o-trophy')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تحديد الفائزين')
                ->modalDescription('سيتم إعادة حساب النتائج النهائية وتحديد الفائزين. هل تريد المتابعة؟')
                ->visible(fn () => in_array($this->record->status, [CompetitionStatus::ENDED, CompetitionStatus::PUBLISHED])
                    && $this->record->winners()->count() === 0)
                ->action(function () {
                    try {
                        // First finalize scores if not done
                        app(ScoreCalculationService::class)->finalizeAllScores($this->record);

                        // Then determine winners
                        $winners = app(WinnerService::class)->determineWinners($this->record);

                        Notification::make()
                            ->title('تم تحديد الفائزين بنجاح')
                            ->body('تم تحديد ' . $winners->count() . ' فائز')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('خطأ')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
