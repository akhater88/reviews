<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseProfile;

class Profile extends BaseProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'الملف الشخصي';
    protected static ?string $title = 'الملف الشخصي';
    protected static bool $shouldRegisterNavigation = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('المعلومات الشخصية')
                    ->description('تحديث معلوماتك الشخصية')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->avatar()
                            ->directory('super-admin-avatars')
                            ->maxSize(1024)
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('تغيير كلمة المرور')
                    ->description('اتركها فارغة إذا لم ترد تغيير كلمة المرور')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('كلمة المرور الحالية')
                            ->password()
                            ->currentPassword()
                            ->requiredWith('password'),

                        TextInput::make('password')
                            ->label('كلمة المرور الجديدة')
                            ->password()
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->dehydrated(fn ($state) => filled($state)),

                        TextInput::make('password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }
}
