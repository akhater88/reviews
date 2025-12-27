<?php

namespace App\Filament\SuperAdmin\Resources\FeatureResource\Pages;

use App\Filament\SuperAdmin\Resources\FeatureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeature extends CreateRecord
{
    protected static string $resource = FeatureResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
