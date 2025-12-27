<?php

namespace App\Filament\SuperAdmin\Resources\TenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = 'المستخدمين';
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمين';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel(),

                Forms\Components\Select::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مسؤول',
                        'manager' => 'مدير فرع',
                        'staff' => 'موظف',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'admin' => 'مسؤول',
                        'manager' => 'مدير فرع',
                        'staff' => 'موظف',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'staff' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('آخر دخول')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مسؤول',
                        'manager' => 'مدير فرع',
                        'staff' => 'موظف',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مستخدم')
                    ->mutateFormDataUsing(function (array $data) {
                        $password = Str::random(12);
                        $data['password'] = Hash::make($password);
                        $data['temp_password'] = $password;
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        Notification::make()
                            ->title('تم إنشاء المستخدم')
                            ->body("كلمة المرور: {$data['temp_password']}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('reset_password')
                    ->label('إعادة كلمة المرور')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $password = Str::random(12);
                        $record->update(['password' => Hash::make($password)]);

                        Notification::make()
                            ->title('تم إعادة تعيين كلمة المرور')
                            ->body("كلمة المرور الجديدة: {$password}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
