<?php

namespace App\Filament\Pages\Auth;

use App\Models\Tenant;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public ?Tenant $tenant = null;

    public function mount(): void
    {
        parent::mount();

        // Load tenant from session if available
        if ($tenantId = session('login_tenant_id')) {
            $this->tenant = Tenant::find($tenantId);
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'تسجيل الدخول';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getTenantLogoUrl(): ?string
    {
        if ($this->tenant && $this->tenant->logo) {
            return Storage::url($this->tenant->logo);
        }

        return null;
    }

    public function getTenantName(): ?string
    {
        return $this->tenant?->display_name;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('البريد الإلكتروني')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('أدخل البريد الإلكتروني')
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('كلمة السر')
            ->password()
            ->revealable()
            ->required()
            ->placeholder('أدخل كلمة السر')
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }
}
