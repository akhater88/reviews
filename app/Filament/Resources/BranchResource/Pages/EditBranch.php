<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use App\Jobs\SyncBranchReviewsJob;
use App\Services\Analysis\AnalysisPipelineService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncReviews')
                ->label('مزامنة المراجعات')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('مزامنة المراجعات')
                ->modalDescription('سيتم جلب أحدث المراجعات من Outscraper. قد تستغرق العملية بضع دقائق.')
                ->modalSubmitActionLabel('بدء المزامنة')
                ->visible(fn (): bool => !empty($this->record->google_place_id))
                ->action(function () {
                    SyncBranchReviewsJob::dispatch($this->record)->onQueue('reviews');

                    Notification::make()
                        ->title('تم بدء المزامنة')
                        ->body("جاري مزامنة مراجعات {$this->record->name}")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('analyzeReviews')
                ->label('تحليل المراجعات')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تحليل المراجعات بالذكاء الاصطناعي')
                ->modalDescription('سيتم تحليل المراجعات باستخدام الذكاء الاصطناعي لاستخراج رؤى وتوصيات. قد تستغرق العملية عدة دقائق.')
                ->modalSubmitActionLabel('بدء التحليل')
                ->visible(fn (): bool => $this->record->reviews()->exists())
                ->disabled(fn (): bool => app(AnalysisPipelineService::class)->hasActiveAnalysis($this->record))
                ->action(function () {
                    try {
                        $service = app(AnalysisPipelineService::class);
                        $overview = $service->startAnalysis($this->record);

                        Notification::make()
                            ->title('تم بدء التحليل')
                            ->body("جاري تحليل مراجعات {$this->record->name} - سيتم إشعارك عند الانتهاء")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل بدء التحليل')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('viewReport')
                ->label('عرض التقرير')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->url(fn () => \App\Filament\Pages\BranchReportPage::getUrl(['branch' => $this->record]))
                ->visible(fn (): bool => $this->record->reviews()->exists()),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث بيانات الفرع بنجاح';
    }
}
