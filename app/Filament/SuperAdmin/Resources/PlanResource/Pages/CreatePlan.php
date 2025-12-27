<?php

namespace App\Filament\SuperAdmin\Resources\PlanResource\Pages;

use App\Filament\SuperAdmin\Resources\PlanResource;
use App\Models\PlanLimit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    private array $limitsData = [];
    private array $featuresData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract limits and features for separate handling
        $this->limitsData = $data['limits'] ?? [];
        $this->featuresData = $data['plan_features_data'] ?? [];

        unset($data['limits'], $data['plan_features_data']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $plan = static::getModel()::create($data);

        // Create limits
        if (!empty($this->limitsData)) {
            PlanLimit::create(array_merge(
                ['plan_id' => $plan->id],
                $this->limitsData
            ));
        }

        // Create features
        if (!empty($this->featuresData)) {
            foreach ($this->featuresData as $feature) {
                if (!empty($feature['feature_id'])) {
                    $plan->planFeatures()->create([
                        'feature_id' => $feature['feature_id'],
                        'is_enabled' => $feature['is_enabled'] ?? true,
                        'limit_value' => $feature['limit_value'] ?? null,
                    ]);
                }
            }
        }

        return $plan;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
