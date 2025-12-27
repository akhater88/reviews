<?php

namespace App\Filament\SuperAdmin\Resources\FeatureResource\Pages;

use App\Filament\SuperAdmin\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeature extends EditRecord
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->planFeatures()->count() === 0),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
