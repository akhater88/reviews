<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Branch;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'المستخدمين';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمين';

    protected static ?int $navigationSort = 1;

    /**
     * Only admins can access the Users resource.
     * Branch managers should only access their profile via profile settings.
     */
    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المستخدم')
                    ->description('أدخل بيانات المستخدم الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\ToggleButtons::make('login_type')
                            ->label('نوع بيانات الدخول')
                            ->options([
                                'email' => 'بريد إلكتروني',
                                'phone' => 'رقم الجوال',
                            ])
                            ->icons([
                                'email' => 'heroicon-o-envelope',
                                'phone' => 'heroicon-o-phone',
                            ])
                            ->default('email')
                            ->inline()
                            ->live(),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required(fn (Forms\Get $get) => $get('login_type') === 'email')
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Forms\Get $get) => $get('login_type') === 'email'),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الجوال')
                            ->tel()
                            ->required(fn (Forms\Get $get) => $get('login_type') === 'phone')
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Forms\Get $get) => $get('login_type') === 'phone'),

                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8),
                    ])->columns(2),

                Forms\Components\Section::make('الصلاحيات')
                    ->description('حدد دور المستخدم والفروع المخصصة له')
                    ->schema([
                        Forms\Components\Radio::make('role')
                            ->label('الدور')
                            ->options([
                                'admin' => 'مدير رئيسي - وصول كامل لجميع الفروع',
                                'manager' => 'مدير فرع - وصول للفروع المحددة فقط',
                            ])
                            ->default('manager')
                            ->required()
                            ->live(),

                        Forms\Components\CheckboxList::make('branches')
                            ->label('الفروع المخصصة')
                            ->relationship('branches', 'name')
                            ->options(fn () => Branch::pluck('name', 'id'))
                            ->columns(2)
                            ->gridDirection('row')
                            ->visible(fn (Forms\Get $get) => $get('role') === 'manager')
                            ->helperText('حدد الفروع التي يمكن للمستخدم الوصول إليها'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('المستخدمين غير النشطين لا يمكنهم تسجيل الدخول'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'admin' => 'مدير رئيسي',
                        'manager' => 'مدير فرع',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'admin' => 'success',
                        'manager' => 'info',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('branches.name')
                    ->label('الفروع')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'admin' => 'مدير رئيسي',
                        'manager' => 'مدير فرع',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا يوجد مستخدمين')
            ->emptyStateDescription('قم بإضافة مستخدم جديد للبدء')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
