<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Only admins can create new users.
     */
    public static function canAccess(array $parameters = []): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && $user->isAdmin();
    }

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
