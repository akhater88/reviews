<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\Pages;

use App\Filament\SuperAdmin\Resources\PlanResource;
use App\Models\PlanLimit;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    private array $limitsData = [];
    private array $featuresData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load limits
        $limits = $this->record->limits;
        if ($limits) {
            $data['limits'] = [
                'max_branches' => $limits->max_branches,
                'max_competitors' => $limits->max_competitors,
                'max_users' => $limits->max_users,
                'max_reviews_sync' => $limits->max_reviews_sync,
                'max_ai_replies' => $limits->max_ai_replies,
                'max_ai_tokens' => $limits->max_ai_tokens,
                'max_api_calls' => $limits->max_api_calls,
                'max_analysis_runs' => $limits->max_analysis_runs,
                'analysis_retention_days' => $limits->analysis_retention_days,
            ];
        }

        // Load features
        $data['plan_features_data'] = $this->record->planFeatures->map(fn ($pf) => [
            'feature_id' => $pf->feature_id,
            'is_enabled' => $pf->is_enabled,
            'limit_value' => $pf->limit_value,
        ])->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract limits and features
        $this->limitsData = $data['limits'] ?? [];
        $this->featuresData = $data['plan_features_data'] ?? [];

        unset($data['limits'], $data['plan_features_data']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Update limits
        PlanLimit::updateOrCreate(
            ['plan_id' => $this->record->id],
            $this->limitsData
        );

        // Sync features
        $this->record->planFeatures()->delete();

        foreach ($this->featuresData as $feature) {
            if (!empty($feature['feature_id'])) {
                $this->record->planFeatures()->create([
                    'feature_id' => $feature['feature_id'],
                    'is_enabled' => $feature['is_enabled'] ?? true,
                    'limit_value' => $feature['limit_value'] ?? null,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
