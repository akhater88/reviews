<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the tenant_id from the current user
        $data['tenant_id'] = Auth::user()->tenant_id;
        
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة المستخدم بنجاح';
    }
}
