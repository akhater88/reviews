<?php

namespace App\Filament\Pages;

use App\Livewire\PhoneVerificationModal;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\On;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.profile';
    protected static ?string $navigationLabel = 'الملف الشخصي';
    protected static ?string $title = 'الملف الشخصي';
    protected static ?string $slug = 'profile';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int $navigationSort = 100;

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $this->passwordData = [
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'الملف الشخصي';
    }

    public static function getNavigationLabel(): string
    {
        return 'الملف الشخصي';
    }

    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('المعلومات الشخصية')
                    ->description('تحديث معلومات حسابك')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email', ignorable: Auth::user())
                            ->extraInputAttributes(['dir' => 'ltr']),
                    ])
                    ->columns(2),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('تغيير كلمة المرور')
                    ->description('تأكد من استخدام كلمة مرور قوية')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('كلمة المرور الحالية')
                            ->password()
                            ->revealable()
                            ->required()
                            ->currentPassword()
                            ->extraInputAttributes(['dir' => 'ltr']),
                        TextInput::make('new_password')
                            ->label('كلمة المرور الجديدة')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::defaults())
                            ->extraInputAttributes(['dir' => 'ltr']),
                        TextInput::make('new_password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required()
                            ->same('new_password')
                            ->extraInputAttributes(['dir' => 'ltr']),
                    ])
                    ->columns(1),
            ])
            ->statePath('passwordData');
    }

    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    public function updateProfile(): void
    {
        try {
            $data = $this->profileForm->getState();

            Auth::user()->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            Notification::make()
                ->title('تم التحديث')
                ->body('تم تحديث المعلومات الشخصية بنجاح')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }

    public function updatePassword(): void
    {
        try {
            $data = $this->passwordForm->getState();

            Auth::user()->update([
                'password' => Hash::make($data['new_password']),
            ]);

            $this->passwordData = [
                'current_password' => '',
                'new_password' => '',
                'new_password_confirmation' => '',
            ];

            Notification::make()
                ->title('تم التحديث')
                ->body('تم تحديث كلمة المرور بنجاح')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }

    public function openPhoneVerificationModal(): void
    {
        $this->dispatch('open-phone-verification-modal');
    }

    /**
     * Get the user's phone verification status.
     */
    public function getPhoneVerified(): bool
    {
        return Auth::user()->isPhoneVerified();
    }

    /**
     * Get the user's masked phone number.
     */
    public function getMaskedPhone(): ?string
    {
        return Auth::user()->masked_phone;
    }

    /**
     * Get the user's phone number.
     */
    public function getPhone(): ?string
    {
        return Auth::user()->phone;
    }
}
